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
