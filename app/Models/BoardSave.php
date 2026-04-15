<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardSave extends Model
{
    protected $fillable = [
        'user_id',
        'board_save_group_id',
        'name',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boardSaveGroup(): BelongsTo
    {
        return $this->belongsTo(BoardSaveGroup::class, 'board_save_group_id');
    }
}
