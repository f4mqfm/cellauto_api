<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskSaveGroup;
use Illuminate\Http\Request;

class TaskSaveGroupController extends Controller
{
    public function index(Request $request)
    {
        return TaskSaveGroup::query()
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

        $group = TaskSaveGroup::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json($group, 201);
    }

    public function show(Request $request, TaskSaveGroup $task_save_group)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $task_save_group;
    }

    public function update(Request $request, TaskSaveGroup $task_save_group)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task_save_group->update([
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json($task_save_group);
    }

    public function destroy(Request $request, TaskSaveGroup $task_save_group)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $task_save_group->delete();

        return response()->json(['ok' => true]);
    }
}
