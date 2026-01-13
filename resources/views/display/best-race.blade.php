@extends('display.layouts.tv-display')

@section('title', 'Best Race - ' . $tournament->name)

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Tomorrow:wght@700&display=swap" rel="stylesheet">
<style>
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: url('/images/display/best-race-bg.png') no-repeat center center fixed !important;
        background-size: cover !important;
        font-family: 'Tomorrow', Arial, sans-serif;
        color: white;
        text-align: center;
        height: 100vh !important;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-shadow: 2px 2px 5px #000;
    }

    .leaderboard {
        margin-top: 430px;
        font-size: 75px;
        width: 1000px;
    }

    .entry {
        display: flex;
        justify-content: space-between;
        margin: 10px 0;
    }

    .rank,
    .team {
        text-align: left;
    }

    .rank {
        width: 100px;
    }

    .team {
        width: 500px;
        flex: 1;
    }

    .score {
        width: 200px;
        text-align: right;
        color: #FFD700;
    }

    #refresh-indicator {
        position: absolute;
        bottom: 20px;
        right: 40px;
        font-size: 20px;
        color: #ccc;
        font-weight: normal;
    }

    .loading {
        font-size: 48px;
    }
</style>
@endsection

@section('content')
<div id="refresh-indicator">Last updated: <span id="last-updated">-</span></div>
<div class="leaderboard" id="leaderboard">
    <div class="loading">Loading...</div>
</div>
@endsection

@section('scripts')
<script>
    var tournamentSlug = '{{ $tournament->slug }}';
    var channelName = tournamentSlug + ':best-race';
    var storageKey = 'best-race-' + tournamentSlug;
    var ablyKey = '{{ config("services.ably.key") }}';
    
    var ably = null;
    var channel = null;
    var currentData = [];
    
    // Only initialize Ably if key is configured
    if (ablyKey && ablyKey !== '') {
        try {
            ably = new Ably.Realtime(ablyKey);
            channel = ably.channels.get(channelName);
        } catch (e) {
            console.error('Ably initialization failed:', e);
        }
    } else {
        console.warn('Ably key not configured. Real-time updates disabled.');
    }
    
    var leaderboardEl = document.getElementById('leaderboard');
    var lastUpdatedEl = document.getElementById('last-updated');
    
    // Helper function to convert values to numbers (TV compatible)
    function toNumber(v) {
        if (v === null || v === undefined) return 0;
        var str = String(v).replace(/[, ]+/g, '');
        var n = Number(str);
        return isFinite(n) ? n : 0;
    }
    
    // Function to update the leaderboard display (TV compatible)
    function updateLeaderboard(data) {
        leaderboardEl.innerHTML = "";

        if (!data || !data.length) {
            leaderboardEl.innerHTML = '<div class="loading">No data available</div>';
            return;
        }

        // Sort data by score (descending) and take top 6
        var sortedData = data.slice(); // Copy array
        sortedData.sort(function(a, b) {
            var scoreA = toNumber(a.TOTAL || a.total || a.score || a.points);
            var scoreB = toNumber(b.TOTAL || b.total || b.score || b.points);
            return scoreB - scoreA;
        });
        sortedData = sortedData.slice(0, 6);

        for (var i = 0; i < sortedData.length; i++) {
            var entry = sortedData[i];
            var entryDiv = document.createElement("div");
            entryDiv.className = "entry";
            
            // Handle different data formats more robustly
            var teamName = entry["TEAM NAME"] || entry["teamName"] || entry.team || entry.name || ("TEAM " + (i + 1));
            var score = entry.TOTAL || entry.total || entry.score || entry.points || 0;
            
            entryDiv.innerHTML = 
                '<span class="rank">' + (i + 1) + '.</span>' +
                '<span class="team">' + escapeHtml(teamName) + '</span>' +
                '<span class="score">' + score + '</span>';
            leaderboardEl.appendChild(entryDiv);
        }
    }
    
    function loadCachedData() {
        try {
            var cached = localStorage.getItem(storageKey);
            if (cached) {
                var data = JSON.parse(cached);
                currentData = data.items || [];
                updateLeaderboard(currentData);
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
                try {
                    var data = JSON.parse(xhr.responseText);
                    currentData = data.items || [];
                    updateLeaderboard(currentData);
                    updateTimestamp(data.updatedAt);
                    cacheData(data);
                } catch (e) {
                    console.error('Failed to parse response:', e);
                    console.error('Response:', xhr.responseText);
                    leaderboardEl.innerHTML = '<div class="loading">Error loading data. Please check tournament slug.</div>';
                }
            } else {
                console.error('Snapshot fetch failed with status:', xhr.status);
                console.error('Response:', xhr.responseText);
                leaderboardEl.innerHTML = '<div class="loading">Error loading data. Status: ' + xhr.status + '</div>';
            }
        };
        xhr.onerror = function() {
            console.error('Snapshot fetch failed');
            leaderboardEl.innerHTML = '<div class="loading">Error loading data. Please check connection.</div>';
        };
        xhr.send();
    }
    
    function updateTimestamp(timestamp) {
        var date = new Date(timestamp);
        var hours = padZero(date.getHours());
        var minutes = padZero(date.getMinutes());
        var seconds = padZero(date.getSeconds());
        lastUpdatedEl.textContent = hours + ':' + minutes + ':' + seconds;
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
    
    // ========= ABLY LIVE UPDATES =========
    if (ably && channel) {
        channel.subscribe('update', function(message) {
            console.log('Received update:', message.data);
            currentData = message.data.items || [];
            updateLeaderboard(currentData);
            updateTimestamp(message.data.updatedAt);
            cacheData(message.data);
        });
        
        ably.connection.on('connected', function() {
            console.log('Ably connected');
        });
        
        ably.connection.on('disconnected', function() {
            console.log('Ably disconnected');
        });
    }
    
    // ========= INITIAL LOAD =========
    loadCachedData();
    fetchSnapshot();
    
    // ========= KEEP ALIVE FOR SMART TV =========
    setInterval(function() {}, 60000);
</script>
@endsection
