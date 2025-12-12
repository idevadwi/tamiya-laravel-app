<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old constraint using raw SQL to avoid errors
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        $tableName = 'races';
        $oldConstraintName = 'races_tournament_stage_track_lane_unique';
        
        $constraintExists = $connection->select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
        ", [$dbName, $tableName, $oldConstraintName]);
        
        if (!empty($constraintExists)) {
            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$oldConstraintName}`");
        }
        
        // Now add the new constraint
        Schema::table('races', function (Blueprint $table) use ($connection, $dbName, $tableName) {
            $newConstraintName = 'races_tournament_stage_race_no_lane_unique';
            $newConstraintExists = $connection->select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ?
            ", [$dbName, $tableName, $newConstraintName]);
            
            if (empty($newConstraintExists)) {
                $table->unique(['tournament_id', 'stage', 'race_no', 'lane'], $newConstraintName);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            // Drop new constraint
            try {
                $table->dropUnique('races_tournament_stage_race_no_lane_unique');
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            // Restore old constraint
            $table->unique(['tournament_id', 'stage', 'track', 'lane'], 'races_tournament_stage_track_lane_unique');
        });
    }
};
