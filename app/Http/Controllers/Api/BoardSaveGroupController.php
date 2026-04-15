<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardSaveGroup;
use Illuminate\Http\Request;

class BoardSaveGroupController extends Controller
{
    public function index(Request $request)
    {
        return BoardSaveGroup::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $group = BoardSaveGroup::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json($group, 201);
    }

    public function show(Request $request, BoardSaveGroup $board_save_group)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $board_save_group;
    }

    public function update(Request $request, BoardSaveGroup $board_save_group)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $board_save_group->update([
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json($board_save_group);
    }

    public function destroy(Request $request, BoardSaveGroup $board_save_group)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $board_save_group->delete();

        return response()->json(['ok' => true]);
    }
}
