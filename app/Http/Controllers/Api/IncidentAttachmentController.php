<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\StoreIncidentAttachmentRequest;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;

class IncidentAttachmentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    public function store(StoreIncidentAttachmentRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('addAttachment', $incident);

        $attachment = $this->incidentService->addAttachment(
            $incident,
            $request->user(),
            $request->file('file'),
            $request,
        );

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'attachment' => $attachment,
        ], 201);
    }
}
