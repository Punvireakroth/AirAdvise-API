<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_enabled',
        'aqi_threshold',
        'preferred_language',
        'temperature_unit',
    ];

    protected $casts = [
        'notification_enabled' => 'boolean',
        'aqi_threshold' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
