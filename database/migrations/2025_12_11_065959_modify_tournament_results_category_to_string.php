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
        Schema::table('tournament_results', function (Blueprint $table) {
            // Change category from ENUM to VARCHAR to support dynamic categories
            $table->string('category')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_results', function (Blueprint $table) {
            // Revert back to ENUM (optional, may not work perfectly)
            $table->enum('category', ['CHAMPION','BTO','BTO_SESSION'])->change();
        });
    }
};
