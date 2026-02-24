<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the tournament_card_assignments table
        Schema::create('tournament_card_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('card_id');
            $table->uuid('racer_id');
            $table->enum('status', ['ACTIVE', 'LOST', 'BANNED'])->default('ACTIVE');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'card_id']);

            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->foreign('racer_id')->references('id')->on('racers')->cascadeOnDelete();
        });

        // 2. Migrate existing card->racer_id assignments to tournament_card_assignments
        //    For each card with a racer_id, find the most recent tournament that racer participated in
        $cards = DB::table('cards')->whereNotNull('racer_id')->get();

        foreach ($cards as $card) {
            $participation = DB::table('tournament_racer_participants')
                ->where('racer_id', $card->racer_id)
                ->orderByDesc('created_at')
                ->first();

            if ($participation) {
                // Avoid duplicates (card already assigned in that tournament)
                $exists = DB::table('tournament_card_assignments')
                    ->where('tournament_id', $participation->tournament_id)
                    ->where('card_id', $card->id)
                    ->exists();

                if (! $exists) {
                    DB::table('tournament_card_assignments')->insert([
                        'id'            => (string) Str::uuid(),
                        'tournament_id' => $participation->tournament_id,
                        'card_id'       => $card->id,
                        'racer_id'      => $card->racer_id,
                        'status'        => $card->status,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

        // 3. Drop racer_id from cards table
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['racer_id']);
            $table->dropColumn('racer_id');
        });
    }

    public function down(): void
    {
        // Restore racer_id column on cards
        Schema::table('cards', function (Blueprint $table) {
            $table->uuid('racer_id')->nullable()->after('card_no');
            $table->foreign('racer_id')->references('id')->on('racers')->cascadeOnDelete();
        });

        Schema::dropIfExists('tournament_card_assignments');
    }
};
