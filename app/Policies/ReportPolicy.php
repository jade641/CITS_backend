<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function view(User $user, Report $report): bool
    {
        return $user->hasPermission('reports.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }
}
