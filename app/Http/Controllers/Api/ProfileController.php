<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $payload = Arr::except($request->validated(), ['current_password']);
        if (empty($payload['password'])) {
            unset($payload['password']);
        }

        $user->fill($payload)->save();

        $this->auditLogService->log($user, 'profile.updated', $user, ['fields' => array_keys($payload)], $request);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->refresh()->load('roles.permissions'),
        ]);
    }
}
