<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** admin vagy tanár — vizsga / értékelés megtekintés */
class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['admin', 'tanar'], true)) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $next($request);
    }
}
