<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TournamentResult extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['tournament_id', 'category', 'rank', 'team_id', 'racer_id', 'created_by', 'updated_by'];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function racer()
    {
        return $this->belongsTo(Racer::class);
    }
}
