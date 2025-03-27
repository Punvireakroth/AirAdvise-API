<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\AirQualityData;
use App\Models\ApiRequestLog;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-data
    {--days=30 : Days of air quality data to keep}
    {--logs=7 : Days of API logs to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old air quality data and API logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int)$this->option('days');
        $logDays = (int)$this->option('logs');

        DB::transaction(function () use ($days, $logDays) {
            // Delete old air quality data
            $cutoff = now()->subDays($days);
            $count = AirQualityData::where('timestamp', '<', $cutoff)->delete();
            $this->info("Deleted {$count} old air quality records");

            // Delete old API logs
            $logCutoff = now()->subDays($logDays);
            $logCount = ApiRequestLog::where('created_at', '<', $logCutoff)->delete();
            $this->info("Deleted {$logCount} old API request logs");
        });

        return Command::SUCCESS;
    }
}
