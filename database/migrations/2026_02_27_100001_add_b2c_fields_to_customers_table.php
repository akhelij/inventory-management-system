<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('category', 10)->default('b2b')->after('type');
            $table->index('category');
            $table->string('cin', 20)->nullable()->unique()->after('category');
            $table->date('date_of_birth')->nullable()->after('cin');
            $table->string('cin_photo')->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'cin', 'date_of_birth', 'cin_photo']);
        });
    }
};
