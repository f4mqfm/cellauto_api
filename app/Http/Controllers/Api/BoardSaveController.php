<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardSave;
use App\Models\BoardSaveGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoardSaveController extends Controller
{
    public function index(Request $request, BoardSaveGroup $board_save_group)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $board_save_group->saves()->get();
    }

    public function store(Request $request, BoardSaveGroup $board_save_group)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('board_saves', 'name')->where(
                    fn ($q) => $q->where('board_save_group_id', $board_save_group->id)
                ),
            ],
            'payload' => ['required', 'array'],
        ]);

        $save = BoardSave::create([
            'user_id' => $request->user()->id,
            'board_save_group_id' => $board_save_group->id,
            'name' => $validated['name'],
            'payload' => $validated['payload'],
        ]);

        return response()->json($save, 201);
    }

    public function show(Request $request, BoardSaveGroup $board_save_group, BoardSave $save)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ($save->board_save_group_id !== $board_save_group->id || $save->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        return $save;
    }

    public function update(Request $request, BoardSaveGroup $board_save_group, BoardSave $save)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ($save->board_save_group_id !== $board_save_group->id || $save->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('board_saves', 'name')
                    ->where(
                        fn ($q) => $q->where('board_save_group_id', $board_save_group->id)
                    )
                    ->ignore($save->id),
            ],
            'payload' => ['required', 'array'],
        ]);

        $save->update([
            'name' => $validated['name'],
            'payload' => $validated['payload'],
        ]);

        return response()->json($save);
    }

    public function destroy(Request $request, BoardSaveGroup $board_save_group, BoardSave $save)
    {
        if ($board_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ($save->board_save_group_id !== $board_save_group->id || $save->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $save->delete();

        return response()->json(['ok' => true]);
    }
}
