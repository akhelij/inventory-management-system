<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_guarantees', function (Blueprint $table) {
            $table->dropColumn('cheque_account_holder');
        });
    }

    public function down(): void
    {
        Schema::table('installment_guarantees', function (Blueprint $table) {
            $table->string('cheque_account_holder')->nullable()->after('cheque_echeance');
        });
    }
};
