<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\AuthorizationMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->create([
                ...$request->safe()->except(['password_confirmation']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $defaultRole = Role::query()->where('slug', AuthorizationMatrix::USER)->firstOrFail();
            $user->roles()->syncWithoutDetaching([$defaultRole->id]);

            return $user;
        });

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->auditLogService->log($user, 'auth.registered', $user, ['email' => $user->email], $request);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user->load('roles.permissions'),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->auditLogService->log($user, 'auth.login', $user, ['email' => $user->email], $request);

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');

        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $this->auditLogService->log($user, 'auth.logout', $user, ['email' => $user->email], $request);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
