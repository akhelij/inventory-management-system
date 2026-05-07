<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_entries', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
        });

        DB::statement("UPDATE installment_entries SET paid_amount = amount WHERE status = 'paid'");
    }

    public function down(): void
    {
        Schema::table('installment_entries', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
