<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if (! $user || ! $token) {
            return response()->json(['error' => 'Nincs hitelesített munkamenet'], 401);
        }

        if (! $user->active || $user->suspended_at !== null) {
            $token->delete();
            return response()->json(['error' => 'A felhasználó inaktív vagy fel van függesztve'], 403);
        }

        $idleTimeoutMinutes = (int) config('sanctum.idle_timeout', 120);
        if ($idleTimeoutMinutes > 0) {
            $lastActivityAt = $token->last_used_at ?? $token->created_at;

            if ($lastActivityAt && now()->diffInMinutes($lastActivityAt) >= $idleTimeoutMinutes) {
                $token->delete();
                return response()->json([
                    'error' => 'A munkamenet inaktivitás miatt lejárt, jelentkezz be újra',
                ], 401);
            }
        }

        return $next($request);
    }
}
