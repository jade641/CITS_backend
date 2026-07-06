<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        abort_unless($user->hasPermission($permission), Response::HTTP_FORBIDDEN, 'Insufficient permission.');

        return $next($request);
    }
}
