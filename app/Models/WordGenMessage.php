<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordGenMessage extends Model
{
    protected $fillable = [
        'list_id',
        'generation',
        'correct_answer_message',
        'incorrect_answer_message',
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
}
