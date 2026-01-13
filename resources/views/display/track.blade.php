@extends('display.layouts.tv-display')

@section('title', 'Track ' . $trackNumber . ' - ' . $tournament->name)

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Tomorrow:wght@700&display=swap" rel="stylesheet">
<style>
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        overflow: hidden !important;
        background: black !important;
        font-family: 'Tomorrow', Arial, sans-serif;
    }

    :root {
        --design-w: 1080px;
        --design-h: 1920px;
        --overscan: 0.03;
    }

    .stage {
        position: absolute;
        top: 50%;
        left: 50%;
        width: var(--design-w);
        height: var(--design-h);
        transform-origin: center center;
        will-change: transform;
    }

    .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .overlay {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        text-align: center;
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        font-weight: 700;
        letter-spacing: 1px;
        z-index: 1;
    }

    #bto-time  { top: 550px; font-size: 150px; }
    #bto-team  { top: 710px; font-size: 100px; }
    
    #limit-time { top: 1500px; font-size: 150px; }
    
    #session-number{ top: 880px; font-size: 80px; color: #000; letter-spacing: 20px;}
    #session-time{ top: 1015px; font-size: 150px; }
    #session-team{ top: 1170px; font-size: 100px; }

    .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 64px;
        color: #fff;
        text-align: center;
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div id="stage" class="stage">
    <img src="/images/display/track{{ $trackNumber }}-bg.png" alt="Background" class="background-image" />
    <div id="loading" class="loading">Loading...</div>

    <div id="bto-time" class="overlay" >00:01</div>
    <div id="bto-team" class="overlay">TEAM 1</div>
    <div id="session-number" class="overlay">SESI 0</div>
    <div id="session-time" class="overlay">00:02</div>
    <div id="session-team" class="overlay">TEAM 2</div>
    <div id="limit-time" class="overlay">00:03</div>
</div>
@endsection

@section('scripts')
<script>
    var tournamentSlug = '{{ $tournament->slug }}';
    var trackNumber = {{ $trackNumber }};
    var channelName = tournamentSlug + ':track-' + trackNumber;
    var storageKey = 'track-' + trackNumber + '-' + tournamentSlug;
    var ablyKey = '{{ config("services.ably.key") }}';
    
    var ably = null;
    var channel = null;
    
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
    
    // ========= SCALE TO SCREEN =========
    function fitStage() {
        var stage = document.getElementById('stage');
        if (!stage) return;
        
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var overscan = 0.03;
        var safeVW = vw * (1 - 2 * overscan);
        var safeVH = vh * (1 - 2 * overscan);
        var designW = 1080;
        var designH = 1920;
        var scale = Math.min(safeVW / designH, safeVH / designW);
        stage.style.transform = 'translate(-50%, -50%) rotate(90deg) scale(' + scale + ')';
    }

    function onResize() {
        fitStage();
    }

    function onOrientationChange() {
        setTimeout(fitStage, 200);
    }

    if (window.addEventListener) {
        window.addEventListener('resize', onResize);
        window.addEventListener('orientationchange', onOrientationChange);
    } else if (window.attachEvent) {
        window.attachEvent('onresize', onResize);
    }

    fitStage();
    
    // ========= DOM HELPERS =========
    function setText(id, value) {
        var element = document.getElementById(id);
        if (element) {
            element.textContent = value || "";
        }
    }
    
    function setDisplay(id, display) {
        var element = document.getElementById(id);
        if (element) {
            element.style.display = display;
        }
    }
    
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
                try {
                    var data = JSON.parse(xhr.responseText);
                    renderStats(data);
                    cacheData(data);
                } catch (e) {
                    console.error('Failed to parse response:', e);
                    console.error('Response:', xhr.responseText);
                    setText('loading', 'Error loading data. Please check tournament slug.');
                }
            } else {
                console.error('Snapshot fetch failed with status:', xhr.status);
                console.error('Response:', xhr.responseText);
                setText('loading', 'Error loading data. Status: ' + xhr.status);
            }
        };
        xhr.onerror = function() {
            console.error('Snapshot fetch failed');
            setText('loading', 'Error loading data. Please check connection.');
        };
        xhr.send();
    }
    
    function renderStats(data) {
        // Hide loading
        setDisplay('loading', 'none');
        
        // Handle BTO data
        setDisplay('bto-time', 'block');
        setDisplay('bto-team', 'block');
        setDisplay('limit-time', 'block');
        
        if (data && data.bto) {
            setText('bto-time', escapeHtml(data.bto.TIMER));
            setText('bto-team', escapeHtml(data.bto.TEAM));
            setText('limit-time', escapeHtml(data.bto.LIMIT));
        } else {
            // Default values for BTO
            setText('bto-time', '00:00');
            setText('bto-team', 'TEAM A');
            setText('limit-time', '00:00');
        }
        
        // Handle Session data
        setDisplay('session-number', 'block');
        setDisplay('session-time', 'block');
        setDisplay('session-team', 'block');
        
        if (data && data.sesi) {
            setText('session-number', 'SESI ' + data.sesi.SESI);
            setText('session-time', escapeHtml(data.sesi.TIMER));
            setText('session-team', escapeHtml(data.sesi.TEAM));
        } else {
            // Default values for Session
            setText('session-number', 'SESI 0');
            setText('session-time', '00:00');
            setText('session-team', 'TEAM B');
        }
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
    
    // ========= ABLY LIVE UPDATES =========
    if (ably && channel) {
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
    }
    
    // ========= INITIAL LOAD =========
    loadCachedData();
    fetchSnapshot();
    
    // ========= KEEP ALIVE FOR SMART TV =========
    setInterval(function() {}, 60000);
</script>
@endsection
