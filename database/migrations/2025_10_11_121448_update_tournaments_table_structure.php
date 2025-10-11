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
            // Change nullable from current_stage
            $table->integer('current_stage')->nullable(true)->change();

            // Change nullable from current_bto_session
            $table->integer('current_bto_session')->nullable(true)->change();

            // Change best_race_number to nullable
            $table->integer('best_race_number')->nullable(true)->change();

            // Update status enum to remove 'PLANNED' and change default to 'PLANNED'
            $table->enum('status', ['PLANNED','ACTIVE','COMPLETED','CANCELLED'])->default('PLANNED')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            // Revert current_stage to not nullable
            $table->integer('current_stage')->nullable(false)->change();

            // Revert current_bto_session to not nullable
            $table->integer('current_bto_session')->nullable(false)->change();

            // Revert best_race_number to not nullable without default
            $table->integer('best_race_number')->nullable(false)->change();

            // Revert status enum and change default to 'ACTIVE'
            $table->enum('status', ['ACTIVE','COMPLETED','CANCELLED'])->default('ACTIVE')->change();
        });
    }
};
