<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ===========================
        // USERS & ROLES
        // ===========================
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role_name');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->unique(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // ===========================
        // TEAMS & RACERS & CARDS
        // ===========================
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('team_name')->unique();
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
        });

        Schema::create('racers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('racer_name');
            $table->string('image')->nullable();
            $table->uuid('team_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('card_code')->unique();
            $table->uuid('racer_id')->nullable();
            $table->integer('coupon')->default(0);
            $table->enum('status', ['ACTIVE','LOST','BANNED'])->default('ACTIVE');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('racer_id')->references('id')->on('racers')->cascadeOnDelete();
        });

        // ===========================
        // TOURNAMENTS
        // ===========================
        Schema::create('tournaments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tournament_name');
            $table->string('vendor_name')->nullable();
            $table->integer('current_stage')->default(1);
            $table->integer('current_bto_session')->default(1);
            $table->integer('track_number')->default(1);
            $table->integer('bto_number')->default(1);
            $table->integer('bto_session_number')->default(0);
            $table->integer('max_racer_per_team')->default(1);
            $table->integer('champion_number')->default(3);
            $table->boolean('best_race_enabled')->default(false);
            $table->integer('best_race_number')->default(1);
            $table->enum('status', ['ACTIVE','COMPLETED','CANCELLED'])->default('ACTIVE');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
        });

        Schema::create('tournament_moderators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('user_id');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('tournament_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('team_id');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });

        // ===========================
        // TOURNAMENT TOKENS
        // ===========================
        Schema::create('tournament_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('user_id');
            $table->string('token');
            $table->enum('status', ['ACTIVE','USED','REVOKED'])->default('ACTIVE');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // ===========================
        // RACES
        // ===========================
        Schema::create('races', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->integer('stage');
            $table->string('track');
            $table->string('lane');
            $table->uuid('racer_id');
            $table->uuid('team_id');
            $table->uuid('card_id');
            $table->string('race_time')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('racer_id')->references('id')->on('racers')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
        });

        // ===========================
        // COUPON HISTORY
        // ===========================
        Schema::create('coupon_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('card_id');
            $table->integer('value');
            $table->integer('before_changes');
            $table->integer('after_changes');
            $table->enum('type', ['DEPOSIT','SEND','GET','BACK']);
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
        });

        // ===========================
        // BEST TIMES
        // ===========================
        Schema::create('best_times', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('team_id');
            $table->string('track');
            $table->string('timer');
            $table->enum('scope', ['OVERALL','SESSION']);
            $table->integer('session_number');
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });

        // ===========================
        // TOURNAMENT RESULTS
        // ===========================
        Schema::create('tournament_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->enum('category', ['CHAMPION','BTO','BTO_SESSION']);
            $table->integer('rank');
            $table->uuid('team_id');
            $table->uuid('racer_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('racer_id')->references('id')->on('racers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_results');
        Schema::dropIfExists('best_times');
        Schema::dropIfExists('coupon_history');
        Schema::dropIfExists('races');
        Schema::dropIfExists('tournament_participants');
        Schema::dropIfExists('tournament_moderators');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('racers');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('tournament_tokens');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
