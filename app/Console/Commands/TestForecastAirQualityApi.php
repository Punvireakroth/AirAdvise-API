<?php

namespace App\Console\Commands;

use App\Services\ForecastAirQualityApiService;
use Illuminate\Console\Command;

class TestForecastAirQualityApi extends Command
{
    protected $signature = 'air-quality:test';
    protected $description = 'Test connection to the Air Quality API';

    public function handle(ForecastAirQualityApiService $service)
    {
        $this->info('Testing Air Quality API connection...');

        // Default test coordinates (San Francisco)
        $latitude = 37.7749;
        $longitude = -122.4194;

        $this->info("Fetching forecast for lat: $latitude, lon: $longitude");

        $forecasts = $service->getForecast($latitude, $longitude);

        if ($forecasts) {
            $this->info('Connection successful!');
            $this->info('Received ' . count($forecasts) . ' days of forecast data');

            // Display sample data
            $this->table(
                ['Date', 'AQI', 'PM2.5', 'PM10', 'O3', 'Category'],
                collect($forecasts)->map(function ($f) {
                    return [
                        $f['forecast_date'],
                        $f['aqi'],
                        $f['pm25'],
                        $f['pm10'],
                        $f['o3'],
                        $f['category'],
                    ];
                })
            );
        } else {
            $this->error('Failed to connect to the Air Quality API');
        }

        return 0;
    }
}