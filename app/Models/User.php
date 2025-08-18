<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ...existing code...
use Filament\Panel;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ...existing code...

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'user_level_id'
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

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type');
    }

    // ...existing code...

    // ...existing code...

    public function hasLevel(int $level): bool
    {
        return $this->user_level_id >= $level;
    }

    public function hasAtleastLevel(int $level): bool
    {
        return $this->user_level_id >= $level;
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
