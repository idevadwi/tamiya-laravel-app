@extends('adminlte::master')

@section('adminlte_css')
@stack('css')
@yield('css')
@stop

@section('classes_body', 'layout-top-nav')

@section('body')
<style>
    /* Full width layout */
    .content-wrapper {
        margin-left: 0 !important;
    }

    .description-header {
            font-size: 3rem !important;
    }
    
    .description-text {
        font-size: 1.7rem !important;
    }

    .badge {
            font-size: 1.4rem !important;
    }

    .small-box .inner p {
            font-size: 1.8rem !important;
    }

    .small-box .inner h3 {
        font-size: 3.2rem !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .small-box {
            margin-bottom: 1rem;
        }
        .small-box .inner h3 {
            font-size: 2rem !important;
        }
        .card-title {
            font-size: 1.1rem !important;
        }
        .description-header {
            font-size: 1rem !important;
        }
        .description-text {
            font-size: 1.2rem !important;
        }
        .table th, .table td {
            font-size: 0.8rem;
            padding: 0.5rem !important;
        }
        .table .font-weight-bold {
            font-size: 1rem !important;
        }
        .badge {
            font-size: 1.1rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .content-header h1 {
            font-size: 1.5rem !important;
        }
        .small-box .inner h3 {
            font-size: 2.2rem !important;
        }
        .small-box .inner p {
            font-size: 1.6rem !important;
        }
        .card-title {
            font-size: 1rem !important;
        }
        .description-header {
            font-size: 1.9rem !important;
        }
        .description-text {
            font-size: 1.2rem !important;
        }
        .table th, .table td {
            font-size: 0.9rem;
            padding: 0.4rem !important;
        }
        .table .font-weight-bold {
            font-size: 1.3rem !important;
        }
    }
    
    /* Full width container override */
    .content > .container {
        max-width: 100% !important;
        padding: 0 1rem;
    }
    
    .content-header > .container {
        max-width: 100% !important;
        padding: 0 1rem;
    }
</style>

<div class="wrapper">

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-center"> {{ $tournament->tournament_name }} </h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Current Stage -->
                    <div class="col-lg-4 col-md-4 col-sm-12 col-12 mb-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $currentStage }}</h3>
                                <p>Current Stage</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Total Races -->
                    <div class="col-lg-4 col-md-4 col-sm-12 col-12 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $totalRaces }}</h3>
                                <p>Total Next Races (Stage {{ $currentStage + 1 }})</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Current Session -->
                    <div class="col-lg-4 col-md-4 col-sm-12 col-12 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $currentSession }}</h3>
                                <p>Current Session</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->



            <!-- Top Teams (Most Races) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-trophy mr-1"></i> Teams Best Race (Stage {{ $currentStage + 1 }})
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="row">
                                @foreach($topTeams as $index => $topTeam)
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 border-right">
                                        <div class="description-block p-2">
                                            <h3 class="description-header text-success">{{ $topTeam->total }}</h3>
                                            <div class="small mt-1 badge badge-info">#{{ $index + 1 }}</div>
                                            <span class="description-text">{{ $topTeam->team->team_name }}</span>
                                            
                                        </div>
                                    </div>
                                @endforeach
                                @if($topTeams->count() == 0)
                                    <div class="col-12 p-3 text-center text-muted">
                                        No races recorded in this stage yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Track Statistics</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered text-center table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%">Track</th>
                                            <th style="width: 30%">BTO</th>
                                            <th style="width: 30%">Track Limit</th>
                                            <th style="width: 30%">Best Time Session {{ $currentSession }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= $tournament->track_number; $i++)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-secondary"
                                                        style="font-size: 1.2em;">{{ $i }}</span>
                                                </td>
                                                <!-- BTO Overall -->
                                                <td>
                                                    @if(isset($btoOverall[$i]))
                                                        <div class="font-weight-bold text-success" style="font-size: 1.3em;">
                                                            {{ $btoOverall[$i]->timer }}
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="badge badge-light border" style="font-size: 0.85em;">
                                                                {{ $btoOverall[$i]->team->team_name }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <!-- Track Limit -->
                                                <td>
                                                    @if($trackLimits[$i] !== 'N/A')
                                                        <div class="font-weight-bold text-danger" style="font-size: 1.3em;">
                                                            {{ $trackLimits[$i] }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <!-- Session Best -->
                                                <td>
                                                    @if(isset($btoSession[$i]))
                                                        <div class="font-weight-bold text-warning" style="font-size: 1.3em;">
                                                            {{ $btoSession[$i]->timer }}
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="badge badge-light border" style="font-size: 0.85em;">
                                                                {{ $btoSession[$i]->team->team_name }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <div class="container-fluid">
        <div class="float-right d-none d-sm-inline">
            RACELANE MINI 4WD RACE APP
        </div>
        <strong>Copyright &copy; {{ date('Y') }}</strong>
    </div>
</footer>
</div>
<!-- ./wrapper -->

@section('adminlte_js')
@stack('js')
@yield('js')
@stop

@stop