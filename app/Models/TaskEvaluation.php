<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvaluation extends Model
{
    protected $fillable = [
        'task_save_id',
        'user_id',
        'date',
        'note',
        'total_good_cell',
        'good_cell',
        'bad_cell',
        'possible_sentence',
        'good_sentence',
        'bad_sentence',
        'completed_time',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'total_good_cell' => 'integer',
            'good_cell' => 'integer',
            'bad_cell' => 'integer',
            'possible_sentence' => 'integer',
            'good_sentence' => 'integer',
            'bad_sentence' => 'integer',
            'completed_time' => 'integer',
        ];
    }

    public function taskSave(): BelongsTo
    {
        return $this->belongsTo(TaskSave::class, 'task_save_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
