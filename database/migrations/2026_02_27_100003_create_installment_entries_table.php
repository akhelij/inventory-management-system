<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_schedule_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('status', 20)->default('pending');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_entries');
    }
};
