<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Services\ForecastAirQualityApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateForecasts extends Command
{
    protected $signature = 'forecasts:update';
    protected $description = 'Update air quality forecasts for all locations';

    protected $airQualityService;

    public function __construct(ForecastAirQualityApiService $airQualityService)
    {
        parent::__construct();
        $this->airQualityService = $airQualityService;
    }

    public function handle()
    {
        $locations = Location::all();
        $this->info("Updating forecasts for {$locations->count()} locations");

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(6);

        foreach ($locations as $location) {
            $this->info("Updating forecast for {$location->name}");

            try {
                $externalForecasts = $this->airQualityService->getForecast(
                    $location->latitude,
                    $location->longitude
                );

                if ($externalForecasts) {
                    foreach ($externalForecasts as $forecastData) {
                        \App\Models\AirQualityForecast::updateOrCreate(
                            [
                                'location_id' => $location->id,
                                'forecast_date' => $forecastData['forecast_date'],
                            ],
                            $forecastData
                        );
                    }

                    $this->info("Successfully updated forecasts for {$location->name}");
                } else {
                    $this->error("Failed to get forecasts for {$location->name}");
                }
            } catch (\Exception $e) {
                $this->error("Error updating forecasts for {$location->name}: {$e->getMessage()}");
            }

            // Avoid rate limiting
            sleep(1);
        }

        $this->info('Forecast update completed');
        return 0;
    }
}