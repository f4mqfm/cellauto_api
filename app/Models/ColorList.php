<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColorList extends Model
{
    protected $table = 'color_lists';

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(Color::class, 'list_id')->orderBy('position')->orderBy('id');
    }
}
