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
}
