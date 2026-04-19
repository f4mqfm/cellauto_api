<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskEvaluation;
use Illuminate\Http\Request;

class StaffTaskEvaluationController extends Controller
{
    /**
     * Összes vizsgaértékelés (tanár / admin): lapozva, filled_board nélkül.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 30), 1), 100);

        $query = TaskEvaluation::query()
            ->with([
                'user:id,name,email,username,role',
                'taskSave:id,name,level,generation_mode,board_size,time_limit,task_save_group_id,user_id',
                'taskSave.taskSaveGroup:id,name',
                'taskSave.user:id,name,username',
            ])
            ->select([
                'task_evaluations.id',
                'task_evaluations.task_save_id',
                'task_evaluations.user_id',
                'task_evaluations.date',
                'task_evaluations.note',
                'task_evaluations.total_good_cell',
                'task_evaluations.good_cell',
                'task_evaluations.bad_cell',
                'task_evaluations.unfilled_cell',
                'task_evaluations.possible_sentence',
                'task_evaluations.good_sentence',
                'task_evaluations.bad_sentence',
                'task_evaluations.duplicate_sentence',
                'task_evaluations.completed_time',
                'task_evaluations.created_at',
                'task_evaluations.updated_at',
            ])
            ->orderByDesc('task_evaluations.date')
            ->orderByDesc('task_evaluations.id');

        if ($request->filled('user_id')) {
            $query->where('task_evaluations.user_id', (int) $request->query('user_id'));
        }
        if ($request->filled('task_save_id')) {
            $query->where('task_evaluations.task_save_id', (int) $request->query('task_save_id'));
        }
        if ($request->filled('from')) {
            $query->where('task_evaluations.date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('task_evaluations.date', '<=', $request->query('to'));
        }

        if ($request->filled('task_name')) {
            $term = $request->query('task_name');
            if (is_string($term) && $term !== '') {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->whereHas('taskSave', function ($q) use ($like) {
                    $q->where('name', 'like', $like);
                });
            }
        }

        if ($request->filled('note')) {
            $term = $request->query('note');
            if (is_string($term) && $term !== '') {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where('task_evaluations.note', 'like', $like);
            }
        }

        if ($request->filled('completed_time_min')) {
            $query->where('task_evaluations.completed_time', '>=', (int) $request->query('completed_time_min'));
        }

        if ($request->filled('completed_time_max')) {
            $query->where('task_evaluations.completed_time', '<=', (int) $request->query('completed_time_max'));
        }

        /** Cella % — megegyezik a frontend `cellaPercent()` képletével */
        if ($request->filled('cella_pct_min')) {
            $v = (float) $request->query('cella_pct_min');
            $query->whereRaw(
                'task_evaluations.total_good_cell > 0 AND (task_evaluations.good_cell - task_evaluations.bad_cell - task_evaluations.unfilled_cell) * 100.0 / task_evaluations.total_good_cell >= ?',
                [$v],
            );
        }
        if ($request->filled('cella_pct_max')) {
            $v = (float) $request->query('cella_pct_max');
            $query->whereRaw(
                'task_evaluations.total_good_cell > 0 AND (task_evaluations.good_cell - task_evaluations.bad_cell - task_evaluations.unfilled_cell) * 100.0 / task_evaluations.total_good_cell <= ?',
                [$v],
            );
        }

        /** Mondat % — megegyezik a frontend `mondatPercent()` képletével */
        if ($request->filled('mondat_pct_min')) {
            $v = (float) $request->query('mondat_pct_min');
            $query->whereRaw(
                'task_evaluations.possible_sentence > 0 AND task_evaluations.good_sentence * 100.0 / task_evaluations.possible_sentence >= ?',
                [$v],
            );
        }
        if ($request->filled('mondat_pct_max')) {
            $v = (float) $request->query('mondat_pct_max');
            $query->whereRaw(
                'task_evaluations.possible_sentence > 0 AND task_evaluations.good_sentence * 100.0 / task_evaluations.possible_sentence <= ?',
                [$v],
            );
        }

        return $query->paginate($perPage);
    }

    /**
     * Egy értékelés részletei (filled_board, sentence_result, feladat adatok).
     */
    public function show(TaskEvaluation $task_evaluation)
    {
        $task_evaluation->load([
            'user:id,name,email,username,role',
            'taskSave.taskSaveGroup:id,name',
            'taskSave.user:id,name,username,email',
        ]);

        return response()->json($task_evaluation);
    }
}
