<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['card_code', 'card_no', 'coupon', 'status', 'created_by', 'updated_by'];

    public function tournamentAssignments()
    {
        return $this->hasMany(TournamentCardAssignment::class);
    }

    /**
     * Get the racer assigned to this card in the active tournament.
     * Provides backward-compatible $card->racer access.
     */
    public function racer()
    {
        $tournamentId = getActiveTournament()?->id;

        return $this->hasOneThrough(
            Racer::class,
            TournamentCardAssignment::class,
            'card_id',    // FK on tournament_card_assignments → cards
            'id',         // PK on racers
            'id',         // PK on cards (local key)
            'racer_id'    // FK on tournament_card_assignments → racers
        )->when($tournamentId, fn ($q) => $q->where(
            'tournament_card_assignments.tournament_id', $tournamentId
        ));
    }

    /**
     * Get the racer_id for this card in the active tournament.
     * Provides backward-compatible $card->racer_id access.
     */
    public function getRacerIdAttribute(): ?string
    {
        $tournament = getActiveTournament();
        if (!$tournament) {
            return null;
        }

        return TournamentCardAssignment::where('card_id', $this->id)
            ->where('tournament_id', $tournament->id)
            ->value('racer_id');
    }

    public function races()
    {
        return $this->hasMany(Race::class);
    }

    public function couponHistory()
    {
        return $this->hasMany(CouponHistory::class);
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
