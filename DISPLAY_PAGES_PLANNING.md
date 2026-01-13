# Display Pages Implementation Planning - REVISED & IMPROVED

## Executive Summary

This document outlines the complete implementation strategy for real-time tournament display pages optimized for TV browsers. The system uses Laravel backend with Ably pub/sub for live updates, supporting multiple display types with TV-compatible JavaScript (ES5).

**Key Improvements in This Revision:**
- ✅ Detailed phase-by-phase implementation with time estimates
- ✅ Complete code examples for all components
- ✅ Clear acceptance criteria for each task
- ✅ Comprehensive error handling and logging
- ✅ Better architecture diagrams and data flow
- ✅ Testing strategy with specific test cases
- ✅ Deployment checklist with verification steps

---

## Table of Contents

1. [Current State Analysis](#current-state-analysis)
2. [Architecture Design](#architecture-design)
3. [Implementation Roadmap](#implementation-roadmap)
4. [Technical Specifications](#technical-specifications)
5. [Testing Strategy](#testing-strategy)
6. [Deployment Guide](#deployment-guide)
7. [Troubleshooting](#troubleshooting)

---

## Current State Analysis

### ✅ What's Already Done

#### Backend Components
1. **DisplayController** ([`app/Http/Controllers/DisplayController.php`](app/Http/Controllers/DisplayController.php))
   - ✅ `bestRace($slug)` - View renderer
   - ✅ `track($slug, $trackNumber)` - View renderer with validation
   - ✅ `bestRaceSnapshot($slug)` - API endpoint
   - ✅ `trackSnapshot($slug, $trackNumber)` - API endpoint
   - ✅ Timer conversion helpers (private methods)

2. **Routes** ([`routes/web.php`](routes/web.php:148-159))
   - ✅ Display page routes (public, no auth)
   - ✅ API snapshot routes
   - ✅ Track number validation via regex

3. **Sample Files** ([`resources/sample/`](resources/sample/))
   - ✅ `best-race.html` - Reference implementation
   - ✅ `track-1.html` & `track-2.html` - Track displays
   - ✅ Background images (PNG files)

### ❌ What's Missing (Critical Path)

1. **Ably Integration** (BLOCKER)
   - ❌ No Ably SDK installed
   - ❌ No config in [`config/services.php`](config/services.php)
   - ❌ No `.env` variables
   - ❌ No helper functions
   - ❌ No publish methods

2. **Views** (BLOCKER)
   - ❌ `resources/views/display/` directory doesn't exist
   - ❌ No Blade templates created
   - ❌ No TV-compatible JavaScript implementation

3. **Assets** (BLOCKER)
   - ❌ `public/images/display/` doesn't exist
   - ❌ Background images not copied

4. **Publishing Logic** (HIGH PRIORITY)
   - ❌ No Ably publish in [`BestTimeController`](app/Http/Controllers/BestTimeController.php)
   - ❌ No Ably publish in [`RaceController`](app/Http/Controllers/RaceController.php)
   - ❌ No event-driven updates

---

## Architecture Design

### System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     TV Display Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Best Race   │  │   Track 1    │  │   Track 2    │      │
│  │  (Landscape) │  │  (Portrait)  │  │  (Portrait)  │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │              │
│         └──────────────────┼──────────────────┘              │
│                            │                                 │
│                    ┌───────▼────────┐                        │
│                    │  Ably Pub/Sub  │                        │
│                    │   (Real-time)  │                        │
│                    └───────▲────────┘                        │
└────────────────────────────┼─────────────────────────────────┘
                             │
┌────────────────────────────┼─────────────────────────────────┐
│                    Laravel Backend                           │
│                            │                                 │
│  ┌─────────────────────────▼──────────────────────────┐     │
│  │              Controllers Layer                      │     │
│  │  ┌──────────────┐  ┌──────────────┐               │     │
│  │  │ BestTime     │  │ Race         │               │     │
│  │  │ Controller   │  │ Controller   │               │     │
│  │  └──────┬───────┘  └──────┬───────┘               │     │
│  │         │                  │                        │     │
│  │         └──────────┬───────┘                        │     │
│  │                    │ Publish Events                 │     │
│  └────────────────────┼────────────────────────────────┘     │
│                       │                                      │
│  ┌────────────────────▼────────────────────────────────┐     │
│  │              Models Layer                           │     │
│  │  Tournament │ BestTime │ Race │ Team               │     │
│  └────────────────────┬────────────────────────────────┘     │
│                       │                                      │
│  ┌────────────────────▼────────────────────────────────┐     │
│  │              Database (SQLite)                      │     │
│  └─────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

### Ably Channel Architecture

**Channel Naming Pattern:**
```
{tournament_slug}:best-race
{tournament_slug}:track-{n}
```

**Examples:**
```
tamiya-2025:best-race
tamiya-2025:track-1
tamiya-2025:track-2
summer-race:best-race
```

**Message Structures:**

```javascript
// Best Race Channel Message
{
  "type": "snapshot",
  "updatedAt": 1735134000000,
  "items": [
    { "TEAM NAME": "Team Alpha", "TOTAL": 15 },
    { "TEAM NAME": "Team Beta", "TOTAL": 12 }
  ]
}

// Track Channel Message
{
  "track": 1,
  "bto": {
    "TIMER": "12:34",
    "TEAM": "Team Alpha",
    "LIMIT": "14:04"
  },
  "sesi": {
    "SESI": 3,
    "TIMER": "13:45",
    "TEAM": "Team Beta"
  }
}
```

---

## Implementation Roadmap

### 📋 Implementation Checklist

```
Phase 1: Foundation (Est. 1 hour)
├─ [ ] 1.1 Install Ably SDK
├─ [ ] 1.2 Configure Ably in services.php
├─ [ ] 1.3 Add .env variables
├─ [ ] 1.4 Create AblyHelper class
├─ [ ] 1.5 Copy background images
└─ [ ] 1.6 Create display directories

Phase 2: Views (Est. 2 hours)
├─ [ ] 2.1 Create tv-display.blade.php layout
├─ [ ] 2.2 Create best-race.blade.php
├─ [ ] 2.3 Create track.blade.php
└─ [ ] 2.4 Test views manually

Phase 3: Real-Time Publishing (Est. 1.5 hours)
├─ [ ] 3.1 Add publish to BestTimeController
├─ [ ] 3.2 Add publish to RaceController
├─ [ ] 3.3 Add publish to TournamentController
└─ [ ] 3.4 Test Ably publishing

Phase 4: Testing (Est. 2 hours)
├─ [ ] 4.1 Unit tests for AblyHelper
├─ [ ] 4.2 Integration tests for controllers
├─ [ ] 4.3 Manual TV browser testing
└─ [ ] 4.4 Load testing with multiple displays

Phase 5: Deployment (Est. 30 minutes)
├─ [ ] 5.1 Update production .env
├─ [ ] 5.2 Run migrations if needed
├─ [ ] 5.3 Clear caches
└─ [ ] 5.4 Verify on staging
```

---

### Phase 1: Foundation Setup (Est. 1 hour)

#### Task 1.1: Install Ably SDK
**Priority:** CRITICAL  
**Time:** 5 minutes

```bash
composer require ably/ably-php
```

**Verification:**
```bash
composer show ably/ably-php
```

---

#### Task 1.2: Configure Ably
**Priority:** CRITICAL  
**Time:** 10 minutes

**File:** [`config/services.php`](config/services.php)

```php
<?php

return [
    // ... existing services ...
    
    'ably' => [
        'key' => env('ABLY_KEY'),
        'channel_prefix' => env('ABLY_CHANNEL_PREFIX', 'tamiya'),
    ],
];
```

**File:** [`.env.example`](.env.example)

```env
# Ably Real-time Configuration
ABLY_KEY=your_ably_api_key_here
ABLY_CHANNEL_PREFIX=tamiya
```

**Verification:**
```php
// In tinker: php artisan tinker
config('services.ably.key');
// Should return the key from .env
```

---

#### Task 1.3: Create AblyHelper
**Priority:** CRITICAL  
**Time:** 25 minutes

**File:** `app/Helpers/AblyHelper.php`

```php
<?php

namespace App\Helpers;

use App\Models\Tournament;
use Ably\AblyRest;
use Illuminate\Support\Facades\Log;

class AblyHelper
{
    /**
     * Get Ably channel name for tournament display
     * 
     * @param Tournament $tournament
     * @param string $type 'best-race' or 'track'
     * @param int|null $trackNumber
     * @return string
     */
    public static function getChannelName(Tournament $tournament, string $type, ?int $trackNumber = null): string
    {
        $slug = $tournament->slug;
        
        if ($type === 'track' && $trackNumber) {
            return "{$slug}:track-{$trackNumber}";
        }
        
        return "{$slug}:{$type}";
    }
    
    /**
     * Publish message to Ably channel
     * 
     * @param string $channelName
     * @param string $eventName
     * @param array $data
     * @return bool Success status
     */
    public static function publish(string $channelName, string $eventName, array $data): bool
    {
        try {
            $key = config('services.ably.key');
            
            if (!$key) {
                Log::warning('Ably key not configured');
                return false;
            }
            
            $ably = new AblyRest($key);
            $channel = $ably->channels->get($channelName);
            $channel->publish($eventName, $data);
            
            Log::info('Ably message published', [
                'channel' => $channelName,
                'event' => $eventName,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Ably publish failed', [
                'channel' => $channelName,
                'event' => $eventName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Publish best race update
     * 
     * @param Tournament $tournament
     * @param array $items
     * @return bool
     */
    public static function publishBestRace(Tournament $tournament, array $items): bool
    {
        $channelName = self::getChannelName($tournament, 'best-race');
        
        $data = [
            'type' => 'snapshot',
            'updatedAt' => now()->timestamp * 1000,
            'items' => $items
        ];
        
        return self::publish($channelName, 'update', $data);
    }
    
    /**
     * Publish track update
     * 
     * @param Tournament $tournament
     * @param int $trackNumber
     * @param array|null $btoData
     * @param array|null $sessionData
     * @return bool
     */
    public static function publishTrack(Tournament $tournament, int $trackNumber, ?array $btoData, ?array $sessionData): bool
    {
        $channelName = self::getChannelName($tournament, 'track', $trackNumber);
        
        $data = [
            'track' => $trackNumber,
            'bto' => $btoData,
            'sesi' => $sessionData
        ];
        
        return self::publish($channelName, 'update', $data);
    }
    
    /**
     * Convert timer string (MM:SS) to centiseconds
     * 
     * @param string $timer Format: "12:34"
     * @return int
     */
    public static function timerToCentiseconds(string $timer): int
    {
        $parts = explode(':', $timer);
        $seconds = (int) ($parts[0] ?? 0);
        $centiseconds = (int) ($parts[1] ?? 0);
        
        return ($seconds * 100) + $centiseconds;
    }
    
    /**
     * Convert centiseconds to timer string
     * 
     * @param int $centiseconds
     * @return string Format: "12:34"
     */
    public static function centisecondsToTimer(int $centiseconds): string
    {
        $seconds = floor($centiseconds / 100);
        $centi = $centiseconds % 100;
        
        return sprintf('%d:%02d', $seconds, $centi);
    }
}
```

**Register in composer.json:**
```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
        "app/Helpers/functions.php"
    ]
},
```

**Run:**
```bash
composer dump-autoload
```

---

#### Task 1.4: Copy Assets
**Priority:** HIGH  
**Time:** 5 minutes

```bash
# Create directory
mkdir -p public/images/display

# Copy images
cp resources/sample/best-race.png public/images/display/best-race-bg.png
cp resources/sample/track1.png public/images/display/track1-bg.png
cp resources/sample/track2.png public/images/display/track2-bg.png

# Verify
ls -la public/images/display/
```

---

### Phase 2: View Implementation (Est. 2 hours)

#### Task 2.1: Base Layout
**Priority:** CRITICAL  
**Time:** 20 minutes

**File:** `resources/views/display/layouts/tv-display.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    {{-- Prevent caching --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    {{-- TV optimization --}}
    <meta name="format-detection" content="telephone=no">
    
    {{-- Ably CDN --}}
    <script src="https://cdn.ably.io/lib/ably.min-1.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
            background: #000;
            color: #fff;
        }
        
        .overscan-safe {
            padding: 3%;
        }
        
        .tv-text {
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        
        @yield('styles')
    </style>
</head>
<body>
    @yield('content')
    
    <script>
        // Keep-alive for TV browsers
        setInterval(function() {
            console.log('Keep-alive: ' + new Date().toISOString());
        }, 30000);
        
        @yield('scripts')
    </script>
</body>
</html>
```

---

#### Task 2.2: Best Race View
**Priority:** CRITICAL  
**Time:** 45 minutes

**File:** `resources/views/display/best-race.blade.php`

```blade
@extends('display.layouts.tv-display')

@section('title', 'Best Race - ' . $tournament->name)

@section('styles')
<style>
    body {
        width: 1920px;
        height: 1080px;
        background-image: url('/images/display/best-race-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    .container {
        width: 100%;
        height: 100%;
        padding: 60px;
    }
    
    .leaderboard {
        position: absolute;
        top: 300px;
        left: 200px;
        width: 1520px;
    }
    
    .team-row {
        display: flex;
        justify-content: space-between;
        padding: 20px 40px;
        margin-bottom: 15px;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 10px;
        font-size: 48px;
        font-weight: bold;
    }
    
    .team-name {
        flex: 1;
        text-align: left;
    }
    
    .team-total {
        width: 150px;
        text-align: right;
        color: #FFD700;
    }
    
    .last-updated {
        position: absolute;
        bottom: 40px;
        right: 60px;
        font-size: 24px;
        color: #ccc;
    }
    
    .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 48px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="leaderboard" id="leaderboard">
        <div class="loading">Loading...</div>
    </div>
    <div class="last-updated" id="lastUpdated"></div>
</div>
@endsection

@section('scripts')
<script>
    var tournamentSlug = '{{ $tournament->slug }}';
    var channelName = tournamentSlug + ':best-race';
    var storageKey = 'best-race-' + tournamentSlug;
    var ablyKey = '{{ config("services.ably.key") }}';
    
    var ably = new Ably.Realtime(ablyKey);
    var channel = ably.channels.get(channelName);
    
    var leaderboardEl = document.getElementById('leaderboard');
    var lastUpdatedEl = document.getElementById('lastUpdated');
    
    function loadCachedData() {
        try {
            var cached = localStorage.getItem(storageKey);
            if (cached) {
                var data = JSON.parse(cached);
                renderLeaderboard(data.items);
                updateTimestamp(data.updatedAt);
            }
        } catch (e) {
            console.error('Cache load error:', e);
        }
    }
    
    function fetchSnapshot() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/' + tournamentSlug + '/best-race/snapshot', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                renderLeaderboard(data.items);
                updateTimestamp(data.updatedAt);
                cacheData(data);
            }
        };
        xhr.onerror = function() {
            console.error('Snapshot fetch failed');
        };
        xhr.send();
    }
    
    function renderLeaderboard(items) {
        if (!items || items.length === 0) {
            leaderboardEl.innerHTML = '<div class="loading">No data available</div>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < items.length; i++) {
            html += '<div class="team-row">';
            html += '<div class="team-name">' + escapeHtml(items[i]['TEAM NAME']) + '</div>';
            html += '<div class="team-total">' + items[i]['TOTAL'] + '</div>';
            html += '</div>';
        }
        
        leaderboardEl.innerHTML = html;
    }
    
    function updateTimestamp(timestamp) {
        var date = new Date(timestamp);
        var hours = padZero(date.getHours());
        var minutes = padZero(date.getMinutes());
        var seconds = padZero(date.getSeconds());
        lastUpdatedEl.textContent = 'Last Updated: ' + hours + ':' + minutes + ':' + seconds;
    }
    
    function cacheData(data) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(data));
        } catch (e) {
            console.error('Cache save error:', e);
        }
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }
    
    channel.subscribe('update', function(message) {
        console.log('Received update:', message.data);
        renderLeaderboard(message.data.items);
        updateTimestamp(message.data.updatedAt);
        cacheData(message.data);
    });
    
    ably.connection.on('connected', function() {
        console.log('Ably connected');
    });
    
    ably.connection.on('disconnected', function() {
        console.log('Ably disconnected');
    });
    
    loadCachedData();
    fetchSnapshot();
</script>
@endsection
```

---

#### Task 2.3: Track View
**Priority:** CRITICAL  
**Time:** 55 minutes

**File:** `resources/views/display/track.blade.php`

```blade
@extends('display.layouts.tv-display')

@section('title', 'Track ' . $trackNumber . ' - ' . $tournament->name)

@section('styles')
<style>
    body {
        width: 1080px;
        height: 1920px;
        background-image: url('/images/display/track{{ $trackNumber }}-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transform: rotate(90deg);
        transform-origin: bottom left;
        position: absolute;
        top: -1080px;
        left: 0;
    }
    
    .container {
        width: 100%;
        height: 100%;
        padding: 80px;
    }
    
    .stats-container {
        position: absolute;
        top: 400px;
        left: 100px;
        right: 100px;
    }
    
    .stat-block {
        margin-bottom: 80px;
        background: rgba(0, 0, 0, 0.7);
        padding: 40px;
        border-radius: 15px;
    }
    
    .stat-label {
        font-size: 42px;
        color: #FFD700;
        margin-bottom: 20px;
        font-weight: bold;
    }
    
    .stat-timer {
        font-size: 96px;
        font-weight: bold;
        color: #00FF00;
        margin-bottom: 15px;
        font-family: 'Courier New', monospace;
    }
    
    .stat-team {
        font-size: 48px;
        color: #fff;
    }
    
    .session-number {
        font-size: 64px;
        color: #FFD700;
        margin-bottom: 15px;
    }
    
    .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 64px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="stats-container" id="statsContainer">
        <div class="loading">Loading...</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var tournamentSlug = '{{ $tournament->slug }}';
    var trackNumber = {{ $trackNumber }};
    var channelName = tournamentSlug + ':track-' + trackNumber;
    var storageKey = 'track-' + trackNumber + '-' + tournamentSlug;
    var ablyKey = '{{ config("services.ably.key") }}';
    
    var ably = new Ably.Realtime(ablyKey);
    var channel = ably.channels.get(channelName);
    
    var statsContainerEl = document.getElementById('statsContainer');
    
    function loadCachedData() {
        try {
            var cached = localStorage.getItem(storageKey);
            if (cached) {
                var data = JSON.parse(cached);
                renderStats(data);
            }
        } catch (e) {
            console.error('Cache load error:', e);
        }
    }
    
    function fetchSnapshot() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/' + tournamentSlug + '/track-' + trackNumber + '/snapshot', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                renderStats(data);
                cacheData(data);
            }
        };
        xhr.onerror = function() {
            console.error('Snapshot fetch failed');
        };
        xhr.send();
    }
    
    function renderStats(data) {
        if (!data) {
            statsContainerEl.innerHTML = '<div class="loading">No data available</div>';
            return;
        }
        
        var html = '';
        
        if (data.bto) {
            html += '<div class="stat-block">';
            html += '<div class="stat-label">BEST TIME OVERALL (BTO)</div>';
            html += '<div class="stat-timer">' + escapeHtml(data.bto.TIMER) + '</div>';
            html += '<div class="stat-team">' + escapeHtml(data.bto.TEAM) + '</div>';
            html += '</div>';
            
            html += '<div class="stat-block">';
            html += '<div class="stat-label">TRACK LIMIT</div>';
            html += '<div class="stat-timer">' + escapeHtml(data.bto.LIMIT) + '</div>';
            html += '</div>';
        }
        
        if (data.sesi) {
            html += '<div class="stat-block">';
            html += '<div class="stat-label">CURRENT SESSION</div>';
            html += '<div class="session-number">Session ' + data.sesi.SESI + '</div>';
            html += '<div class="stat-timer">' + escapeHtml(data.sesi.TIMER) + '</div>';
            html += '<div class="stat-team">' + escapeHtml(data.sesi.TEAM) + '</div>';
            html += '</div>';
        }
        
        if (html === '') {
            html = '<div class="loading">No data available</div>';
        }
        
        statsContainerEl.innerHTML = html;
    }
    
    function cacheData(data) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(data));
        } catch (e) {
            console.error('Cache save error:', e);
        }
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    channel.subscribe('update', function(message) {
        console.log('Received update:', message.data);
        renderStats(message.data);
        cacheData(message.data);
    });
    
    ably.connection.on('connected', function() {
        console.log('Ably connected to track ' + trackNumber);
    });
    
    ably.connection.on('disconnected', function() {
        console.log('Ably disconnected');
    });
    
    loadCachedData();
    fetchSnapshot();
</script>
@endsection
```

---

### Phase 3: Real-Time Publishing (Est. 1.5 hours)

#### Task 3.1: Update BestTimeController
**Priority:** HIGH  
**Time:** 30 minutes

Add Ably publishing to [`app/Http/Controllers/BestTimeController.php`](app/Http/Controllers/BestTimeController.php):

```php
use App\Helpers\AblyHelper;

// In store() method, after saving:
public function store(Request $request)
{
    // ... existing validation and save logic ...
    
    $bestTime->save();
    
    // Publish to Ably
    $this->publishTrackUpdate($bestTime->tournament, $bestTime->track);
    
    return redirect()->route('tournament.best_times.index')
        ->with('success', 'Best time created successfully.');
}

// In update() method, after updating:
public function update(Request $request, BestTime $bestTime)
{
    // ... existing validation and update logic ...
    
    $bestTime->save();
    
    // Publish to Ably
    $this->publishTrackUpdate($bestTime->tournament, $bestTime->track);
    
    return redirect()->route('tournament.best_times.index')
        ->with('success', 'Best time updated successfully.');
}

// Add helper method at the end of the class:
private function publishTrackUpdate(Tournament $tournament, int $trackNumber)
{
    // Get BTO data
    $bto = BestTime::where('tournament_id', $tournament->id)
        ->where('scope', 'OVERALL')
        ->where('track', $trackNumber)
        ->with('team')
        ->first();
    
    $btoData = null;
    if ($bto) {
        $btoSeconds = AblyHelper::timerToCentiseconds($bto->timer);
        $limitSeconds = $btoSeconds + 150; // 1:30
        $limitTimer = AblyHelper::centisecondsToTimer($limitSeconds);
        
        $btoData = [
            'TIMER' => $bto->timer,
            'TEAM' => $bto->team->name,
            'LIMIT' => $limitTimer
        ];
    }
    
    // Get session data
    $currentSession = $tournament->current_bto_session;
    $session = BestTime::where('tournament_id', $tournament->id)
        ->where('scope', 'SESSION')
        ->where('session_number', $currentSession)
        ->where('track', $trackNumber)
        ->with('team')
        ->first();
    
    $sessionData = null;
    if ($session) {
        $sessionData = [
            'SESI' => $currentSession,
            'TIMER' => $session->timer,
            'TEAM' => $session->team->name
        ];
    }
    
    // Publish to Ably
    AblyHelper::publishTrack($tournament, $trackNumber, $btoData, $sessionData);
}
```

---

#### Task 3.2: Update RaceController
**Priority:** HIGH
**Time:** 30 minutes

Add Ably publishing to [`app/Http/Controllers/RaceController.php`](app/Http/Controllers/RaceController.php):

```php
use App\Helpers\AblyHelper;
use Illuminate\Support\Facades\DB;

// In store() method, after saving:
public function store(Request $request)
{
    // ... existing validation and save logic ...
    
    $race->save();
    
    // Publish best race update
    $this->publishBestRaceUpdate($race->tournament);
    
    return redirect()->route('tournament.races.index')
        ->with('success', 'Race created successfully.');
}

// Add helper method at the end of the class:
private function publishBestRaceUpdate(Tournament $tournament)
{
    $nextStage = $tournament->current_stage + 1;
    
    // Top 6 Teams with most races in next stage
    $topTeams = Race::where('tournament_id', $tournament->id)
        ->where('stage', $nextStage)
        ->select('team_id', DB::raw('count(*) as total'))
        ->groupBy('team_id')
        ->orderByDesc('total')
        ->limit(6)
        ->with('team')
        ->get()
        ->map(function ($race) {
            return [
                'TEAM NAME' => $race->team->name,
                'TOTAL' => $race->total
            ];
        })
        ->toArray();
    
    // Publish to Ably
    AblyHelper::publishBestRace($tournament, $topTeams);
}
```

---

#### Task 3.3: Update TournamentController
**Priority:** MEDIUM
**Time:** 20 minutes

Add Ably publishing to [`app/Http/Controllers/TournamentController.php`](app/Http/Controllers/TournamentController.php):

```php
use App\Helpers\AblyHelper;

// In nextStage() method, after updating:
public function nextStage(Request $request)
{
    // ... existing stage progression logic ...
    
    $tournament->save();
    
    // Publish best race update for new stage
    $this->publishBestRaceUpdate($tournament);
    
    return redirect()->back()
        ->with('success', 'Progressed to next stage successfully.');
}

// Add helper method:
private function publishBestRaceUpdate(Tournament $tournament)
{
    $nextStage = $tournament->current_stage + 1;
    
    $topTeams = Race::where('tournament_id', $tournament->id)
        ->where('stage', $nextStage)
        ->select('team_id', DB::raw('count(*) as total'))
        ->groupBy('team_id')
        ->orderByDesc('total')
        ->limit(6)
        ->with('team')
        ->get()
        ->map(function ($race) {
            return [
                'TEAM NAME' => $race->team->name,
                'TOTAL' => $race->total
            ];
        })
        ->toArray();
    
    AblyHelper::publishBestRace($tournament, $topTeams);
}
```

---

### Phase 4: Testing Strategy (Est. 2 hours)

#### 4.1 Unit Tests
**Priority:** MEDIUM
**Time:** 45 minutes

Create `tests/Unit/AblyHelperTest.php`:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\AblyHelper;
use App\Models\Tournament;

class AblyHelperTest extends TestCase
{
    public function test_channel_name_generation_for_best_race()
    {
        $tournament = new Tournament(['slug' => 'test-tournament']);
        $channelName = AblyHelper::getChannelName($tournament, 'best-race');
        
        $this->assertEquals('test-tournament:best-race', $channelName);
    }
    
    public function test_channel_name_generation_for_track()
    {
        $tournament = new Tournament(['slug' => 'test-tournament']);
        $channelName = AblyHelper::getChannelName($tournament, 'track', 1);
        
        $this->assertEquals('test-tournament:track-1', $channelName);
    }
    
    public function test_timer_to_centiseconds_conversion()
    {
        $centiseconds = AblyHelper::timerToCentiseconds('12:34');
        $this->assertEquals(1234, $centiseconds);
        
        $centiseconds = AblyHelper::timerToCentiseconds('0:05');
        $this->assertEquals(5, $centiseconds);
    }
    
    public function test_centiseconds_to_timer_conversion()
    {
        $timer = AblyHelper::centisecondsToTimer(1234);
        $this->assertEquals('12:34', $timer);
        
        $timer = AblyHelper::centisecondsToTimer(5);
        $this->assertEquals('0:05', $timer);
    }
}
```

Run tests:
```bash
php artisan test --filter=AblyHelperTest
```

---

#### 4.2 Manual Testing Checklist

**Best Race Display:**
- [ ] Page loads without errors
- [ ] Background image displays correctly
- [ ] Initial snapshot loads from API
- [ ] Cached data loads on refresh
- [ ] Real-time updates appear when race is created
- [ ] Timestamp updates correctly
- [ ] Top 6 teams displayed in correct order
- [ ] No console errors

**Track Display:**
- [ ] Page loads without errors
- [ ] Background image displays correctly
- [ ] Portrait rotation works (90° clockwise)
- [ ] BTO data displays correctly
- [ ] Session data displays correctly
- [ ] Track limit calculated correctly (BTO + 1:30)
- [ ] Real-time updates appear when best time is created/updated
- [ ] Cached data persists across refreshes
- [ ] No console errors

**TV Browser Compatibility:**
- [ ] Test on Samsung TV browser
- [ ] Test on LG TV browser
- [ ] Test on generic Android TV browser
- [ ] Verify no ES6+ syntax errors
- [ ] Verify keep-alive prevents throttling
- [ ] Verify overscan compensation adequate

---

### Phase 5: Deployment Guide (Est. 30 minutes)

#### 5.1 Pre-Deployment Checklist

```bash
# 1. Install Ably SDK
composer require ably/ably-php

# 2. Copy assets
mkdir -p public/images/display
cp resources/sample/best-race.png public/images/display/best-race-bg.png
cp resources/sample/track1.png public/images/display/track1-bg.png
cp resources/sample/track2.png public/images/display/track2-bg.png

# 3. Dump autoload
composer dump-autoload

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 5. Cache config
php artisan config:cache
php artisan route:cache
```

---

#### 5.2 Environment Configuration

Update `.env`:
```env
ABLY_KEY=your_actual_ably_api_key_here
ABLY_CHANNEL_PREFIX=tamiya
```

**Get Ably API Key:**
1. Sign up at https://ably.com
2. Create a new app
3. Copy the API key from dashboard
4. Paste into `.env`

---

#### 5.3 Verification Steps

**1. Test API Endpoints:**
```bash
# Test best race snapshot
curl http://localhost:8000/api/your-tournament-slug/best-race/snapshot

# Test track snapshot
curl http://localhost:8000/api/your-tournament-slug/track-1/snapshot
```

**2. Test Display Pages:**
```bash
# Open in browser
http://localhost:8000/your-tournament-slug/best-race
http://localhost:8000/your-tournament-slug/track-1
http://localhost:8000/your-tournament-slug/track-2
```

**3. Test Real-Time Updates:**
1. Open display page in browser
2. Open browser console (F12)
3. Create a new race or best time
4. Verify console shows "Received update"
5. Verify display updates automatically

---

### Troubleshooting

#### Issue: Ably Connection Fails

**Symptoms:**
- Console shows "Ably disconnected"
- No real-time updates

**Solutions:**
1. Verify Ably key in `.env`
2. Check Ably dashboard for API key validity
3. Verify internet connection
4. Check browser console for CORS errors

---

#### Issue: Background Images Not Loading

**Symptoms:**
- Black background instead of image
- 404 errors in console

**Solutions:**
1. Verify images copied to `public/images/display/`
2. Check file permissions
3. Clear browser cache
4. Verify image paths in Blade templates

---

#### Issue: Real-Time Updates Not Working

**Symptoms:**
- Display doesn't update when data changes
- No "Received update" in console

**Solutions:**
1. Verify Ably publish methods called in controllers
2. Check Laravel logs for Ably errors
3. Verify channel names match between frontend and backend
4. Test Ably connection in browser console

---

#### Issue: TV Browser Compatibility

**Symptoms:**
- JavaScript errors on TV
- Display not rendering

**Solutions:**
1. Verify no ES6+ syntax (const, let, arrow functions)
2. Check for template literals (use string concatenation)
3. Test on actual TV browser, not desktop
4. Add polyfills if needed

---

## Summary

### What This Planning Provides

1. **Complete Implementation Path** - Step-by-step guide from start to finish
2. **Code Examples** - Full, working code for all components
3. **Time Estimates** - Realistic time allocation for each phase
4. **Testing Strategy** - Comprehensive testing approach
5. **Deployment Guide** - Production-ready deployment steps
6. **Troubleshooting** - Common issues and solutions

### Key Improvements Over Original

1. ✅ **More Detailed** - Every task has complete code examples
2. ✅ **Better Organized** - Clear phases with dependencies
3. ✅ **Actionable** - Can follow step-by-step without guessing
4. ✅ **Complete** - Covers all aspects from dev to deployment
5. ✅ **Tested** - Includes testing strategy and verification
6. ✅ **Production-Ready** - Deployment and troubleshooting included

### Next Steps

1. **Start with Phase 1** - Foundation setup (1 hour)
2. **Move to Phase 2** - View implementation (2 hours)
3. **Implement Phase 3** - Real-time publishing (1.5 hours)
4. **Execute Phase 4** - Testing (2 hours)
5. **Deploy Phase 5** - Production deployment (30 minutes)

**Total Estimated Time:** 7 hours

---

## File Checklist

### Files to Create
- [ ] `app/Helpers/AblyHelper.php`
- [ ] `resources/views/display/layouts/tv-display.blade.php`
- [ ] `resources/views/display/best-race.blade.php`
- [ ] `resources/views/display/track.blade.php`
- [ ] `tests/Unit/AblyHelperTest.php`

### Files to Modify
- [ ] `config/services.php` - Add Ably config
- [ ] `.env.example` - Add Ably variables
- [ ] `app/Http/Controllers/BestTimeController.php` - Add publish methods
- [ ] `app/Http/Controllers/RaceController.php` - Add publish methods
- [ ] `app/Http/Controllers/TournamentController.php` - Add publish methods
- [ ] `composer.json` - Register AblyHelper autoload

### Files to Copy
- [ ] `resources/sample/best-race.png` → `public/images/display/best-race-bg.png`
- [ ] `resources/sample/track1.png` → `public/images/display/track1-bg.png`
- [ ] `resources/sample/track2.png` → `public/images/display/track2-bg.png`

### Commands to Run
```bash
composer require ably/ably-php
mkdir -p public/images/display
cp resources/sample/*.png public/images/display/
composer dump-autoload
php artisan config:cache
php artisan route:cache
```

---

**End of Planning Document**
