<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'min_aqi',
        'max_aqi',
        'created_by',
    ];

    protected $casts = [
        'min_aqi' => 'integer',
        'max_aqi' => 'integer',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope for finding relevant health tips for a given AQI
    public function scopeForAqi($query, $aqi)
    {
        return $query->where('min_aqi', '<=', $aqi)
            ->where('max_aqi', '>=', $aqi);
    }
}
