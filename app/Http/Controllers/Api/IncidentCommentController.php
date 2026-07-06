<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\StoreIncidentCommentRequest;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentCommentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    public function store(StoreIncidentCommentRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('addComment', $incident);

        $comment = $this->incidentService->addComment(
            $incident,
            $request->user(),
            $request->validated('body'),
            $request->boolean('is_internal'),
            $request,
        );

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment,
        ], 201);
    }
}
