<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('air_quality_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->integer('aqi');
            $table->decimal('pm25', 6, 2)->nullable();
            $table->decimal('pm10', 6, 2)->nullable();
            $table->enum('category', [
                'Good',
                'Moderate',
                'Unhealthy for Sensitive Groups',
                'Unhealthy',
                'Very Unhealthy',
                'Hazardous'
            ]);
            $table->timestamps();

            // Unique constraint on location_id and forecast_date
            $table->unique(['location_id', 'forecast_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_quality_forecasts');
    }
};
