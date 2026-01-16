@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')

    {{-- Custom CSS for Add Race Menu Item --}}
    <style>
        /* Add Race Menu Item - Attractive Styling */
        .add-race-menu-item {
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-radius: 8px !important;
            margin: 5px 8px !important;
            padding: 2px 0 !important;
            box-shadow: 0 4px 15px 0 rgba(102, 126, 234, 0.4) !important;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .add-race-menu-item a {
            color: #ffffff !important;
            font-weight: 600 !important;
            padding: 12px 16px !important;
        }

        .add-race-menu-item .nav-icon {
            color: #ffffff !important;
            animation: rotate-icon 3s linear infinite;
        }

        /* Pulse Glow Animation */
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 4px 15px 0 rgba(102, 126, 234, 0.4);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 4px 25px 0 rgba(102, 126, 234, 0.75);
                transform: scale(1.02);
            }
        }

        /* Rotating Icon Animation */
        @keyframes rotate-icon {
            0%, 90%, 100% {
                transform: rotate(0deg);
            }
            92%, 96% {
                transform: rotate(-15deg);
            }
            94%, 98% {
                transform: rotate(15deg);
            }
        }

        /* Hover Effect */
        .add-race-menu-item:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
            box-shadow: 0 6px 30px 0 rgba(102, 126, 234, 0.8) !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .add-race-menu-item:hover .nav-icon {
            animation: shake 0.5s ease-in-out infinite;
        }

        /* Shake Animation on Hover */
        @keyframes shake {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-10deg);
            }
            75% {
                transform: rotate(10deg);
            }
        }

        /* Add a subtle shine effect */
        .add-race-menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
            border-radius: 8px;
        }

        @keyframes shine {
            0% {
                left: -100%;
            }
            50%, 100% {
                left: 100%;
            }
        }

        /* Active state */
        .add-race-menu-item.active {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%) !important;
        }

        /* Badge/Label for "NEW" or "HOT" (optional) */
        .add-race-menu-item::after {
            content: '⚡ SCAN HERE!';
            position: absolute;
            top: 5px;
            right: 10px;
            background: #ffd700;
            color: #000;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            animation: badge-pulse 2s ease-in-out infinite;
        }

        @keyframes badge-pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    </style>
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
