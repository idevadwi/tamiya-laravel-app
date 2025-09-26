<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['card_code', 'racer_id', 'coupon', 'status', 'created_by', 'updated_by'];

    public function racer()
    {
        return $this->belongsTo(Racer::class);
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
