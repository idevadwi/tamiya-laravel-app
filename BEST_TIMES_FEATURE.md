# Best Times Management Feature

## Overview
The Best Times feature allows you to track the best race lap times for teams in a tournament. This feature supports both **Overall** and **Session** based tracking.

## Key Concepts

### Timer Format
- Format: `seconds:milliseconds` (e.g., `14:20`, `13:11`)
- Example: `14:20` = 14 seconds and 20 milliseconds

### Scopes

#### OVERALL
- Represents the best time throughout the **entire tournament** for a team on a specific track
- This is the all-time best record for that team/track combination
- Automatically updated when a session time beats it

#### SESSION
- Represents the best time for a **specific session** (typically 1 hour sessions)
- Up to 10 sessions can be tracked per tournament
- Each session has its own best time records
- When a session time beats the overall time, the system automatically updates the overall record

### Auto-Update Logic
When recording a SESSION best time:
1. The system checks if there's an existing OVERALL record for the same team/track
2. If the new SESSION time is better (lower) than the OVERALL time, it automatically updates the OVERALL record
3. If no OVERALL record exists, it creates one with the SESSION time

## Files Created

### Controller
- **`app/Http/Controllers/BestTimeController.php`**
  - Full CRUD operations (index, create, store, edit, update, destroy)
  - Auto-update logic for OVERALL times when SESSION beats them
  - Timer comparison logic (converts MM:SS to centiseconds for accurate comparison)
  - Filtering by track, scope, session, and team
  - Tournament context validation

### Routes
- Added to **`routes/web.php`** under the `tournament.context` middleware group:
  ```php
  Route::resource('best_times', \App\Http\Controllers\BestTimeController::class);
  ```

### Views
1. **`resources/views/best_times/index.blade.php`**
   - Lists all best times for the active tournament
   - Filters: Track, Scope, Session Number, Team
   - Visual badges for OVERALL (trophy icon) and SESSION (clock icon)
   - Timer displayed prominently with large badge
   - Pagination support
   - Info box explaining the feature

2. **`resources/views/best_times/create.blade.php`**
   - Form to record new best times
   - Dynamic session number field (shows only when SESSION scope is selected)
   - Timer format validation (MM:SS pattern)
   - JavaScript to toggle session number field visibility
   - Info box with important notes

3. **`resources/views/best_times/edit.blade.php`**
   - Form to edit existing best times
   - Same features as create form
   - Pre-populated with existing data

### Navigation
- Added to **`config/adminlte.php`** menu:
  ```php
  [
      'text' => 'Best Times',
      'url' => 'best_times',
      'icon' => 'fas fa-fw fa-stopwatch',
      'active' => ['best_times*'],
  ]
  ```

### Migration
- **`database/migrations/2025_12_10_233207_modify_best_times_session_number_nullable.php`**
  - Makes `session_number` column nullable in `best_times` table
  - Required because OVERALL records don't have a session number

## Usage

### Recording a New Best Time

1. Navigate to **Best Times** from the sidebar
2. Click **"Record New Best Time"**
3. Fill in the form:
   - **Team**: Select the team from the dropdown
   - **Track**: Select the track number (1 to tournament's track_number)
   - **Timer**: Enter time in format `MM:SS` (e.g., `14:20`)
   - **Scope**: Choose OVERALL or SESSION
   - **Session Number**: If SESSION is selected, choose the session number (1-10)
4. Click **"Record Best Time"**

### Filtering Best Times

Use the filter card to narrow down results:
- **Track**: Filter by specific track
- **Scope**: Show only OVERALL or SESSION records
- **Session Number**: Filter by specific session
- **Team**: Filter by specific team

### Automatic OVERALL Updates

Example scenario:
1. Team A records a SESSION 1 time of `15:30` on Track 1
   - Creates SESSION record with `15:30`
   - Creates OVERALL record with `15:30` (no overall existed)

2. Later, Team A records a SESSION 2 time of `14:20` on Track 1
   - Creates SESSION 2 record with `14:20`
   - **Automatically updates** OVERALL record to `14:20` (better time)

3. Team A records a SESSION 3 time of `14:50` on Track 1
   - Creates SESSION 3 record with `14:50`
   - OVERALL record stays at `14:20` (not better)

## Validation Rules

- **team_id**: Required, must exist in teams table
- **track**: Required, string
- **timer**: Required, must match format `/^\d{1,2}:\d{2}$/` (MM:SS)
- **scope**: Required, must be either 'OVERALL' or 'SESSION'
- **session_number**: 
  - Required when scope is SESSION
  - Nullable when scope is OVERALL
  - Must be integer between 1 and 10

## Database Schema

The `best_times` table structure:
```sql
id (UUID, primary key)
tournament_id (UUID, foreign key)
team_id (UUID, foreign key)
track (string)
timer (string) - format: MM:SS
scope (enum: 'OVERALL', 'SESSION')
session_number (integer, nullable) - 1 to 10
created_at (timestamp)
created_by (UUID)
updated_at (timestamp)
updated_by (UUID)
```

## Features

✅ Full CRUD operations
✅ Automatic OVERALL time updates
✅ Multiple track support
✅ Up to 10 session tracking
✅ Team-based filtering
✅ Timer validation (MM:SS format)
✅ Tournament context validation
✅ Visual indicators (badges, icons)
✅ Responsive design
✅ Pagination
✅ Search and filter functionality
✅ Dynamic form fields (session number)
✅ User-friendly interface with AdminLTE

## Technical Details

### Timer Comparison Algorithm

```php
private function timerToSeconds($timer)
{
    $parts = explode(':', $timer);
    $seconds = (int)$parts[0];
    $milliseconds = (int)$parts[1];
    
    // Convert to centiseconds for accurate comparison
    return ($seconds * 100) + $milliseconds;
}
```

This converts `14:20` to `1420` centiseconds for accurate numerical comparison.

### Session Number Visibility

JavaScript in create/edit forms:
- Monitors the scope dropdown
- Shows session_number field only when SESSION is selected
- Makes session_number required/not required dynamically
- Preserves old values on validation errors

## Next Steps

To use this feature:

1. **Run the migration** (if not already run):
   ```bash
   php artisan migrate
   ```

2. **Ensure you have an active tournament selected**

3. **Navigate to Best Times** from the sidebar menu

4. **Start recording best times** for your teams!

## Support for Multiple Lanes

The current implementation tracks best times per team and track. If you need to track which lane the best time was achieved on, you can extend the feature by:

1. Adding a `lane` column to the `best_times` table
2. Updating the controller to accept and store lane information
3. Updating the views to display and filter by lane

This would be useful if different lanes have significantly different performance characteristics.

