<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('incidents.view');
    }

    public function view(User $user, Incident $incident): bool
    {
        if (! $user->hasPermission('incidents.view')) {
            return false;
        }

        return $user->hasAnyRole(['security-analyst', 'administrator'])
            || $incident->reporter_id === $user->id
            || $incident->current_assignee_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('incidents.create');
    }

    public function update(User $user, Incident $incident): bool
    {
        if (! $user->hasPermission('incidents.update')) {
            return false;
        }

        return $user->hasAnyRole(['security-analyst', 'administrator'])
            || ($incident->reporter_id === $user->id && is_null($incident->closed_at));
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->hasPermission('incidents.delete') && is_null($incident->closed_at);
    }

    public function assign(User $user, Incident $incident): bool
    {
        return $user->hasPermission('incidents.assign') && $this->view($user, $incident);
    }

    public function changeStatus(User $user, Incident $incident): bool
    {
        if (! $user->hasPermission('incidents.change-status')) {
            return false;
        }

        return $user->hasAnyRole(['security-analyst', 'administrator'])
            || $incident->current_assignee_id === $user->id;
    }

    public function addComment(User $user, Incident $incident): bool
    {
        return $user->hasPermission('incidents.comment') && $this->view($user, $incident);
    }

    public function addAttachment(User $user, Incident $incident): bool
    {
        return $user->hasPermission('incidents.upload') && $this->view($user, $incident);
    }
}
