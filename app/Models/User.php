<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'active',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function lists(): HasMany
    {
        return $this->hasMany(WordList::class, 'user_id');
    }

    public function colorLists(): HasMany
    {
        return $this->hasMany(ColorList::class, 'user_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class, 'user_id');
    }

    public function taskSaveGroups(): HasMany
    {
        return $this->hasMany(TaskSaveGroup::class, 'user_id');
    }

    public function taskSaves(): HasMany
    {
        return $this->hasMany(TaskSave::class, 'user_id');
    }

    public function taskEvaluations(): HasMany
    {
        return $this->hasMany(TaskEvaluation::class, 'user_id');
    }
}
