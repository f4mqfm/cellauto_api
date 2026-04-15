<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}

