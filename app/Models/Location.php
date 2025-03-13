<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'state_province',
        'country',
        'country_code',
        'latitude',
        'longitude',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Get full location name
    public function getFullNameAttribute()
    {
        return $this->state_province
            ? "{$this->city_name}, {$this->state_province}, {$this->country}"
            : "{$this->city_name}, {$this->country}";
    }


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
