<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        ?User $user,
        string $action,
        Model|string|null $subject = null,
        array $metadata = [],
        ?Request $request = null,
    ): AuditLog {
        $entityType = null;
        $entityId = null;

        if ($subject instanceof Model) {
            $entityType = $subject::class;
            $entityId = (int) $subject->getKey();
        } elseif (is_string($subject)) {
            $entityType = $subject;
        }

        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
