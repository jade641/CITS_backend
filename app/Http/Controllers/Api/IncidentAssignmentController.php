<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\AssignIncidentRequest;
use App\Models\Incident;
use App\Models\User;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentAssignmentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    public function store(AssignIncidentRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('assign', $incident);

        $assignee = User::query()->findOrFail($request->integer('assigned_to'));
        abort_unless($assignee->hasAnyRole(['security-analyst']), 422, 'Assigned user must be a security analyst.');

        $assignment = $this->incidentService->assignIncident(
            $incident,
            $assignee,
            $request->user(),
            $request->validated('note'),
            $request,
        );

        return response()->json([
            'message' => 'Incident assigned successfully.',
            'assignment' => $assignment,
            'incident' => $this->incidentService->loadIncident($incident->refresh()),
        ]);
    }
}
