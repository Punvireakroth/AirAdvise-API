<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirQualityForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'forecast_date',
        'aqi',
        'pm25',
        'pm10',
        'category',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'aqi' => 'integer',
        'pm25' => 'decimal:2',
        'pm10' => 'decimal:2',
    ];

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
