<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:update-air-quality-data')
    ->everyThirtyMinutes();

Schedule::command('app:cleanup-old-data')->daily()
    ->at('01:00')
    ->withoutOverlapping();
