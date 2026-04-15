<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        return User::query()->get();
    }

    public function onlineStatus()
    {
        $users = User::query()->get();
        $userIds = $users->pluck('id')->all();
        if (empty($userIds)) {
            return $users;
        }

        $tokenRows = DB::table('personal_access_tokens')
            ->select('tokenable_id', DB::raw('COUNT(*) as active_token_count'))
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $userIds)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->groupBy('tokenable_id')
            ->get();
        $tokenCountMap = [];
        foreach ($tokenRows as $row) {
            $tokenCountMap[(int) $row->tokenable_id] = (int) $row->active_token_count;
        }

        $lastSeenRows = AccessLog::query()
            ->select('user_id', DB::raw('MAX(occurred_at) as last_seen_at'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get();
        $lastSeenMap = [];
        foreach ($lastSeenRows as $row) {
            $lastSeenMap[(int) $row->user_id] = $row->last_seen_at;
        }

        return $users->map(function (User $user) use ($tokenCountMap, $lastSeenMap) {
            $arr = $user->toArray();
            $tokenCount = $tokenCountMap[$user->id] ?? 0;
            $arr['is_logged_in'] = $tokenCount > 0;
            $arr['active_token_count'] = $tokenCount;
            $arr['last_seen_at'] = $lastSeenMap[$user->id] ?? null;
            return $arr;
        });
    }

    public function store(Request $request)
    {
	if ($request->user()->role !== 'admin') {
 	   return response()->json(['error' => 'Nincs jogosultság'], 403);
	}
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $request->username,
            'role' => $request->role ?? 'vendeg'
        ]);

        return response()->json($user);
    }

    public function suspend($id)
    {
    	$user = User::findOrFail($id);

	    $user->active = false;
	    $user->suspended_at = now();
	    $user->save();

	    return response()->json([
        	'message' => 'Felhasználó felfüggesztve',
	        'user' => $user
    	]);
	}

	public function unsuspend($id)
	{
	    $user = User::findOrFail($id);

	    $user->active = true;
	    $user->suspended_at = null;
	    $user->save();

	    return response()->json([
	        'message' => 'Felhasználó újra aktiválva',
	        'user' => $user
	    ]);
	}

public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $user->name = $request->name ?? $user->name;
    $user->email = $request->email ?? $user->email;
    $user->username = $request->username ?? $user->username;
    $user->role = $request->role ?? $user->role;

    if ($request->password) {
        $user->password = \Hash::make($request->password);
    }

    $user->save();

    return response()->json($user);
}

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $actor = $request->user();
        $targetId = (int) $id;

        if ($targetId === (int) $actor->id) {
            return response()->json(['error' => 'Nem törölheted önmagadat.'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['error' => 'Admin szerepkörű felhasználó nem törölhető.'], 403);
        }

        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json(['ok' => true]);
    }
}
