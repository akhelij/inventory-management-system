<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shoppingcart', function (Blueprint $table) {
            // Modify the content column to use LONGTEXT and utf8mb4 character set
            $table->longText('content')->change();
        });

        // Set the character set and collation for the table
        DB::statement('ALTER TABLE shoppingcart CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shoppingcart', function (Blueprint $table) {
            // No need to revert as we're just expanding the column capacity
        });
    }
};
