<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('air_quality_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->date('forecast_date');
            $table->integer('aqi');
            $table->float('pm25')->nullable();
            $table->float('pm10')->nullable();
            $table->float('o3')->nullable();
            $table->float('no2')->nullable();
            $table->float('so2')->nullable();
            $table->float('co')->nullable();
            $table->string('category');
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'forecast_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('air_quality_forecasts');
    }
};