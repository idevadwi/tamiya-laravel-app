<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['role_name', 'created_by', 'updated_by'];

    public $incrementing = false;
    protected $keyType = 'string';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withTimestamps();
    }
}
