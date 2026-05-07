<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('deducted','restored','adjusted','refilled','transferred_out','transferred_in') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('deducted','restored','adjusted','refilled') NOT NULL");
    }
};
