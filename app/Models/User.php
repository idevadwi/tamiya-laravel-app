<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'phone',
        'email',
        'password',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }

    public function tournamentTokens()
    {
        return $this->hasMany(TournamentToken::class);
    }

    public function moderatedTournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_moderators');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('role_name', $roleNames)->exists();
    }

    /**
     * Get the user's display name (for AdminLTE and other UI components)
     */
    public function getNameAttribute(): string
    {
        return $this->email ?? $this->phone ?? 'User';
    }
}
