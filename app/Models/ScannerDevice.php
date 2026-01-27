<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScannerDevice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'device_code',
        'device_name',
        'tournament_id',
        'status',
        'last_seen_at',
        'firmware_version',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeLinked($query)
    {
        return $query->whereNotNull('tournament_id');
    }

    public function scopeUnlinked($query)
    {
        return $query->whereNull('tournament_id');
    }

    public function isLinked(): bool
    {
        return $this->tournament_id !== null;
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
