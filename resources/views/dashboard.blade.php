@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Tournament Dashboard</h1>
            <p class="text-muted mb-0">Active Tournament: <strong>{{ $activeTournament->tournament_name }}</strong></p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="fas fa-exchange-alt"></i> Switch Tournament
        </a>
    </div>
@stop

@section('content')
    @php
        $isAdmin = auth()->check() && auth()->user()->hasRole('ADMINISTRATOR');
    @endphp
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $activeTournament->participants()->count() }}</h3>
                    <p>Teams</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('teams.index') }}" class="small-box-footer">
                    View Teams <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalRacers ?? 0 }}</h3>
                    <p>Racers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <a href="{{ route('racers.index') }}" class="small-box-footer">
                    View Racers <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalCards ?? 0 }}</h3>
                    <p>Cards</p>
                </div>
                <div class="icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <a href="{{ route('cards.index') }}" class="small-box-footer">
                    View Cards <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $raceCount ?? 0 }}</h3>
                    <p>Races</p>
                </div>
                <div class="icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <a href="{{ route('races.index') }}" class="small-box-footer">
                    View Races <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tournament Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Tournament Name</th>
                            <td>{{ $activeTournament->tournament_name }}</td>
                        </tr>
                        <tr>
                            <th>Current Stage</th>
                            <td>{{ $activeTournament->current_stage }}</td>
                        </tr>
                        <tr>
                            <th>Vendor Name</th>
                            <td>{{ $activeTournament->vendor_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($activeTournament->status === 'ACTIVE')
                                    <span class="badge badge-success">{{ $activeTournament->status }}</span>
                                @elseif($activeTournament->status === 'COMPLETED')
                                    <span class="badge badge-info">{{ $activeTournament->status }}</span>
                                @elseif($activeTournament->status === 'CANCELLED')
                                    <span class="badge badge-danger">{{ $activeTournament->status }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $activeTournament->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Track Number</th>
                            <td>{{ $activeTournament->track_number }}</td>
                        </tr>
                        <tr>
                            <th>Max Racers Per Team</th>
                            <td>{{ $activeTournament->max_racer_per_team }}</td>
                        </tr>
                        <tr>
                            <th>Current BTO Session</th>
                            <td>
                                <span class="badge badge-primary" style="font-size: 1.1em;">
                                    <i class="fas fa-clock"></i> Session {{ $currentSession }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('teams.index') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-users"></i> Manage Teams
                    </a>
                    <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal" data-target="#confirmNextStageModal">
                        <i class="fas fa-forward"></i> Proceed to Next Round
                    </button>
                    <a href="{{ route('racers.index') }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-user-friends"></i> Manage Racers
                    </a>
                    <a href="{{ route('cards.index') }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-credit-card"></i> Manage Cards
                    </a>
                    <a href="{{ route('best_times.index') }}" class="btn btn-secondary btn-block mb-2">
                        <i class="fas fa-stopwatch"></i> Manage Best Times
                    </a>
                    @if($isAdmin)
                        <a href="{{ route('tournaments.show', $activeTournament->id) }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-info-circle"></i> Tournament Details
                        </a>
                        <a href="{{ route('tournaments.edit', $activeTournament->id) }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-cog"></i> Tournament Settings
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Best Times Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i> Best Times Overall (BTO)
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('best_times.index', ['scope' => 'OVERALL']) }}" class="btn btn-tool btn-sm">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($bestTimesOverall->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="30%">Track</th>
                                        <th width="40%">Team</th>
                                        <th width="30%">Best Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($track = 1; $track <= $activeTournament->track_number; $track++)
                                        <tr>
                                            <td>
                                                <span class="badge badge-info">Track {{ $track }}</span>
                                            </td>
                                            @if(isset($bestTimesOverall[$track]) && $bestTimesOverall[$track]->count() > 0)
                                                @php
                                                    $bestTime = $bestTimesOverall[$track]->first();
                                                @endphp
                                                <td><strong>{{ $bestTime->team->team_name }}</strong></td>
                                                <td>
                                                    <span class="badge badge-success" style="font-size: 1.1em;">
                                                        {{ $bestTime->timer }}
                                                    </span>
                                                </td>
                                            @else
                                                <td colspan="2" class="text-muted text-center">
                                                    No time recorded yet
                                                </td>
                                            @endif
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted py-3">
                            <i class="fas fa-info-circle"></i> No overall best times recorded yet.
                            <br>
                            <a href="{{ route('best_times.create') }}">Record one now</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Best Times - Session {{ $currentSession }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('best_times.index', ['scope' => 'SESSION', 'session_number' => $currentSession]) }}" class="btn btn-tool btn-sm">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($bestTimesSession->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="30%">Track</th>
                                        <th width="40%">Team</th>
                                        <th width="30%">Best Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($track = 1; $track <= $activeTournament->track_number; $track++)
                                        <tr>
                                            <td>
                                                <span class="badge badge-info">Track {{ $track }}</span>
                                            </td>
                                            @if(isset($bestTimesSession[$track]) && $bestTimesSession[$track]->count() > 0)
                                                @php
                                                    $bestTime = $bestTimesSession[$track]->first();
                                                @endphp
                                                <td><strong>{{ $bestTime->team->team_name }}</strong></td>
                                                <td>
                                                    <span class="badge badge-warning" style="font-size: 1.1em;">
                                                        {{ $bestTime->timer }}
                                                    </span>
                                                </td>
                                            @else
                                                <td colspan="2" class="text-muted text-center">
                                                    No time recorded yet
                                                </td>
                                            @endif
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted py-3">
                            <i class="fas fa-info-circle"></i> No session best times recorded yet for Session {{ $currentSession }}.
                            <br>
                            <a href="{{ route('best_times.create') }}">Record one now</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Next Stage Modal -->
    <div class="modal fade" id="confirmNextStageModal" tabindex="-1" role="dialog" aria-labelledby="confirmNextStageLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmNextStageLabel">Proceed to Next Round</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to move to the next round? This will advance the tournament stage from <strong>{{ $activeTournament->current_stage }}</strong> to <strong>{{ $activeTournament->current_stage + 1 }}</strong>.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="nextStageForm" action="{{ route('tournaments.nextStage') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

