<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_safe_aqi',
    ];

    protected $casts = [
        'max_safe_aqi' => 'integer',
    ];

    // Scope for finding safe activities for a given AQI
    public function scopeSafeFor($query, $aqi)
    {
        return $query->where('max_safe_aqi', '>=', $aqi);
    }

    // Scope for finding unsafe activities for a given AQI
    public function scopeUnsafeFor($query, $aqi)
    {
        return $query->where('max_safe_aqi', '<', $aqi);
    }
}
