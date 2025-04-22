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
        'o3',
        'no2',
        'so2',
        'co',
        'category',
        'description',
        'recommendation',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'aqi' => 'integer',
        'pm25' => 'float',
        'pm10' => 'float',
        'o3' => 'float',
        'no2' => 'float',
        'so2' => 'float',
        'co' => 'float',
    ];

    // Relationships
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function getCategoryFromAQI(int $aqi): string
    {
        if ($aqi <= 50) {
            return 'Good';
        } elseif ($aqi <= 100) {
            return 'Moderate';
        } elseif ($aqi <= 150) {
            return 'Unhealthy for Sensitive Groups';
        } elseif ($aqi <= 200) {
            return 'Unhealthy';
        } elseif ($aqi <= 300) {
            return 'Very Unhealthy';
        } else {
            return 'Hazardous';
        }
    }

    public static function getDescriptionFromCategory(string $category): string
    {
        return match ($category) {
            'Good' => 'Air quality is satisfactory, and air pollution poses little or no risk.',
            'Moderate' => 'Air quality is acceptable. However, there may be a risk for some people, particularly those who are unusually sensitive to air pollution.',
            'Unhealthy for Sensitive Groups' => 'Members of sensitive groups may experience health effects. The general public is less likely to be affected.',
            'Unhealthy' => 'Some members of the general public may experience health effects; members of sensitive groups may experience more serious health effects.',
            'Very Unhealthy' => 'Health alert: The risk of health effects is increased for everyone.',
            'Hazardous' => 'Health warning of emergency conditions: everyone is more likely to be affected.',
            default => 'No description available for this air quality level.',
        };
    }

    public static function getRecommendationFromCategory(string $category): string
    {
        return match ($category) {
            'Good' => 'It\'s a great day to be active outside.',
            'Moderate' => 'Unusually sensitive people should consider reducing prolonged or heavy exertion.',
            'Unhealthy for Sensitive Groups' => 'People with heart or lung disease, older adults, children and teens should reduce prolonged or heavy exertion.',
            'Unhealthy' => 'Everyone should reduce prolonged or heavy exertion. Take more breaks during outdoor activities.',
            'Very Unhealthy' => 'Avoid prolonged or heavy exertion. Consider moving activities indoors or rescheduling.',
            'Hazardous' => 'Avoid all physical activity outdoors. Move activities indoors or reschedule to a time when air quality is better.',
            default => 'No specific recommendations available for this air quality level.',
        };
    }
}