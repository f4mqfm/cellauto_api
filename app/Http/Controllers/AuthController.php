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
        foreach ($request->ips() as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP)) {
                continue;
            }
            if ($ip === '127.0.0.1' || $ip === '::1') {
                continue;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        foreach ($request->ips() as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '127.0.0.1' && $ip !== '::1') {
                return $ip;
            }
        }

        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP', 'X-Forwarded-For'] as $header) {
            $raw = (string) ($request->header($header) ?? '');
            if ($raw === '') {
                continue;
            }

            foreach (array_map('trim', explode(',', $raw)) as $candidate) {
                if ($candidate === '127.0.0.1' || $candidate === '::1') {
                    continue;
                }
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        $ip = $request->getClientIp();
        if (is_string($ip) && $ip !== '') {
            return $ip;
        }

        return 'unknown';
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
