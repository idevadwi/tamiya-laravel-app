<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Race extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tournament_id', 'stage', 'race_no', 'track', 'lane', 'racer_id',
        'team_id', 'card_id', 'race_time', 'is_called', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_called' => 'boolean',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function racer()
    {
        return $this->belongsTo(Racer::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
