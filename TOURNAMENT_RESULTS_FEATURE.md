# Tournament Results Feature

## Overview

The Tournament Results feature allows administrators to record and manage the winners and champions of a tournament. This feature dynamically generates result categories based on the tournament configuration and automatically calculates "Best Race Champions" based on Stage 2 race wins.

## Features

### 1. Dynamic Result Categories

The system automatically generates result categories based on three tournament settings:

#### a) Main Champions (`tournament.champion_number`)
- **Configuration**: Set the number of champion positions (e.g., 3)
- **Generated Categories**:
  - `champions_1` → 1st Place
  - `champions_2` → 2nd Place
  - `champions_3` → 3rd Place
- These represent the overall tournament winners

#### b) BTO (Best Track Owner) Champions
- **Configuration**: Based on `tournament.bto_number` and `tournament.track_number`
- **Example**: If `bto_number = 2` and `track_number = 3`, generates:
  - **BTO Session 1**:
    - `bto_champions_1_track_1` → BTO Champions 1 - Track 1
    - `bto_champions_1_track_2` → BTO Champions 1 - Track 2
    - `bto_champions_1_track_3` → BTO Champions 1 - Track 3
  - **BTO Session 2**:
    - `bto_champions_2_track_1` → BTO Champions 2 - Track 1
    - `bto_champions_2_track_2` → BTO Champions 2 - Track 2
    - `bto_champions_2_track_3` → BTO Champions 2 - Track 3
- BTO champions are for teams that own/excel at specific tracks in specific sessions

#### c) Best Race Champions (Auto-Calculated)
- **Configuration**: Enabled by `tournament.best_race_enabled` and `tournament.best_race_number`
- **Auto-Calculation Logic**:
  1. Counts all races where `stage = 2` in the `races` table
  2. Groups by `team_id` to count wins per team
  3. Ranks teams by the number of Stage 2 wins (descending)
  4. Automatically selects the top N teams based on `best_race_number`
- **Example**: If `best_race_enabled = true` and `best_race_number = 2`:
  - `best_race_champions_1` → 1st Place (most Stage 2 wins)
  - `best_race_champions_2` → 2nd Place (second-most Stage 2 wins)
- If disabled (`best_race_enabled = false`), this section won't display

## Database Structure

### Tournament Configuration Fields
```
tournaments table:
- champion_number: INT (default: 3)
- bto_number: INT (default: 1)
- track_number: INT (default: 1)
- best_race_enabled: BOOLEAN (default: false)
- best_race_number: INT (default: 1)
```

### Tournament Results Table
```
tournament_results table:
- id: UUID (primary key)
- tournament_id: UUID (foreign key to tournaments)
- category: VARCHAR (e.g., "champions_1", "bto_champions_1_track_2")
- rank: INT (extracted from category, e.g., 1, 2, 3)
- team_id: UUID (foreign key to teams)
- racer_id: UUID (nullable, foreign key to racers)
- created_by: UUID
- updated_by: UUID
- created_at: TIMESTAMP
- updated_at: TIMESTAMP
```

## Files Created/Modified

### New Files
1. **Migration**: `database/migrations/2025_12_11_065959_modify_tournament_results_category_to_string.php`
   - Changed `category` field from ENUM to VARCHAR to support dynamic categories

2. **Controller**: `app/Http/Controllers/TournamentResultController.php`
   - `index()`: Display tournament results management page
   - `store()`: Save tournament results
   - `destroy()`: Delete a tournament result
   - `generateCategories()`: Generate categories based on tournament settings
   - `calculateBestRaceChampions()`: Auto-calculate best race winners
   - `getChampionLabel()`: Format rank labels (1st, 2nd, 3rd)
   - `extractRankFromCategory()`: Extract rank number from category string

3. **View**: `resources/views/tournament_results/index.blade.php`
   - Form to assign teams to each category
   - Three sections: Main Champions, BTO Champions, Best Race Champions
   - Auto-populated dropdowns with all participating teams
   - Select2 for better dropdown UX
   - Auto-calculation display for Best Race Champions

