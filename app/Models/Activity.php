<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'intensity_level',
        'description',
        'active'
    ];

    /**
     * Get activities grouped by intensity level
     * 
     * @return array
     */
    public static function getGroupedActivities()
    {
        $activities = self::where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('intensity_level')
            ->map(function ($items) {
                return $items->pluck('name')->toArray();
            });

        return $activities->toArray();
    }
}
