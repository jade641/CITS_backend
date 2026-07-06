<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\ChangeIncidentStatusRequest;
use App\Models\Incident;
use App\Models\IncidentStatus;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentStatusController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    public function store(ChangeIncidentStatusRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('changeStatus', $incident);

        $status = IncidentStatus::query()->findOrFail($request->integer('status_id'));

        $incident = $this->incidentService->changeStatus(
            $incident,
            $status,
            $request->user(),
            $request->validated('resolution_notes'),
            $request,
        );

        return response()->json([
            'message' => 'Incident status updated successfully.',
            'incident' => $incident,
        ]);
    }
}
