<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\StoreIncidentRequest;
use App\Http\Requests\Incident\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\IncidentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(
        private readonly IncidentService $incidentService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Incident::class);

        /** @var User $user */
        $user = $request->user();

        $incidents = $this->incidentService->visibleIncidentsQuery($user)
            ->with([
                'category',
                'status',
                'reporter.roles',
                'currentAssignee.roles',
                'attachments.user.roles',
                'history.user.roles',
            ])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('severity'), fn (Builder $query) => $query->where('severity', $request->string('severity')->toString()))
            ->when($request->filled('status_id'), fn (Builder $query) => $query->where('status_id', $request->integer('status_id')))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('mine'), fn (Builder $query) => $query->where('reporter_id', $user->id))
            ->when($request->boolean('assigned_to_me'), fn (Builder $query) => $query->where('current_assignee_id', $user->id))
            ->paginate($request->integer('per_page', 15));

        return response()->json($incidents);
    }

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $this->authorize('create', Incident::class);

        $incident = $this->incidentService->createIncident($request->validated(), $request->user(), $request);

        return response()->json([
            'message' => 'Incident created successfully.',
            'incident' => $incident,
        ], 201);
    }

    public function show(Incident $incident): JsonResponse
    {
        $this->authorize('view', $incident);

        return response()->json([
            'incident' => $this->incidentService->loadIncident($incident),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('update', $incident);

        $incident = $this->incidentService->updateIncident($incident, $request->validated(), $request->user(), $request);

        return response()->json([
            'message' => 'Incident updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function destroy(Request $request, Incident $incident): JsonResponse
    {
        $this->authorize('delete', $incident);

        $incident->delete();

        $this->auditLogService->log($request->user(), 'incident.deleted', $incident, ['ticket_number' => $incident->ticket_number], $request);

        return response()->json([
            'message' => 'Incident deleted successfully.',
        ]);
    }

    public function updateStatus(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'status' => ['required', 'string', 'exists:incident_statuses,slug'],
        ]);

        $status = \App\Models\IncidentStatus::query()->where('slug', $request->input('status'))->firstOrFail();

        try {
            $incident = $this->incidentService->changeStatus($incident, $status, $request->user(), null, $request);
            return response()->json([
                'message' => 'Incident status updated successfully.',
                'incident' => $incident,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function saveTimeline(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'entries' => ['present', 'array'],
            'entries.*.occurred_at' => ['required', 'string'],
            'entries.*.description' => ['required', 'string'],
        ]);

        $incident = $this->incidentService->saveTimeline($incident, $request->input('entries'), $request->user(), $request);

        return response()->json([
            'message' => 'Incident timeline updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function saveIocs(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'entries' => ['present', 'array'],
            'entries.*.type' => ['required', 'string', 'in:IP,domain,hash,email,ip,Domain,Hash,Email'],
            'entries.*.value' => ['required', 'string'],
            'entries.*.description' => ['nullable', 'string'],
        ]);

        $incident = $this->incidentService->saveIocs($incident, $request->input('entries'), $request->user(), $request);

        return response()->json([
            'message' => 'Incident IOCs updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function saveAffectedSystems(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'entries' => ['present', 'array'],
            'entries.*.asset_name' => ['required', 'string'],
            'entries.*.asset_type' => ['required', 'string'],
            'entries.*.impact_level' => ['required', 'string', 'in:None,Low,Medium,High,none,low,medium,high'],
        ]);

        $incident = $this->incidentService->saveAffectedSystems($incident, $request->input('entries'), $request->user(), $request);

        return response()->json([
            'message' => 'Incident affected systems updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function saveActionsTaken(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'entries' => ['present', 'array'],
            'entries.*.occurred_at' => ['required', 'string'],
            'entries.*.action' => ['required', 'string'],
            'entries.*.performed_by' => ['required', 'string'],
        ]);

        $incident = $this->incidentService->saveActionsTaken($incident, $request->input('entries'), $request->user(), $request);

        return response()->json([
            'message' => 'Incident containment steps updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function saveSeverity(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'confidentiality_impact' => ['required', 'string', 'in:None,Low,Medium,High,none,low,medium,high'],
            'integrity_impact' => ['required', 'string', 'in:None,Low,Medium,High,none,low,medium,high'],
            'availability_impact' => ['required', 'string', 'in:None,Low,Medium,High,none,low,medium,high'],
            'affected_systems_count' => ['required', 'integer', 'min:0'],
            'data_sensitivity' => ['required', 'string', 'in:Public,Internal,Confidential,Restricted,public,internal,confidential,restricted'],
            'severity_override' => ['required', 'boolean'],
            'severity' => ['required_if:severity_override,true', 'nullable', 'string', 'in:low,medium,high,critical'],
            'severity_override_justification' => ['required_if:severity_override,true', 'nullable', 'string'],
        ]);

        $incident = $this->incidentService->saveSeverity($incident, $request->all(), $request->user(), $request);

        return response()->json([
            'message' => 'Incident severity updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function uploadEvidence(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // max 20MB
            'description' => ['required', 'string', 'min:1'],
        ]);

        $attachment = $this->incidentService->uploadEvidence(
            $incident,
            $request->file('file'),
            $request->input('description'),
            $request->user(),
            $request
        );

        return response()->json([
            'message' => 'Evidence uploaded successfully.',
            'attachment' => $attachment,
            'incident' => $this->incidentService->loadIncident($incident->refresh()),
        ]);
    }

    public function saveFindings(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'root_cause_category' => ['required', 'string', 'in:Human Error,System Vulnerability,Third-Party,Malicious Insider,External Attack'],
            'root_cause_explanation' => ['required', 'string'],
            'lessons_learned' => ['required', 'string'],
        ]);

        $incident = $this->incidentService->saveFindings($incident, $request->all(), $request->user(), $request);

        return response()->json([
            'message' => 'Incident findings updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function saveRemediationActions(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        $request->validate([
            'entries' => ['present', 'array'],
            'entries.*.description' => ['required', 'string'],
            'entries.*.owner_id' => ['required', 'integer', 'exists:users,id'],
            'entries.*.due_date' => ['required', 'date_format:Y-m-d'],
            'entries.*.status' => ['required', 'string', 'in:Pending,In Progress,Done'],
        ]);

        $incident = $this->incidentService->saveRemediationActions($incident, $request->input('entries'), $request->user(), $request);

        return response()->json([
            'message' => 'Remediation actions updated successfully.',
            'incident' => $incident,
        ]);
    }

    public function submitResolution(Request $request, Incident $incident): JsonResponse
    {
        if (! $request->user()->isSocAnalyst()) {
            return response()->json(['message' => 'Unauthorized. Only SOC Analysts can perform this action.'], 403);
        }

        // Validation: resolution cannot be submitted if any required Findings field is empty, or if no Evidence has been attached.
        if (empty($incident->root_cause_category) || empty($incident->root_cause_explanation) || empty($incident->lessons_learned)) {
            return response()->json([
                'message' => 'Validation failed: Root Cause Analysis and Lessons Learned must be completed before submission.'
            ], 422);
        }

        if ($incident->attachments()->count() === 0) {
            return response()->json([
                'message' => 'Validation failed: At least one Evidence file must be uploaded before submitting resolution.'
            ], 422);
        }

        $incident = $this->incidentService->submitResolution($incident, $request->user(), $request);

        return response()->json([
            'message' => 'Incident resolved and closed successfully.',
            'incident' => $incident,
        ]);
    }

    public function getAuditLog(Request $request, Incident $incident): JsonResponse
    {
        $auditLogs = \App\Models\AuditLog::query()
            ->where('entity_type', Incident::class)
            ->where('entity_id', $incident->id)
            ->with('user')
            ->latest('created_at')
            ->get();

        return response()->json([
            'audit_logs' => $auditLogs,
        ]);
    }
}
