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
            // Remove nullable from current_stage
            $table->integer('current_stage')->nullable()->change();
            
            // Remove nullable from current_bto_session
            $table->integer('current_bto_session')->nullable()->change();
            
            // Change best_race_number from nullable to default(1)
            $table->integer('best_race_number')->default(1)->nullable()->change();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            // Revert current_stage to nullable
            $table->integer('current_stage')->nullable(false)->change();
            
            // Revert current_bto_session to nullable
            $table->integer('current_bto_session')->nullable(false)->change();
            
            // Revert best_race_number to nullable without default
            $table->integer('best_race_number')->nullable(false)->change();
            
            // Revert status enum to include 'PLANNED' and change default to 'PLANNED'
            $table->enum('status', ['PLANNED','ACTIVE','COMPLETED','CANCELLED'])->default('PLANNED')->change();
        });
    }
};
