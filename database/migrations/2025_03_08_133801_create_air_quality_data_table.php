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
        Schema::create('air_quality_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->integer('aqi');
            $table->decimal('pm25', 6, 2);
            $table->decimal('pm10', 6, 2);
            $table->decimal('o3', 6, 2)->nullable();
            $table->decimal('no2', 6, 2)->nullable();
            $table->decimal('so2', 6, 2)->nullable();
            $table->decimal('co', 6, 2)->nullable();
            $table->enum('category', [
                'Good',
                'Moderate',
                'Unhealthy for Sensitive Groups',
                'Unhealthy',
                'Very Unhealthy',
                'Hazardous'
            ]);
            $table->string('source');
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->index(['timestamp', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_quality_data');
    }
};
