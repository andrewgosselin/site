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
        Schema::create('spotify_playlists', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_id')->unique();
            $table->string('name')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('total_tracks')->default(0);
            $table->integer('processed_tracks')->default(0);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('job_id')->nullable();
            $table->json('tracks')->nullable(); // Storing tracks as JSON for now
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_playlists');
    }
};
