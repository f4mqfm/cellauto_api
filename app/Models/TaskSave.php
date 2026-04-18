<?php

namespace App\Models;

use App\Enums\TaskSaveLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskSave extends Model
{
    protected $fillable = [
        'user_id',
        'task_save_group_id',
        'word_list_id',
        'name',
        'level',
        'generation_mode',
        'board_size',
        'generations_count',
        'time_limit',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'level' => TaskSaveLevel::class,
            'word_list_id' => 'integer',
            'board_size' => 'integer',
            'generations_count' => 'integer',
            'time_limit' => 'integer',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskSaveGroup(): BelongsTo
    {
        return $this->belongsTo(TaskSaveGroup::class, 'task_save_group_id');
    }

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class, 'word_list_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TaskEvaluation::class, 'task_save_id')->orderByDesc('date')->orderByDesc('id');
    }
}
