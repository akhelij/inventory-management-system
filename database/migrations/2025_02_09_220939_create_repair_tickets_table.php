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
        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->string('serial_number')->nullable();
            $table->text('problem_description');
            $table->enum('status', [
                'RECEIVED',
                'IN_PROGRESS',
                'REPAIRED',
                'UNREPAIRABLE',
                'DELIVERED'
            ])->default('RECEIVED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_tickets');
    }
};
