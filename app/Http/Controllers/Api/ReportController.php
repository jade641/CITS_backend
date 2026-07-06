<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\GenerateReportRequest;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        /** @var User $user */
        $user = $request->user();

        $reports = Report::query()
            ->when(! $user->hasAnyRole(['administrator', 'security-analyst']), fn ($query) => $query->where('generated_by', $user->id))
            ->latest('generated_at')
            ->paginate(20);

        return response()->json($reports);
    }

    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        return response()->json($this->reportService->summary($request->user(), $request->all()));
    }

    public function exportCsv(GenerateReportRequest $request)
    {
        $this->authorize('export', Report::class);

        return $this->reportService->exportCsv($request->user(), $request->validated());
    }

    public function exportPdf(GenerateReportRequest $request)
    {
        $this->authorize('export', Report::class);

        return $this->reportService->exportPdf($request->user(), $request->validated());
    }
}
