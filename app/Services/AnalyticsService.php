<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $incidents = $this->incidentService->visibleIncidentsQuery($user)
            ->with(['category', 'status', 'reporter', 'assignments' => fn ($query) => $query->oldest('assigned_at')])
            ->get();

        $resolved = $incidents->filter(fn (Incident $incident) => $this->completionTimestamp($incident) && $incident->reported_at);
        $resolvedIncidents = $incidents->filter(fn (Incident $incident) => in_array($incident->status?->slug, ['closed'], true));
        $meanResolutionHours = $resolved->isEmpty()
            ? 0.0
            : round($resolved->avg(fn (Incident $incident) => $incident->reported_at->diffInHours($incident->resolved_at)), 2);
        $meanResponseMinutes = $this->averageResponseMinutes($incidents);
        $totalIncidents = $incidents->count();

        return [
            'overview' => [
                'totalIncidents' => $totalIncidents,
                'openIncidents' => $incidents->filter(fn (Incident $incident) => in_array($incident->status?->slug, ['new', 'investigating', 'contained', 'eradicated', 'recovering'], true))->count(),
                'resolvedIncidents' => $resolvedIncidents->count(),
                'criticalIncidents' => $incidents->where('severity', 'critical')->count(),
                'resolutionRate' => $totalIncidents === 0 ? 0.0 : round(($resolvedIncidents->count() / $totalIncidents) * 100, 2),
                'averageResponseMinutes' => $meanResponseMinutes,
                'averageResolutionHours' => $meanResolutionHours,
            ],
            'incidentAnalytics' => [
                'bySeverity' => $incidents->groupBy('severity')->map->count()->all(),
                'byStatus' => $incidents->groupBy(fn ($incident) => $incident->status?->name ?? 'Unknown')->map->count()->all(),
                'byCategory' => $incidents->groupBy(fn ($incident) => $incident->category?->name ?? 'Unknown')->map->count()->all(),
                'monthlyTrend' => $this->buildMonthlyTrend($incidents),
                'performanceTrend' => $this->buildPerformanceTrend($incidents),
                'meanResolutionHours' => $meanResolutionHours,
                'meanResponseMinutes' => $meanResponseMinutes,
            ],
            'userActivityAnalytics' => AuditLog::query()
                ->select('user_id', 'action')
                ->with('user.roles')
                ->latest('created_at')
                ->limit(250)
                ->get()
                ->groupBy(fn (AuditLog $log) => $log->user?->email ?? 'system')
                ->map(function ($group) {
                    return [
                        'totalActions' => $group->count(),
                        'topActions' => $group->groupBy('action')->map->count()->sortDesc()->take(5)->all(),
                    ];
                })
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Incident>  $incidents
     * @return array<int, array{month: string, incidents: int, resolved: int, critical: int}>
     */
    private function buildMonthlyTrend(Collection $incidents): array
    {
        return $this->buildMonthlyBuckets()->map(function (array $bucket) use ($incidents): array {
            $reported = $incidents->filter(fn (Incident $incident) => $incident->reported_at?->format('Y-m') === $bucket['key']);
            $resolved = $incidents->filter(fn (Incident $incident) => $this->completionTimestamp($incident)?->format('Y-m') === $bucket['key']);

            return [
                'month' => $bucket['label'],
                'incidents' => $reported->count(),
                'resolved' => $resolved->count(),
                'critical' => $reported->where('severity', 'critical')->count(),
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Incident>  $incidents
     * @return array<int, array{month: string, responseMinutes: float, resolutionHours: float}>
     */
    private function buildPerformanceTrend(Collection $incidents): array
    {
        return $this->buildMonthlyBuckets()->map(function (array $bucket) use ($incidents): array {
            $monthlyIncidents = $incidents->filter(fn (Incident $incident) => $incident->reported_at?->format('Y-m') === $bucket['key']);

            return [
                'month' => $bucket['label'],
                'responseMinutes' => round($this->averageResponseMinutes($monthlyIncidents), 2),
                'resolutionHours' => round($this->averageResolutionHours($monthlyIncidents), 2),
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Incident>  $incidents
     */
    private function averageResponseMinutes(Collection $incidents): float
    {
        $durations = $incidents->map(function (Incident $incident): ?float {
            $reportedAt = $incident->reported_at;
            $firstAssignment = $incident->assignments->first();

            if (! $reportedAt || ! $firstAssignment?->assigned_at) {
                return null;
            }

            if ($firstAssignment->assigned_at->lessThan($reportedAt)) {
                return null;
            }

            return $reportedAt->diffInMinutes($firstAssignment->assigned_at);
        })->filter(fn ($duration): bool => $duration !== null);

        return $durations->isEmpty() ? 0.0 : round($durations->avg(), 2);
    }

    /**
     * @param  Collection<int, Incident>  $incidents
     */
    private function averageResolutionHours(Collection $incidents): float
    {
        $durations = $incidents->map(function (Incident $incident): ?float {
            $completedAt = $this->completionTimestamp($incident);

            if (! $incident->reported_at || ! $completedAt) {
                return null;
            }

            if ($completedAt->lessThan($incident->reported_at)) {
                return null;
            }

            return $incident->reported_at->diffInMinutes($completedAt) / 60;
        })->filter(fn ($duration): bool => $duration !== null);

        return $durations->isEmpty() ? 0.0 : round($durations->avg(), 2);
    }

    /**
     * @return Collection<int, array{key: string, label: string}>
     */
    private function buildMonthlyBuckets(): Collection
    {
        return collect(range(5, 0))->map(function (int $offset): array {
            $month = now()->startOfMonth()->subMonths($offset);

            return [
                'key' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
            ];
        });
    }

    private function completionTimestamp(Incident $incident): ?\Illuminate\Support\Carbon
    {
        return $incident->resolved_at ?? $incident->closed_at;
    }
}
