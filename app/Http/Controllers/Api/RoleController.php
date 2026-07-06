<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->hasPermission('roles.view'), 403, 'Insufficient permission.');

        return response()->json([
            'roles' => Role::query()->with(['permissions', 'users'])->orderBy('name')->get(),
        ]);
    }
}
