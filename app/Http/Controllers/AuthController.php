<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function resolveClientIp(Request $request): string
    {
        $xff = (string) ($request->header('X-Forwarded-For') ?? '');
        if ($xff !== '') {
            $parts = array_map('trim', explode(',', $xff));
            foreach ($parts as $ip) {
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        $xri = (string) ($request->header('X-Real-IP') ?? '');
        if ($xri !== '' && filter_var($xri, FILTER_VALIDATE_IP)) {
            return $xri;
        }

        return $request->ip() ?? 'unknown';
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'entry_point' => ['required', 'in:www,admin'],
        ]);

        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['error' => 'Hibás adatok'], 401);
        }

        if (!$user->active || $user->suspended_at !== null) {
            return response()->json(['error' => 'A felhasználó fel van függesztve'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        AccessLog::create([
            'user_id' => $user->id,
            'event_type' => 'login',
            'entry_point' => $validated['entry_point'],
            'ip_address' => $this->resolveClientIp($request),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $validated = $request->validate([
            'entry_point' => ['required', 'in:www,admin'],
        ]);

        AccessLog::create([
            'user_id' => $request->user()->id,
            'event_type' => 'logout',
            'entry_point' => $validated['entry_point'],
            'ip_address' => $this->resolveClientIp($request),
            'user_agent' => $request->userAgent(),
            'occurred_at' => now(),
        ]);

        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }
}
