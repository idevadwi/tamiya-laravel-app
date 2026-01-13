-- Drop the old unique constraint
ALTER TABLE `races` DROP INDEX `races_tournament_stage_track_lane_unique`;

-- Add the new unique constraint with race_no
ALTER TABLE `races` ADD UNIQUE INDEX `races_tournament_stage_race_no_lane_unique` (`tournament_id`, `stage`, `race_no`, `lane`);
