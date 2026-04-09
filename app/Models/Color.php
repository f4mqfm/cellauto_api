<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Color extends Model
{
    protected $table = 'colors';

    protected $fillable = [
        'list_id',
        'color',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function colorList(): BelongsTo
    {
        return $this->belongsTo(ColorList::class, 'list_id');
    }
}