### Modified Files
1. **Model**: `app/Models/TournamentResult.php`
   - Added `boot()` method for UUID generation

2. **Routes**: `routes/web.php`
   - Added tournament results routes (requires active tournament context)

3. **Config**: `config/adminlte.php`
   - Added "Tournament Results" menu item with crown icon

## Routes

```php
GET  /tournament-results          → tournament_results.index  (Display form)
POST /tournament-results          → tournament_results.store  (Save results)
DELETE /tournament-results/{id}   → tournament_results.destroy (Delete result)
```

All routes require:
- Authentication
- Active tournament context (via `tournament.context` middleware)

## Usage Flow

1. **Navigate to Tournament Results**
   - Click "Tournament Results" in the sidebar menu
   - Must have an active tournament selected

2. **View Tournament Configuration**
   - See tournament settings at the top (champions, BTO, tracks, best race)

3. **Assign Main Champions**
   - Select teams for 1st, 2nd, 3rd place (or however many configured)

4. **Assign BTO Champions**
   - For each BTO session and track combination, select the winning team

5. **Review Best Race Champions** (if enabled)
   - System shows auto-calculated rankings based on Stage 2 race wins
   - Can override if needed by changing the dropdown selection
   - Shows a table with win counts for transparency

6. **Save Results**
   - Click "Save Tournament Results" button
   - System validates at least one team is selected
   - Updates existing results or creates new ones

## Best Race Champions Logic

The best race champions are determined by counting wins in Stage 2 races:

```sql
SELECT team_id, COUNT(*) as win_count
FROM races
WHERE tournament_id = ? AND stage = 2
GROUP BY team_id
ORDER BY win_count DESC
LIMIT ?  -- based on best_race_number
```

This means:
- Stage 2 is considered the "final" or "championship" stage
- Each race entry where `stage = 2` counts as one win for that team
- Teams with the most Stage 2 races are ranked higher
- The system automatically populates the dropdown selections

## UI Features

1. **Select2 Dropdowns**
   - Searchable team selection
   - Better UX for long team lists
   - Clear button to unset selections

2. **Color-Coded Sections**
   - Main Champions: Primary (blue)
   - BTO Champions: Info (cyan)
   - Best Race Champions: Success (green)

3. **Auto-Calculation Display**
   - Best Race section shows a table with team rankings
   - Win counts displayed as badges
   - "(Auto)" indicator on auto-selected teams

4. **Validation**
   - Client-side: Ensures at least one team is selected
   - Server-side: Validates team existence and category format

## Example Scenarios

### Scenario 1: Simple Tournament
```
champion_number = 3
bto_number = 1
track_number = 2
best_race_enabled = false
```
**Generated Categories**: 5 total
- champions_1, champions_2, champions_3
- bto_champions_1_track_1, bto_champions_1_track_2

### Scenario 2: Complex Tournament
```
champion_number = 5
bto_number = 3
track_number = 4
best_race_enabled = true
best_race_number = 2
```
**Generated Categories**: 19 total
- 5 main champions (champions_1 to champions_5)
- 12 BTO champions (3 sessions × 4 tracks)
- 2 best race champions (best_race_champions_1, best_race_champions_2)

## Navigation

**Sidebar Menu**:
- Icon: Crown (fas fa-crown)
- Label: "Tournament Results"
- Position: Between "Best Times" and "Users"
- Requires: Active tournament (no admin-only restriction)

## Notes

- Tournament results can be updated multiple times
- Existing results are preserved; form shows current selections
- Deleting a result removes that specific category assignment
- Best race champions are recalculated on every page load based on current Stage 2 race data
- All participating teams (from `tournament_participants`) are available in dropdowns

## Future Enhancements (Potential)

1. Add racer-specific awards (currently `racer_id` is nullable but unused)
2. Export results as PDF/Excel
3. Public results display page
4. Historical results comparison
5. Awards/certificates generation
6. Email notifications to winning teams

