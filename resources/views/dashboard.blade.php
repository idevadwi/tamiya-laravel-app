@extends('adminlte::page')

@section('title', __('messages.dashboard'))

@section('content_top_nav_right')
@include('vendor.adminlte.partials.navbar.menu-item-language-switcher')
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ __('messages.tournament_dashboard') }}</h1>
        <p class="text-muted mb-0">{{ __('messages.active_tournament') }}:
            <strong>{{ $activeTournament->tournament_name }}</strong>
        </p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="fas fa-exchange-alt"></i> {{ __('messages.switch_tournament') }}
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
                <p>{{ __('messages.teams') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('tournament.teams.index') }}" class="small-box-footer">
                {{ __('messages.view_teams') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalRacers ?? 0 }}</h3>
                <p>{{ __('messages.racers') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-friends"></i>
            </div>
            <a href="{{ route('tournament.racers.index') }}" class="small-box-footer">
                {{ __('messages.view_racers') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalCards ?? 0 }}</h3>
                <p>{{ __('messages.cards') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <a href="{{ route('tournament.cards.index') }}" class="small-box-footer">
                {{ __('messages.view_cards') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $raceCount ?? 0 }}</h3>
                <p>{{ __('messages.races') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <a href="{{ route('tournament.races.index') }}" class="small-box-footer">
                {{ __('messages.view_races') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('messages.tournament_information') }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">{{ __('messages.tournament_name') }}</th>
                        <td>{{ $activeTournament->tournament_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.current_stage') }}</th>
                        <td>{{ $activeTournament->current_stage }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.vendor_name') }}</th>
                        <td>{{ $activeTournament->vendor_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.status') }}</th>
                        <td>
                            @if($activeTournament->status === 'ACTIVE')
                                <span class="badge badge-success">{{ __('messages.active') }}</span>
                            @elseif($activeTournament->status === 'COMPLETED')
                                <span class="badge badge-info">{{ __('messages.completed') }}</span>
                            @elseif($activeTournament->status === 'CANCELLED')
                                <span class="badge badge-danger">{{ __('messages.cancelled') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $activeTournament->status }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.track_number') }}</th>
                        <td>{{ $activeTournament->track_number }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.max_racers_per_team') }}</th>
                        <td>{{ $activeTournament->max_racer_per_team }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.current_bto_session') }}</th>
                        <td>
                            <span class="badge badge-primary" style="font-size: 1.1em;">
                                <i class="fas fa-clock"></i> {{ __('messages.session') }} {{ $currentSession }}
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
                <h3 class="card-title">{{ __('messages.quick_actions') }}</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('tournament.teams.index') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-users"></i> {{ __('messages.manage_teams') }}
                </a>
                <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal"
                    data-target="#confirmNextStageModal">
                    <i class="fas fa-forward"></i> {{ __('messages.proceed_to_next_round') }}
                </button>
                <a href="{{ route('tournament.racers.index') }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-user-friends"></i> {{ __('messages.manage_racers') }}
                </a>
                <a href="{{ route('tournament.cards.index') }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-credit-card"></i> {{ __('messages.manage_cards') }}
                </a>
                <a href="{{ route('tournament.best_times.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-stopwatch"></i> {{ __('messages.manage_best_times') }}
                </a>
                @if($isAdmin)
                    <a href="{{ route('tournaments.show', $activeTournament->id) }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-info-circle"></i> {{ __('messages.tournament_details') }}
                    </a>
                    <a href="{{ route('tournaments.edit', $activeTournament->id) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-cog"></i> {{ __('messages.tournament_settings') }}
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
                    <i class="fas fa-trophy"></i> {{ __('messages.best_times_overall') }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('tournament.best_times.index', ['scope' => 'OVERALL']) }}"
                        class="btn btn-tool btn-sm">
                        <i class="fas fa-eye"></i> {{ __('messages.view_all') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($bestTimesOverall->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="30%">{{ __('messages.track') }}</th>
                                    <th width="40%">{{ __('messages.team') }}</th>
                                    <th width="30%">{{ __('messages.best_time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($track = 1; $track <= $activeTournament->track_number; $track++)
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">{{ __('messages.track') }} {{ $track }}</span>
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
                                                {{ __('messages.no_time_recorded') }}
                                            </td>
                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i> {{ __('messages.no_overall_best_times') }}
                        <br>
                        <a href="{{ route('tournament.best_times.create') }}">{{ __('messages.record_one_now') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> {{ __('messages.best_times_session', ['session' => $currentSession]) }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('tournament.best_times.index', ['scope' => 'SESSION', 'session_number' => $currentSession]) }}"
                        class="btn btn-tool btn-sm">
                        <i class="fas fa-eye"></i> {{ __('messages.view_all') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($bestTimesSession->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="30%">{{ __('messages.track') }}</th>
                                    <th width="40%">{{ __('messages.team') }}</th>
                                    <th width="30%">{{ __('messages.best_time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($track = 1; $track <= $activeTournament->track_number; $track++)
                                    <tr>
                                        <td>
                                            <span class="badge badge-info">{{ __('messages.track') }} {{ $track }}</span>
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
                                                {{ __('messages.no_time_recorded') }}
                                            </td>
                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i>
                        {{ __('messages.no_session_best_times', ['session' => $currentSession]) }}
                        <br>
                        <a href="{{ route('tournament.best_times.create') }}">{{ __('messages.record_one_now') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Confirm Next Stage Modal -->
<div class="modal fade" id="confirmNextStageModal" tabindex="-1" role="dialog" aria-labelledby="confirmNextStageLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmNextStageLabel">{{ __('messages.proceed_next_round_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('messages.proceed_next_round_message', ['current' => $activeTournament->current_stage, 'next' => $activeTournament->current_stage + 1]) }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <form id="nextStageForm" action="{{ route('tournament.tournaments.nextStage') }}" method="POST"
                    class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        {{ __('messages.confirm') }}
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