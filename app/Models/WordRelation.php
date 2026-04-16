<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordRelation extends Model
{
    protected $table = 'word_relations';

    protected $fillable = [
        'list_id',
        'from_word_id',
        'to_word_id',
    ];

    protected function casts(): array
    {
        return [
            'list_id' => 'integer',
            'from_word_id' => 'integer',
            'to_word_id' => 'integer',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(WordList::class, 'list_id');
    }

    public function fromWord(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'from_word_id');
    }

    public function toWord(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'to_word_id');
    }
}

