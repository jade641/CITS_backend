<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAssignment;
use App\Models\IncidentAttachment;
use App\Models\IncidentComment;
use App\Models\IncidentHistory;
use App\Models\IncidentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IncidentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function visibleIncidentsQuery(User $user): Builder
    {
        $query = Incident::query()->with([
            'category',
            'status',
            'reporter.roles',
            'currentAssignee.roles',
            'assignments.assignee.roles',
            'assignments.assigner.roles',
        ])->latest('reported_at');

        if ($user->hasAnyRole(['administrator', 'security-analyst'])) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user): void {
            $builder
                ->where('reporter_id', $user->id)
                ->orWhere('current_assignee_id', $user->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createIncident(array $data, User $actor, ?Request $request = null): Incident
    {
        $openStatus = IncidentStatus::query()->where('slug', 'open')->firstOrFail();

        $data['description'] = (string) ($data['description'] ?? '');

        $incident = DB::transaction(function () use ($data, $actor, $openStatus, $request): Incident {
            $incident = Incident::query()->create([
                ...$data,
                'ticket_number' => $this->generateTicketNumber(),
                'status_id' => $openStatus->id,
                'reporter_id' => $actor->id,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'reported_at' => now(),
            ]);

            $this->recordHistory(
                $incident,
                $actor,
                'created',
                'Incident created by '.$actor->name,
                null,
                null,
                ['severity' => $incident->severity, 'status' => $openStatus->slug],
            );

            $this->auditLogService->log(
                $actor,
                'incident.created',
                $incident,
                ['ticket_number' => $incident->ticket_number],
                $request,
            );

            $this->notificationService->notifyUsers(
                User::query()->whereHas('roles', fn (Builder $builder) => $builder->whereIn('slug', ['administrator', 'security-analyst']))->get(),
                'New Incident Reported',
                sprintf('%s reported incident %s.', $actor->name, $incident->ticket_number),
                'warning',
                ['incident_id' => $incident->id],
            );

            return $incident;
        });

        return $this->loadIncident($incident);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateIncident(Incident $incident, array $data, User $actor, ?Request $request = null): Incident
    {
        DB::transaction(function () use ($incident, $data, $actor, $request): void {
            $original = $incident->replicate();

            $incident->fill([
                ...$data,
                'updated_by' => $actor->id,
            ]);
            $incident->save();

            foreach ($incident->getChanges() as $field => $newValue) {
                if ($field === 'updated_at') {
                    continue;
                }

                $this->recordHistory(
                    $incident,
                    $actor,
                    'updated',
                    sprintf('Updated %s on incident %s.', $field, $incident->ticket_number),
                    $field,
                    Arr::get($original->getAttributes(), $field),
                    $newValue,
                );
            }

            $this->auditLogService->log(
                $actor,
                'incident.updated',
                $incident,
                ['changes' => array_keys($incident->getChanges())],
                $request,
            );
        });

        return $this->loadIncident($incident->refresh());
    }

    public function assignIncident(Incident $incident, User $assignee, User $actor, ?string $note = null, ?Request $request = null): IncidentAssignment
    {
        return DB::transaction(function () use ($incident, $assignee, $actor, $note, $request): IncidentAssignment {
            $incident->assignments()
                ->where('is_active', true)
                ->update(['is_active' => false, 'released_at' => now()]);

            $assignment = $incident->assignments()->create([
                'assigned_to' => $assignee->id,
                'assigned_by' => $actor->id,
                'note' => $note,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $assignedStatus = IncidentStatus::query()->where('slug', 'assigned')->first();

            $incident->forceFill([
                'current_assignee_id' => $assignee->id,
                'status_id' => $assignedStatus?->id ?? $incident->status_id,
                'updated_by' => $actor->id,
            ])->save();

            $this->recordHistory(
                $incident,
                $actor,
                'assigned',
                sprintf('Assigned incident %s to %s.', $incident->ticket_number, $assignee->name),
                'current_assignee_id',
                null,
                $assignee->id,
            );

            $this->auditLogService->log(
                $actor,
                'incident.assigned',
                $incident,
                ['assigned_to' => $assignee->email],
                $request,
            );

            $this->notificationService->notifyUsers(
                [$assignee, $incident->reporter],
                'Incident Assignment Updated',
                sprintf('%s has been assigned to incident %s.', $assignee->name, $incident->ticket_number),
                'info',
                ['incident_id' => $incident->id],
            );

            return $assignment->load(['assignee.roles', 'assigner.roles']);
        });
    }

    public function changeStatus(Incident $incident, IncidentStatus $status, User $actor, ?string $resolutionNotes = null, ?Request $request = null): Incident
    {
        DB::transaction(function () use ($incident, $status, $actor, $resolutionNotes, $request): void {
            $oldStatusId = $incident->status_id;

            $payload = [
                'status_id' => $status->id,
                'updated_by' => $actor->id,
            ];

            if ($resolutionNotes) {
                $payload['resolution_notes'] = $resolutionNotes;
            }

            if ($status->slug === 'resolved') {
                $payload['resolved_at'] = now();
            }

            if ($status->slug === 'closed') {
                $payload['closed_at'] = now();
            }

            // When an analyst marks a ticket as 'failed', record a failed_jobs entry
            if ($status->slug === 'failed') {
                $payload['closed_at'] = now();
            }

            $incident->forceFill($payload)->save();

            $this->recordHistory(
                $incident,
                $actor,
                'status_changed',
                sprintf('Changed incident %s status to %s.', $incident->ticket_number, $status->name),
                'status_id',
                $oldStatusId,
                $status->id,
            );

            $this->auditLogService->log(
                $actor,
                'incident.status_changed',
                $incident,
                ['status' => $status->slug],
                $request,
            );

            $this->notificationService->notifyUsers(
                array_filter([$incident->reporter, $incident->currentAssignee]),
                'Incident Status Updated',
                sprintf('Incident %s is now %s.', $incident->ticket_number, $status->name),
                'success',
                ['incident_id' => $incident->id, 'status' => $status->slug],
            );

            // If status is failed, insert a record into failed_jobs for auditing/tracking.
            if ($status->slug === 'failed') {
                try {
                    DB::table('failed_jobs')->insert([
                        'uuid' => (string) Str::uuid(),
                        'connection' => config('queue.default') ?? 'sync',
                        'queue' => 'incidents',
                        'payload' => json_encode([
                            'incident_id' => $incident->id,
                            'ticket_number' => $incident->ticket_number,
                            'marked_by' => $actor->id,
                            'marked_by_name' => $actor->name,
                            'notes' => $resolutionNotes,
                            'timestamp' => now()->toDateTimeString(),
                        ]),
                        'exception' => json_encode(['message' => 'Marked failed by '.$actor->name, 'notes' => $resolutionNotes]),
                        'failed_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Do not abort the status change if failed_jobs insert fails; log to audit instead.
                    $this->auditLogService->log(
                        $actor,
                        'incident.failed_job_record_error',
                        $incident,
                        ['error' => $e->getMessage()],
                        $request,
                    );
                }
            }
        });

        return $this->loadIncident($incident->refresh());
    }

    public function addComment(Incident $incident, User $actor, string $body, bool $isInternal = false, ?Request $request = null): IncidentComment
    {
        return DB::transaction(function () use ($incident, $actor, $body, $isInternal, $request): IncidentComment {
            $comment = $incident->comments()->create([
                'user_id' => $actor->id,
                'body' => $body,
                'is_internal' => $isInternal,
            ]);

            $this->recordHistory(
                $incident,
                $actor,
                'comment_added',
                sprintf('Added a %scomment to incident %s.', $isInternal ? 'private ' : '', $incident->ticket_number),
            );

            $this->auditLogService->log(
                $actor,
                'incident.comment_added',
                $incident,
                ['comment_id' => $comment->id, 'is_internal' => $isInternal],
                $request,
            );

            $this->notificationService->notifyUsers(
                array_filter([$incident->reporter, $incident->currentAssignee]),
                'New Incident Comment',
                sprintf('%s commented on incident %s.', $actor->name, $incident->ticket_number),
                'info',
                ['incident_id' => $incident->id],
            );

            return $comment->load('user.roles');
        });
    }

    public function addAttachment(Incident $incident, User $actor, UploadedFile $file, ?Request $request = null): IncidentAttachment
    {
        return DB::transaction(function () use ($incident, $actor, $file, $request): IncidentAttachment {
            $storedPath = $file->storePublicly('incident-evidence/'.$incident->id, 'public');

            $attachment = $incident->attachments()->create([
                'user_id' => $actor->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => basename($storedPath),
                'disk' => 'public',
                'file_path' => $storedPath,
                'mime_type' => $file->getClientMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize(),
            ]);

            $this->recordHistory(
                $incident,
                $actor,
                'attachment_added',
                sprintf('Uploaded evidence %s to incident %s.', $file->getClientOriginalName(), $incident->ticket_number),
            );

            $this->auditLogService->log(
                $actor,
                'incident.attachment_added',
                $incident,
                ['attachment_id' => $attachment->id, 'file_name' => $attachment->original_name],
                $request,
            );

            return $attachment;
        });
    }

    public function loadIncident(Incident $incident): Incident
    {
        return $incident->load([
            'category',
            'status',
            'reporter.roles',
            'currentAssignee.roles',
            'assignments.assignee.roles',
            'assignments.assigner.roles',
            'comments.user.roles',
            'attachments.user.roles',
            'history.user.roles',
        ]);
    }

    private function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'INC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Incident::query()->where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    /**
     * @param  mixed  $oldValue
     * @param  mixed  $newValue
     */
    private function recordHistory(
        Incident $incident,
        User $actor,
        string $eventType,
        string $description,
        ?string $fieldName = null,
        mixed $oldValue = null,
        mixed $newValue = null,
    ): IncidentHistory {
        return $incident->history()->create([
            'user_id' => $actor->id,
            'event_type' => $eventType,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
        ]);
    }
}
