<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(
        private readonly IncidentService $incidentService,
        private readonly AnalyticsService $analyticsService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function summary(User $user, array $filters = []): array
    {
        $query = $this->applyFilters($this->incidentService->visibleIncidentsQuery($user), $filters);
        $incidents = (clone $query)->get();

        return [
            'totalIncidents' => $incidents->count(),
            'severityAnalytics' => $incidents->groupBy('severity')->map->count()->all(),
            'statusAnalytics' => $incidents->groupBy(fn (Incident $incident) => $incident->status?->name ?? 'Unknown')->map->count()->all(),
            'userActivityAnalytics' => $this->analyticsService->build($user)['userActivityAnalytics'],
            'incidentAnalytics' => $this->analyticsService->build($user)['incidentAnalytics'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(User $user, array $filters = []): StreamedResponse
    {
        $incidents = $this->applyFilters($this->incidentService->visibleIncidentsQuery($user), $filters)->get();
        $summary = $this->summary($user, $filters);
        $filename = 'incident-report-'.now()->format('Ymd-His').'.csv';

        $report = $this->createReportRecord($user, 'Incident CSV Report', 'incident-export', 'csv', $filters, $summary);

        $this->auditLogService->log($user, 'report.exported_csv', $report, ['filename' => $filename]);

        return response()->streamDownload(function () use ($incidents): void {
            $output = fopen('php://output', 'wb');

            fputcsv($output, ['Ticket Number', 'Title', 'Severity', 'Category', 'Status', 'Reporter', 'Assignee', 'Reported At']);

            foreach ($incidents as $incident) {
                fputcsv($output, [
                    $incident->ticket_number,
                    $incident->title,
                    ucfirst($incident->severity),
                    $incident->category?->name,
                    $incident->status?->name,
                    $incident->reporter?->email,
                    $incident->currentAssignee?->email,
                    $incident->reported_at?->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportPdf(User $user, array $filters = []): StreamedResponse
    {
        $incidents = $this->applyFilters($this->incidentService->visibleIncidentsQuery($user), $filters)->get();
        $summary = $this->summary($user, $filters);
        $filename = 'incident-report-'.now()->format('Ymd-His').'.pdf';

        $report = $this->createReportRecord($user, 'Incident PDF Report', 'incident-export', 'pdf', $filters, $summary);

        $this->auditLogService->log($user, 'report.exported_pdf', $report, ['filename' => $filename]);

        $pdf = Pdf::loadView('reports.incidents', [
            'summary' => $summary,
            'incidents' => $incidents,
            'generatedAt' => now(),
        ]);

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function createReportRecord(User $user, string $name, string $type, string $format, array $filters, array $summary): Report
    {
        return Report::query()->create([
            'generated_by' => $user->id,
            'name' => $name,
            'type' => $type,
            'format' => $format,
            'status' => 'generated',
            'filters' => $filters,
            'summary' => $summary,
            'generated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['date_from'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('reported_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, string $date) => $builder->whereDate('reported_at', '<=', $date))
            ->when($filters['status_ids'] ?? null, fn (Builder $builder, array $statusIds) => $builder->whereIn('status_id', $statusIds))
            ->when($filters['category_ids'] ?? null, fn (Builder $builder, array $categoryIds) => $builder->whereIn('category_id', $categoryIds))
            ->when($filters['severities'] ?? null, fn (Builder $builder, array $severities) => $builder->whereIn('severity', $severities))
            ->when($filters['reporter_id'] ?? null, fn (Builder $builder, int $reporterId) => $builder->where('reporter_id', $reporterId))
            ->when($filters['assigned_to'] ?? null, fn (Builder $builder, int $assignedTo) => $builder->where('current_assignee_id', $assignedTo));
    }
}
