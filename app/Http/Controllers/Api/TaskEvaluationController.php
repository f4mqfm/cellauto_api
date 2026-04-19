<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskEvaluation;
use App\Models\TaskSave;
use Illuminate\Http\Request;

class TaskEvaluationController extends Controller
{
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'sentence_result' => ['nullable', 'string'],
            'filled_board' => ['required', 'array'],
            'total_good_cell' => ['required', 'integer', 'min:0'],
            'good_cell' => ['required', 'integer', 'min:0'],
            'bad_cell' => ['required', 'integer', 'min:0'],
            'unfilled_cell' => ['required', 'integer', 'min:0'],
            'possible_sentence' => ['required', 'integer', 'min:0'],
            'good_sentence' => ['required', 'integer', 'min:0'],
            'bad_sentence' => ['required', 'integer', 'min:0'],
            'duplicate_sentence' => ['required', 'integer', 'min:0'],
            'completed_time' => ['required', 'integer', 'min:0'],
        ]);
    }

    public function index(Request $request, TaskSave $task_save)
    {
        $query = $task_save->evaluations()->with('user:id,name,email,username,role');

        $isOwner = (int) $task_save->user_id === (int) $request->user()->id;
        $isStaff = in_array($request->user()->role, ['admin', 'tanar'], true);
        if (! $isOwner && ! $isStaff) {
            $query->where('user_id', $request->user()->id);
        }

        return $query->get();
    }

    public function store(Request $request, TaskSave $task_save)
    {
        $validated = $this->validatePayload($request);

        $evaluation = TaskEvaluation::create([
            'task_save_id' => $task_save->id,
            'user_id' => $request->user()->id,
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
            'sentence_result' => $validated['sentence_result'] ?? null,
            'filled_board' => $validated['filled_board'],
            'total_good_cell' => (int) $validated['total_good_cell'],
            'good_cell' => (int) $validated['good_cell'],
            'bad_cell' => (int) $validated['bad_cell'],
            'unfilled_cell' => (int) $validated['unfilled_cell'],
            'possible_sentence' => (int) $validated['possible_sentence'],
            'good_sentence' => (int) $validated['good_sentence'],
            'bad_sentence' => (int) $validated['bad_sentence'],
            'duplicate_sentence' => (int) $validated['duplicate_sentence'],
            'completed_time' => (int) $validated['completed_time'],
        ]);

        return response()->json($evaluation, 201);
    }

    public function update(Request $request, TaskSave $task_save, TaskEvaluation $task_evaluation)
    {
        if ((int) $task_evaluation->task_save_id !== (int) $task_save->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $isOwnEvaluation = (int) $task_evaluation->user_id === (int) $request->user()->id;
        $isAdmin = $request->user()->role === 'admin';
        if (! $isOwnEvaluation && ! $isAdmin) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $this->validatePayload($request);

        $task_evaluation->update([
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
            'sentence_result' => $validated['sentence_result'] ?? null,
            'filled_board' => $validated['filled_board'],
            'total_good_cell' => (int) $validated['total_good_cell'],
            'good_cell' => (int) $validated['good_cell'],
            'bad_cell' => (int) $validated['bad_cell'],
            'unfilled_cell' => (int) $validated['unfilled_cell'],
            'possible_sentence' => (int) $validated['possible_sentence'],
            'good_sentence' => (int) $validated['good_sentence'],
            'bad_sentence' => (int) $validated['bad_sentence'],
            'duplicate_sentence' => (int) $validated['duplicate_sentence'],
            'completed_time' => (int) $validated['completed_time'],
        ]);

        return response()->json($task_evaluation);
    }

    public function destroy(Request $request, TaskSave $task_save, TaskEvaluation $task_evaluation)
    {
        if ((int) $task_evaluation->task_save_id !== (int) $task_save->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $isOwnEvaluation = (int) $task_evaluation->user_id === (int) $request->user()->id;
        $isAdmin = $request->user()->role === 'admin';
        if (! $isOwnEvaluation && ! $isAdmin) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $task_evaluation->delete();

        return response()->json(['ok' => true]);
    }
}
