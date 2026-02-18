<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tournament extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tournament_name',
        'slug',
        'vendor_name',
        'current_stage',
        'current_bto_session',
        'track_number',
        'bto_number',
        'bto_session_number',
        'max_racer_per_team',
        'champion_number',
        'best_race_enabled',
        'best_race_live_update',
        'best_race_number',
        'persiapan_delay',
        'panggilan_delay',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'best_race_enabled' => 'boolean',
        'best_race_live_update' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->tournament_name);
            }
        });
    }

    public function tokens()
    {
        return $this->hasMany(TournamentToken::class);
    }

    public function moderators()
    {
        return $this->belongsToMany(User::class, 'tournament_moderators', 'tournament_id', 'user_id')
            ->withPivot('id', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    public function participants()
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    public function races()
    {
        return $this->hasMany(Race::class);
    }

    public function couponHistory()
    {
        return $this->hasMany(CouponHistory::class);
    }

    public function bestTimes()
    {
        return $this->hasMany(BestTime::class);
    }

    public function results()
    {
        return $this->hasMany(TournamentResult::class);
    }

    public function tournamentRacerParticipants()
    {
        return $this->hasMany(TournamentRacerParticipant::class);
    }

    public function scannerDevices()
    {
        return $this->hasMany(ScannerDevice::class);
    }
}
