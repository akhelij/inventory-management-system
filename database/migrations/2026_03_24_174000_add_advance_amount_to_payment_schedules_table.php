<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->decimal('advance_amount', 10, 2)->default(0)->after('total_amount');
            $table->unsignedBigInteger('advance_payment_id')->nullable()->after('advance_amount');
            $table->foreign('advance_payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropForeign(['advance_payment_id']);
            $table->dropColumn(['advance_amount', 'advance_payment_id']);
        });
    }
};
