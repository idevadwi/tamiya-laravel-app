<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TournamentCardAssignment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tournament_id',
        'card_id',
        'racer_id',
        'status',
        'returned_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function racer()
    {
        return $this->belongsTo(Racer::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
