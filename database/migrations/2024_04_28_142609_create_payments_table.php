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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('nature')->unique();
            $table->string('banque')->nullable();
            $table->string('payment_type')->nullable();
            $table->date('echeance')->nullable();
            $table->decimal('amount')->nullable();
            $table->boolean('reported')->default(false);
            $table->boolean('cashed_in')->default(false);
            $table->date('cashed_in_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
