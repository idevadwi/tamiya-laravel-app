<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['team_name', 'created_by', 'updated_by'];

    public function racers()
    {
        return $this->hasMany(Racer::class);
    }

    public function tournamentParticipants()
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    public function bestTimes()
    {
        return $this->hasMany(BestTime::class);
    }

    public function tournamentResults()
    {
        return $this->hasMany(TournamentResult::class);
    }

    public function tournamentRacerParticipants()
    {
        return $this->hasMany(TournamentRacerParticipant::class);
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
