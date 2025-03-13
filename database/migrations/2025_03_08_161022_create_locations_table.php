<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city_name');
            $table->string('state_province')->nullable();
            $table->string('country');
            $table->string('country_code', 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // Center coordinates of the city
            $table->decimal('longitude', 11, 8)->nullable(); // Center coordinates of the city
            $table->string('timezone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();


            // Make city_name, country combination unique
            $table->unique(['city_name', 'country']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
