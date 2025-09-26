<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponHistory extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tournament_id', 'card_id', 'value',
        'before_changes', 'after_changes', 'type',
        'created_by', 'updated_by'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
