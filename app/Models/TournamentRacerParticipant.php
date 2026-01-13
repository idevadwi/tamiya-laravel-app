<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentRacerParticipant extends Model
{
    protected $fillable = [
        'id',
        'tournament_id',
        'team_id',
        'racer_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'tournament_id' => 'string',
        'team_id' => 'string',
        'racer_id' => 'string',
        'is_active' => 'boolean',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the tournament this participant belongs to.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the team this participant belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the racer for this participant.
     */
    public function racer()
    {
        return $this->belongsTo(Racer::class);
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
