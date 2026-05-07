<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_guarantees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_entry_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('type', ['person', 'cheque']);

            $table->foreignId('person_customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('cheque_nature')->nullable();
            $table->decimal('cheque_amount', 10, 2)->nullable();
            $table->string('cheque_bank')->nullable();
            $table->date('cheque_echeance')->nullable();
            $table->string('cheque_account_holder')->nullable();
            $table->string('cheque_photo')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_guarantees');
    }
};
