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
        Schema::table('tournaments', function (Blueprint $table) {
            // Change status enum to include 'PLANNED' and change default to 'PLANNED'
            $table->enum('status', ['PLANNED','ACTIVE','COMPLETED','CANCELLED'])->default('PLANNED')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            // Revert status enum to original values
            $table->enum('status', ['ACTIVE','COMPLETED','CANCELLED'])->default('ACTIVE')->change();
        });
    }
};
