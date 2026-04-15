<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordList extends Model
{
    protected $table = 'lists';

    protected $fillable = [
        'user_id',
        'name',
        'public',
    ];

    protected function casts(): array
    {
        return [
            'public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function words(): HasMany
    {
        return $this->hasMany(Word::class, 'list_id')
            ->orderBy('generation')
            ->orderBy('word')
            ->orderBy('id');
    }
}

