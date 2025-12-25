<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Racer extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['racer_name', 'image', 'team_id', 'created_by', 'updated_by'];
    protected $appends = ['image_url'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function races()
    {
        return $this->hasMany(Race::class);
    }

    public function tournamentResults()
    {
        return $this->hasMany(TournamentResult::class);
    }

    public function tournamentRacerParticipants()
    {
        return $this->hasMany(TournamentRacerParticipant::class);
    }

    /**
     * Get the full URL for the racer's image.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
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
