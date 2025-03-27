<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Location;
use App\Services\AirQualityApiService;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\AirQualityData;
// use App\Notifications\AirQualityAlert;

class UpdateAirQualityDataJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Execute the job.
     */
    public function handle(AirQualityApiService $airQualityService)
    {
        try {
            $airQualityData = $airQualityService->getAirQualityByCoordinates(
                $this->location->latitude,
                $this->location->longitude
            );

            if ($airQualityData) {
                AirQualityData::create([
                    'location_id' => $this->location->id,
                    'aqi' => $airQualityData['aqi'],
                    'pm25' => $airQualityData['pm25'],
                    'pm10' => $airQualityData['pm10'],
                    'o3' => $airQualityData['o3'],
                    'no2' => $airQualityData['no2'],
                    'so2' => $airQualityData['so2'],
                    'co' => $airQualityData['co'],
                    'category' => $airQualityData['category'],
                    'source' => $airQualityData['source'],
                    'timestamp' => now(),
                ]);
            }


            // Check if any users have notifications enabled and threshold exceeded
            foreach ($this->location->users as $user) {
                if (
                    $user->preference && $user->preference->air_quality_threshold
                    && $airQualityData['aqi'] >= $user->preference->air_quality_threshold
                ) {
                    // $user->notify(new AirQualityAlert($this->location, $airQualityData));
                    // TODO: Send notification to user
                    Log::info("Would notify user {$user->id} about AQI {$airQualityData['aqi']} at {$this->location->full_name}");
                }
            }
        } catch (Exception $e) {
            Log::error("Error updating air quality data for location {$this->location->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
