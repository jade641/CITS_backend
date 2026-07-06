<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\IncidentStatus;
use App\Models\User;

class DashboardService
{
    public function __construct(private readonly IncidentService $incidentService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $incidentQuery = $this->incidentService->visibleIncidentsQuery($user);
        $openStatusIds = IncidentStatus::query()->whereIn('slug', ['open', 'assigned', 'in_progress'])->pluck('id');
        $resolvedStatusIds = IncidentStatus::query()->whereIn('slug', ['resolved', 'closed'])->pluck('id');

        $recentActivities = AuditLog::query()
            ->with('user.roles')
            ->when(
                ! $user->hasAnyRole(['administrator', 'security-analyst']),
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->latest('created_at')
            ->limit(10)
            ->get();

        $severityBreakdown = (clone $incidentQuery)
            ->get(['severity'])
            ->groupBy('severity')
            ->map->count()
            ->all();

        $statusBreakdown = (clone $incidentQuery)
            ->with('status')
            ->get()
            ->groupBy(fn ($incident) => $incident->status?->name ?? 'Unknown')
            ->map->count()
            ->all();

        return [
            'widgets' => [
                'totalIncidents' => (clone $incidentQuery)->count(),
                'openIncidents' => (clone $incidentQuery)->whereIn('status_id', $openStatusIds)->count(),
                'resolvedIncidents' => (clone $incidentQuery)->whereIn('status_id', $resolvedStatusIds)->count(),
                'criticalIncidents' => (clone $incidentQuery)->where('severity', 'critical')->count(),
            ],
            'recentActivities' => $recentActivities,
            'securityMetrics' => [
                'severityBreakdown' => $severityBreakdown,
                'statusBreakdown' => $statusBreakdown,
            ],
        ];
    }
}
