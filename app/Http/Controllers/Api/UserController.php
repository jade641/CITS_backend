<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles.permissions')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->paginate(15);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $payload = Arr::except($request->validated(), ['role_ids', 'password_confirmation']);

        $user = User::query()->create($payload + ['email_verified_at' => now()]);
        $user->roles()->sync($request->validated('role_ids'));

        $this->auditLogService->log($request->user(), 'user.created', $user, ['email' => $user->email], $request);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user->load('roles.permissions'),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $payload = Arr::except($request->validated(), ['role_ids', 'password_confirmation']);
        if (empty($payload['password'])) {
            unset($payload['password']);
        }

        $user->fill($payload)->save();
        $user->roles()->sync($request->validated('role_ids'));

        $this->auditLogService->log($request->user(), 'user.updated', $user, ['email' => $user->email], $request);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->refresh()->load('roles.permissions'),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        $this->auditLogService->log($request->user(), 'user.deleted', $user, ['email' => $user->email], $request);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
