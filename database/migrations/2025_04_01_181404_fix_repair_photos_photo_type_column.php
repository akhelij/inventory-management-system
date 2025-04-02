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
        Schema::table('repair_photos', function (Blueprint $table) {
            // First change the photo_type column to allow null values
            $table->string('photo_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_photos', function (Blueprint $table) {
            // Revert back to the original structure if needed
            // This depends on what your original column definition was
        });
    }
};
