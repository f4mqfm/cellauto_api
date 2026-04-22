<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskSaveLevel;
use App\Http\Controllers\Controller;
use App\Models\TaskSave;
use App\Models\TaskSaveGroup;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskSaveController extends Controller
{
    private function validateWordListGenerationCount(?int $wordListId, int $generationsCount): ?array
    {
        if ($wordListId === null) {
            return null;
        }

        $listGenerationCount = Word::query()
            ->where('list_id', $wordListId)
            ->distinct('generation')
            ->count('generation');

        if ($listGenerationCount !== $generationsCount) {
            return [
                'error' => 'A kiválasztott szólista generációszáma nem egyezik a beállított generációk számával',
            ];
        }

        return null;
    }

    public function index(Request $request, TaskSaveGroup $task_save_group)
    {
        return $task_save_group->saves()->with('user:id,name,username,email')->get();
    }

    public function store(Request $request, TaskSaveGroup $task_save_group)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_saves', 'name')->where(
                    fn ($q) => $q->where('task_save_group_id', $task_save_group->id)
                ),
            ],
            'level' => ['required', Rule::enum(TaskSaveLevel::class)],
            'generation_mode' => ['required', Rule::in(['square_lateral', 'square_apex', 'hexagonal'])],
            'board_size' => ['required', 'integer', 'min:1'],
            'generations_count' => ['required', 'integer', 'min:1'],
            'word_list_id' => ['nullable', 'integer', Rule::exists('lists_word', 'id')],
            'time_limit' => ['required', 'integer', 'min:1'],
            'payload' => ['required', 'array'],
        ]);

        $wordListError = $this->validateWordListGenerationCount(
            $validated['word_list_id'] ?? null,
            (int) $validated['generations_count']
        );
        if ($wordListError !== null) {
            return response()->json($wordListError, 422);
        }

        $save = TaskSave::create([
            'user_id' => $request->user()->id,
            'task_save_group_id' => $task_save_group->id,
            'word_list_id' => $validated['word_list_id'] ?? null,
            'name' => $validated['name'],
            'level' => $validated['level'],
            'generation_mode' => $validated['generation_mode'],
            'board_size' => (int) $validated['board_size'],
            'generations_count' => (int) $validated['generations_count'],
            'time_limit' => (int) $validated['time_limit'],
            'payload' => $validated['payload'],
        ]);

        return response()->json($save, 201);
    }

    public function show(Request $request, TaskSaveGroup $task_save_group, TaskSave $save)
    {
        if ($save->task_save_group_id !== $task_save_group->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $save->loadMissing('user:id,name,username,email');

        return $save;
    }

    public function update(Request $request, TaskSaveGroup $task_save_group, TaskSave $save)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ($save->task_save_group_id !== $task_save_group->id || $save->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_saves', 'name')
                    ->where(
                        fn ($q) => $q->where('task_save_group_id', $task_save_group->id)
                    )
                    ->ignore($save->id),
            ],
            'level' => ['required', Rule::enum(TaskSaveLevel::class)],
            'generation_mode' => ['required', Rule::in(['square_lateral', 'square_apex', 'hexagonal'])],
            'board_size' => ['required', 'integer', 'min:1'],
            'generations_count' => ['required', 'integer', 'min:1'],
            'word_list_id' => ['nullable', 'integer', Rule::exists('lists_word', 'id')],
            'time_limit' => ['required', 'integer', 'min:1'],
            'payload' => ['required', 'array'],
        ]);

        $wordListError = $this->validateWordListGenerationCount(
            $validated['word_list_id'] ?? null,
            (int) $validated['generations_count']
        );
        if ($wordListError !== null) {
            return response()->json($wordListError, 422);
        }

        $save->update([
            'word_list_id' => $validated['word_list_id'] ?? null,
            'name' => $validated['name'],
            'level' => $validated['level'],
            'generation_mode' => $validated['generation_mode'],
            'board_size' => (int) $validated['board_size'],
            'generations_count' => (int) $validated['generations_count'],
            'time_limit' => (int) $validated['time_limit'],
            'payload' => $validated['payload'],
        ]);

        return response()->json($save);
    }

    public function destroy(Request $request, TaskSaveGroup $task_save_group, TaskSave $save)
    {
        if ($task_save_group->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ($save->task_save_group_id !== $task_save_group->id || $save->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $save->delete();

        return response()->json(['ok' => true]);
    }
}
