<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardSaveGroup extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(BoardSave::class, 'board_save_group_id')->orderByDesc('id');
    }
}
