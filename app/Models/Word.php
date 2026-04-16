<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    protected $table = 'words';

    protected $fillable = [
        'list_id',
        'generation',
        'word',
    ];

    protected function casts(): array
    {
        return [
            'generation' => 'integer',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(WordList::class, 'list_id');
    }

    public function outgoingRelations(): HasMany
    {
        return $this->hasMany(WordRelation::class, 'from_word_id');
    }

    public function incomingRelations(): HasMany
    {
        return $this->hasMany(WordRelation::class, 'to_word_id');
    }
}

