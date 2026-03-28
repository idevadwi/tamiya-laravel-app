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
    <div class="alert-sweetalert" data-type="success" data-message="{{ session('success') }}"></div>
@endif

@if(session('error'))
    <div class="alert-sweetalert" data-type="error" data-message="{{ session('error') }}"></div>
@endif

@if(session('info'))
    <div class="alert-sweetalert" data-type="info" data-message="{{ session('info') }}"></div>
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
                <h3>{{ $currentStage ?? 0 }}</h3>
                <p>{{ __('messages.current_stage') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <a href="{{ route('tournament.races.index') }}" class="small-box-footer">
                {{ __('messages.view_races') }} <i class="fas fa-arrow-circle-right"></i>
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
                {{-- <a href="{{ route('tournament.teams.index') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-users"></i> {{ __('messages.manage_teams') }}
                </a> --}}
                <button type="button" class="btn btn-danger btn-block mb-2" data-toggle="modal"
                    data-target="#confirmNextStageModal">
                    <i class="fas fa-forward"></i> {{ __('messages.proceed_to_next_round') }}
                </button>
                <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal"
                    data-target="#confirmNextSessionModal">
                    <i class="fas fa-step-forward"></i> {{ __('messages.proceed_to_next_session') }}
                </button>
                {{-- <a href="{{ route('tournament.racers.index') }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-user-friends"></i> {{ __('messages.manage_racers') }}
                </a> --}}
                {{-- <a href="{{ route('tournament.cards.index') }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-credit-card"></i> {{ __('messages.manage_cards') }}
                </a> --}}
                <button type="button" class="btn btn-info btn-block mb-2" data-toggle="modal"
                    data-target="#balanceRacesModal">
                    <i class="fas fa-balance-scale"></i> {{ __('messages.balance_races') }}
                </button>
                <button type="button" class="btn btn-dark btn-block mb-2" data-toggle="modal"
                    data-target="#convertToSingleTrackModal">
                    <i class="fas fa-compress-arrows-alt"></i> {{ __('messages.convert_to_single_track') }}
                </button>
                {{-- <a href="{{ route('tournament.best_times.index') }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-stopwatch"></i> {{ __('messages.manage_best_times') }}
                </a> --}}
                <button type="button" class="btn btn-outline-danger btn-block mb-2" data-toggle="modal"
                    data-target="#deleteLastRaceModal">
                    <i class="fas fa-trash-alt"></i> {{ __('messages.delete_last_input_race') }}
                </button>
                <button type="button" class="btn btn-outline-danger btn-block mb-2" data-toggle="modal"
                    data-target="#deleteSpecificRaceModal">
                    <i class="fas fa-search-minus"></i> Delete Specific Race
                </button>
                <button type="button" class="btn btn-outline-primary btn-block mb-2" data-toggle="modal"
                    data-target="#shareLinksModal">
                    <i class="fas fa-share-alt"></i> {{ __('messages.share_links') }}
                </button>
                @if($isAdmin)
                    <a href="{{ route('tournaments.settings', $activeTournament->id) }}" class="btn btn-secondary btn-block">
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

<!-- Balance Races Modal -->
<div class="modal fade" id="balanceRacesModal" tabindex="-1" role="dialog" aria-labelledby="balanceRacesLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balanceRacesLabel">{{ __('messages.balance_races_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('messages.balance_races_message') }}</p>
                <form id="balanceRacesForm" action="{{ route('tournament.races.balance') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="stage">{{ __('messages.stage') }}</label>
                        <select class="form-control" id="stage" name="stage" required>
                            <option value="">{{ __('messages.select_stage') }}</option>
                            @for($i = 1; $i <= $activeTournament->current_stage + 1; $i++)
                                <option value="{{ $i }}">{{ __('messages.stage') }} {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="submit" form="balanceRacesForm" class="btn btn-info">
                    <i class="fas fa-balance-scale"></i> {{ __('messages.balance') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Convert to Single Track Modal -->
<div class="modal fade" id="convertToSingleTrackModal" tabindex="-1" role="dialog" aria-labelledby="convertToSingleTrackLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertToSingleTrackLabel">{{ __('messages.convert_to_single_track_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.convert_to_single_track_message') }}
                </div>
                <form id="convertToSingleTrackForm" action="{{ route('tournament.races.convertToSingleTrack') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="convertStage">{{ __('messages.stage') }}</label>
                        <select class="form-control" id="convertStage" name="stage" required>
                            <option value="">{{ __('messages.select_stage') }}</option>
                            @for($i = 1; $i <= $activeTournament->current_stage + 1; $i++)
                                <option value="{{ $i }}">{{ __('messages.stage') }} {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="submit" form="convertToSingleTrackForm" class="btn btn-dark">
                    <i class="fas fa-compress-arrows-alt"></i> {{ __('messages.convert') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Share Links Modal -->
<div class="modal fade" id="shareLinksModal" tabindex="-1" role="dialog" aria-labelledby="shareLinksLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareLinksLabel">
                    <i class="fas fa-share-alt"></i> {{ __('messages.share_links') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">{{ $activeTournament->tournament_name }}</p>

                {{-- Race Schedule --}}
                <div class="form-group">
                    <label class="font-weight-bold"><i class="fas fa-flag-checkered text-secondary"></i> Race Schedule</label>
                    <div class="input-group">
                        <input type="text" class="form-control share-link-input" readonly
                            value="{{ url($activeTournament->slug . '/races') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-copy" type="button"
                                data-clipboard-target="{{ url($activeTournament->slug . '/races') }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <button class="btn btn-outline-primary btn-show-qr" type="button"
                                data-qr-url="{{ url($activeTournament->slug . '/races') }}"
                                data-qr-label="Race Schedule">
                                <i class="fas fa-qrcode"></i> QR
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Best Race --}}
                <div class="form-group">
                    <label class="font-weight-bold"><i class="fas fa-trophy text-warning"></i> Best Race</label>
                    <div class="input-group">
                        <input type="text" class="form-control share-link-input" readonly
                            value="{{ url($activeTournament->slug . '/best-race') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-copy" type="button"
                                data-clipboard-target="{{ url($activeTournament->slug . '/best-race') }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="form-group">
                    <label class="font-weight-bold"><i class="fas fa-list-alt text-info"></i> Summary</label>
                    <div class="input-group">
                        <input type="text" class="form-control share-link-input" readonly
                            value="{{ url($activeTournament->slug . '/summary') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-copy" type="button"
                                data-clipboard-target="{{ url($activeTournament->slug . '/summary') }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tracks --}}
                @for($track = 1; $track <= $activeTournament->track_number; $track++)
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-flag text-danger"></i> Track {{ $track }}
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control share-link-input" readonly
                                value="{{ url($activeTournament->slug . '/track-' . $track) }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-copy" type="button"
                                    data-clipboard-target="{{ url($activeTournament->slug . '/track-' . $track) }}">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeModalLabel">
                    <i class="fas fa-qrcode"></i> QR Code — <span id="qrCodeTitle"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p id="qrCodeUrl" class="text-muted small text-break mb-3"></p>
                <div id="qrcode" class="d-inline-block"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Last Input Race Modal -->
<div class="modal fade" id="deleteLastRaceModal" tabindex="-1" role="dialog" aria-labelledby="deleteLastRaceLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLastRaceLabel">
                    <i class="fas fa-trash-alt text-danger"></i> {{ __('messages.delete_last_input_race') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="deleteLastRaceLoading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 text-muted">{{ __('messages.loading') }}...</p>
                </div>
                <div id="deleteLastRaceInfo" style="display:none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ __('messages.warning') }}:</strong>
                        {{ __('messages.delete_last_race_warning') }}
                    </div>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">{{ __('messages.current_stage') }}</th>
                            <td><span id="dlr-stage" class="badge badge-primary" style="font-size:1em;"></span></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.race_no') }}</th>
                            <td id="dlr-race-no"></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.track') }}</th>
                            <td id="dlr-track"></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.lane') }}</th>
                            <td id="dlr-lane"></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.team') }}</th>
                            <td><strong id="dlr-team"></strong></td>
                        </tr>
                    </table>
                </div>
                <div id="deleteLastRaceError" class="alert alert-warning" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLastRaceBtn" disabled>
                    <i class="fas fa-trash-alt"></i> {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Specific Race Modal -->
<div class="modal fade" id="deleteSpecificRaceModal" tabindex="-1" role="dialog" aria-labelledby="deleteSpecificRaceLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSpecificRaceLabel">
                    <i class="fas fa-search-minus text-danger"></i> Delete Specific Race
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>{{ __('messages.warning') }}:</strong> This will permanently delete the matching race entry.
                </div>
                <form id="deleteSpecificRaceForm">
                    @csrf
                    <div class="form-group">
                        <label for="dsr-stage">{{ __('messages.stage') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="dsr-stage" name="stage" required>
                            @for($i = 1; $i <= $activeTournament->current_stage + 1; $i++)
                                <option value="{{ $i }}" {{ $i == $activeTournament->current_stage + 1 ? 'selected' : '' }}>
                                    {{ __('messages.stage') }} {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dsr-race-no">Race No <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="dsr-race-no" name="race_no"
                            min="1" placeholder="e.g. 80" required>
                    </div>
                    <div class="form-group">
                        <label for="dsr-lane">Lane <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dsr-lane" name="lane"
                            maxlength="5" placeholder="e.g. F" style="text-transform:uppercase;" required>
                    </div>
                </form>
                <div id="deleteSpecificRaceError" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSpecificRaceBtn">
                    <i class="fas fa-trash-alt"></i> {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Next Session Modal -->
<div class="modal fade" id="confirmNextSessionModal" tabindex="-1" role="dialog" aria-labelledby="confirmNextSessionLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmNextSessionLabel">{{ __('messages.proceed_next_session_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('messages.proceed_next_session_message', ['current' => $currentSession, 'next' => $currentSession + 1]) }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('messages.cancel') }}</button>
                <form id="nextSessionForm" action="{{ route('tournament.tournaments.nextSession') }}" method="POST"
                    class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
<script>
    $(document).ready(function() {
        // Show session alerts using SweetAlert2
        $('.alert-sweetalert').each(function() {
            var type = $(this).data('type');
            var message = $(this).data('message');
            
            var swalConfig = {
                icon: type === 'error' ? 'error' : type,
                title: type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Information',
                text: message,
                confirmButtonColor: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8',
                confirmButtonText: 'OK'
            };
            
            if (type === 'success') {
                Swal.fire(swalConfig).then(function() {
                    $(this).remove();
                });
            } else if (type === 'error') {
                Swal.fire(swalConfig);
            } else if (type === 'info') {
                Swal.fire(swalConfig);
            }
        });

        // Handle balance races form submission
        $('#balanceRacesForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Close modal
                    $('#balanceRacesModal').modal('hide');
                    
                    // Show success message
                    if (response.success) {
                        var message = response.success;
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: message,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            location.reload();
                        });
                    } else if (response.info) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Information',
                            text: response.info,
                            confirmButtonColor: '#17a2b8',
                            confirmButtonText: 'OK'
                        });
                        $('#balanceRacesModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    // Show error message
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle convert to single track form submission
        $('#convertToSingleTrackForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Close modal
                    $('#convertToSingleTrackModal').modal('hide');
                    
                    // Show success message
                    if (response.success) {
                        var message = response.success;
                        var detailsHtml = '';
                        
                        // Add details if available
                        if (response.details && response.details.length > 0) {
                            detailsHtml = '<div style="text-align: left; margin-top: 15px;"><strong>Conversion details:</strong><ul style="margin: 10px 0 0 20px;">';
                            response.details.forEach(function(detail) {
                                detailsHtml += '<li>Race ' + detail.original_race + ' → ' + detail.new_race + ' (' + detail.team + ')</li>';
                            });
                            detailsHtml += '</ul></div>';
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: message + detailsHtml,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            location.reload();
                        });
                    } else if (response.info) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Information',
                            text: response.info,
                            confirmButtonColor: '#17a2b8',
                            confirmButtonText: 'OK'
                        });
                        $('#convertToSingleTrackModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    // Show error message
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Copy to clipboard for share links
        $(document).on('click', '.btn-copy', function() {
            var text = $(this).data('clipboard-target');
            navigator.clipboard.writeText(text).then(() => {
                var btn = $(this);
                btn.html('<i class="fas fa-check"></i> Copied!').addClass('btn-success').removeClass('btn-outline-secondary');
                setTimeout(function() {
                    btn.html('<i class="fas fa-copy"></i> Copy').removeClass('btn-success').addClass('btn-outline-secondary');
                }, 2000);
            });
        });

        // QR Code generation
        var qrCodeInstance = null;

        $(document).on('click', '.btn-show-qr', function() {
            var url = $(this).data('qr-url');
            var label = $(this).data('qr-label');

            $('#qrCodeTitle').text(label);
            $('#qrCodeUrl').text(url);

            // Clear previous QR and regenerate
            var qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '';
            qrCodeInstance = new QRCode(qrContainer, {
                text: url,
                width: 220,
                height: 220,
                correctLevel: QRCode.CorrectLevel.H
            });

            $('#qrCodeModal').modal('show');
        });

        // Clear QR when modal closes
        $('#qrCodeModal').on('hidden.bs.modal', function() {
            document.getElementById('qrcode').innerHTML = '';
            qrCodeInstance = null;
        });

        // Delete Last Input Race Modal
        $('#deleteLastRaceModal').on('show.bs.modal', function () {
            $('#deleteLastRaceLoading').show();
            $('#deleteLastRaceInfo').hide();
            $('#deleteLastRaceError').hide();
            $('#confirmDeleteLastRaceBtn').prop('disabled', true);

            $.ajax({
                url: '{{ route("tournament.races.lastInputPreview") }}',
                method: 'GET',
                success: function (data) {
                    $('#dlr-stage').text(data.stage);
                    $('#dlr-race-no').text(data.race_no);
                    $('#dlr-track').text(data.track);
                    $('#dlr-lane').text(data.lane);
                    $('#dlr-team').text(data.team_name);
                    $('#deleteLastRaceLoading').hide();
                    $('#deleteLastRaceInfo').show();
                    $('#confirmDeleteLastRaceBtn').prop('disabled', false);
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Failed to load race info.';
                    $('#deleteLastRaceLoading').hide();
                    $('#deleteLastRaceError').text(msg).show();
                }
            });
        });

        $('#confirmDeleteLastRaceBtn').on('click', function () {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: '{{ route("tournament.races.deleteLastInput") }}',
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    $('#deleteLastRaceModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.success,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> {{ __("messages.delete") }}');
                }
            });
        });

        // Delete Specific Race
        $('#deleteSpecificRaceModal').on('show.bs.modal', function () {
            $('#deleteSpecificRaceError').hide();
            $('#dsr-race-no').val('');
            $('#dsr-lane').val('');
        });

        $('#dsr-lane').on('input', function () {
            $(this).val($(this).val().toUpperCase());
        });

        $('#confirmDeleteSpecificRaceBtn').on('click', function () {
            var btn = $(this);
            var stage = $('#dsr-stage').val();
            var raceNo = $('#dsr-race-no').val().trim();
            var lane = $('#dsr-lane').val().trim();

            if (!stage || !raceNo || !lane) {
                $('#deleteSpecificRaceError').text('Please fill in all fields.').show();
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
            $('#deleteSpecificRaceError').hide();

            $.ajax({
                url: '{{ route("tournament.races.deleteByRaceNoLane") }}',
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}', stage: stage, race_no: raceNo, lane: lane },
                success: function (response) {
                    $('#deleteSpecificRaceModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.success,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#deleteSpecificRaceError').text(errorMsg).show();
                    btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> {{ __("messages.delete") }}');
                }
            });
        });

        // Handle next session form submission
        $('#nextSessionForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Close modal
                    $('#confirmNextSessionModal').modal('hide');
                    
                    // Show success message
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.success,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    // Show error message
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
@stop
