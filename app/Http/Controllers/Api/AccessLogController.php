<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessLogController extends Controller
{
    private function resolveClientIp(Request $request): string
    {
        $candidates = [];

        $xff = (string) ($request->header('X-Forwarded-For') ?? '');
        if ($xff !== '') {
            $parts = array_map('trim', explode(',', $xff));
            foreach ($parts as $ip) {
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    $candidates[] = $ip;
                }
            }
        }

        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP'] as $header) {
            $value = (string) ($request->header($header) ?? '');
            if ($value !== '' && filter_var($value, FILTER_VALIDATE_IP)) {
                $candidates[] = $value;
            }
        }

        foreach ($candidates as $ip) {
            if ($ip === '127.0.0.1' || $ip === '::1') {
                continue;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        foreach ($candidates as $ip) {
            if ($ip !== '127.0.0.1' && $ip !== '::1') {
                return $ip;
            }
        }

        return $request->ip() ?? 'unknown';
    }

    public function storeVisit(Request $request)
    {
        $validated = $request->validate([
            'entry_point' => ['required', 'in:www,admin'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $occurredAt = $validated['occurred_at'] ?? now();

        $sanctumUser = Auth::guard('sanctum')->user();
        $log = AccessLog::create([
            'user_id' => $sanctumUser?->id ?? $request->user()?->id,
            'event_type' => 'visit',
            'entry_point' => $validated['entry_point'],
            'ip_address' => $this->resolveClientIp($request),
            'user_agent' => $request->userAgent(),
            'occurred_at' => $occurredAt,
        ]);

        return response()->json($log, 201);
    }

    public function myLogs(Request $request)
    {
        return AccessLog::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(100);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'event_type' => ['sometimes', 'in:visit,login,logout'],
            'entry_point' => ['sometimes', 'in:www,admin'],
            'user_id' => ['sometimes', 'integer', 'min:1'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);

        $query = AccessLog::query()->with('user:id,name,email,username,role');

        if (isset($validated['event_type'])) {
            $query->where('event_type', $validated['event_type']);
        }

        if (isset($validated['entry_point'])) {
            $query->where('entry_point', $validated['entry_point']);
        }

        if (isset($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (isset($validated['from'])) {
            $query->where('occurred_at', '>=', $validated['from']);
        }

        if (isset($validated['to'])) {
            $query->where('occurred_at', '<=', $validated['to']);
        }

        return $query
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 100);
    }
}
