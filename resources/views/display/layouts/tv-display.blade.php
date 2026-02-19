<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title')</title>
    
    {{-- Prevent caching --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    {{-- TV optimization --}}
    <meta name="format-detection" content="telephone=no">
    
    {{-- Ably Library (Local) --}}
    <script src="{{ asset('js/ably.min.js') }}"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
            color: #fff;
        }
        
        .overscan-safe {
            padding: 3%;
        }
        
        .tv-text {
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
    </style>
    
    @yield('styles')
</head>
<body>
    @yield('content')
    
    

    @yield('scripts')
</body>
</html>
