<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirQualityData extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'aqi',
        'pm25',
        'pm10',
        'o3',
        'no2',
        'so2',
        'co',
        'category',
        'source',
        'timestamp',
    ];

    protected $casts = [
        'aqi' => 'integer',
        'pm25' => 'decimal:2',
        'pm10' => 'decimal:2',
        'o3' => 'decimal:2',
        'no2' => 'decimal:2',
        'so2' => 'decimal:2',
        'co' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
