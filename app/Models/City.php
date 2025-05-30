<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'country',
        'region',
        'latitude',
        'longitude',
        'population',
        'timezone',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_favorite_cities')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function distanceFrom($latitude, $longitude)
    {
        // Haversine formula to calculate distance between two points on Earth
        $earthRadius = 6371; // in km

        $dLat = deg2rad($this->latitude - $latitude);
        $dLon = deg2rad($this->longitude - $longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($latitude)) * cos(deg2rad($this->latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}