<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Services\AirQualityApiService;
use App\Models\Location;
use App\Models\AirQualityData;
use Illuminate\Support\Facades\Log;

class UpdateAirQualityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-air-quality-data {--all : Update all locations not just the active ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update air quality data for all locations';

    /**
     * Execute the console command.
     */
    public function handle(AirQualityApiService $airQualityService)
    {
        $query = Location::query();

        if (!$this->option('all')) {
            $query->where('active', true);
        }

        $locations = $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        $this->info("Updating air quality data for {$locations->count()} locations...");

        $bar = $this->output->createProgressBar($locations->count());
        $bar->start();

        foreach ($locations as $location) {
            try {
                $this->updateLocationAirQuality($location, $airQualityService);
                $bar->advance();
            } catch (Exception $e) {
                $this->error("Error updating air quality data for location {$location->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Air quality data updated successfully');

        return Command::SUCCESS;
    }

    protected function updateLocationAirQuality(Location $location, AirQualityApiService $airQualityService)
    {
        try {
            $airQualityData = $airQualityService->getAirQualityByCoordinates(
                $location->latitude,
                $location->longitude
            );


            if ($airQualityData) {
                AirQualityData::create([
                    'location_id' => $location->id,
                    'aqi' => $airQualityData['aqi'] ?? 0,
                    'pm25' => $airQualityData['pm25'] ?? 0,
                    'pm10' => $airQualityData['pm10'] ?? 0,
                    'o3' => $airQualityData['o3'] ?? null,
                    'no2' => $airQualityData['no2'] ?? null,
                    'so2' => $airQualityData['so2'] ?? null,
                    'co' => $airQualityData['co'] ?? null,
                    'category' => $airQualityData['category'] ?? 'Unknown',
                    'source' => $airQualityData['source'] ?? 'IQAir',
                    'timestamp' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::error("Error updating air quality data for location {$location->id}: {$e->getMessage()}");
            $this->error("Error updating air quality data for location {$location->id}: {$e->getMessage()}");
        }
    }
}