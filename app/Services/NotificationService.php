<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * @param  iterable<User>  $users
     * @param  array<string, mixed>  $data
     */
    public function notifyUsers(iterable $users, string $title, string $message, string $type = 'info', array $data = []): void
    {
        Collection::make($users)
            ->unique('id')
            ->each(function (User $user) use ($title, $message, $type, $data): void {
                Notification::query()->create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'data' => $data,
                ]);
            });
    }
}
