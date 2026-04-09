<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
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
}
