<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteCity extends Model
{
    protected $table = 'user_favorite_cities';

    protected $fillable = [
        'user_id',
        'city_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}