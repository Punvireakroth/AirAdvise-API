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
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name');
            $table->string('endpoint');
            $table->text('parameters')->nullable();
            $table->integer('response_code');
            $table->integer('execution_time'); // in milliseconds
            $table->timestamp('created_at');

            // Index on api_name and created_at
            $table->index(['api_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
