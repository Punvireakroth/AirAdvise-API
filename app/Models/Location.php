<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_locations')
            ->withPivot('is_favorite')
            ->withTimestamps();
    }

    public function airQualityData()
    {
        return $this->hasMany(AirQualityData::class);
    }

    public function latestAirQuality()
    {
        return $this->hasOne(AirQualityData::class)
            ->latest('timestamp');
    }

    public function forecasts()
    {
        return $this->hasMany(AirQualityForecast::class);
    }
}
