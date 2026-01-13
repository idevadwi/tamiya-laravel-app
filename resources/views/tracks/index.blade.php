@extends('adminlte::page')

@section('title', 'Track Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Track Management</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
</div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Track Cards Row -->
<div class="row">
    @foreach($trackData as $trackNumber => $data)
        <div class="{{ $colClass }}">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-road mr-2"></i>
                        Track {{ $trackNumber }}
                    </h3>
                </div>
                <div class="card-body">
                    <!-- BTO Section -->
                    <div class="mb-3">
                        <h5 class="text-primary font-weight-bold">
                            <i class="fas fa-trophy"></i> BTO
                        </h5>
                        @if($data['bto'])
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Timer</small>
                                    <div class="h2 mb-0">
                                        <span class="badge badge-success" style="font-size: 1.2em;">
                                            {{ $data['bto']->timer }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Team</small>
                                    <div class="h1 mb-0">{{ $data['bto']->team->team_name }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                <em>No BTO recorded yet</em>
                            </div>
                        @endif
                    </div>

                    <hr>

                    <!-- Session Section -->
                    <div class="mb-3">
                        <h5 class="text-warning font-weight-bold">
                            <i class="fas fa-clock"></i> SESSION {{ $data['current_session'] }}
                        </h5>
                        @if($data['session'])
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Timer</small>
                                    <div class="h2 mb-0">
                                        <span class="badge badge-info" style="font-size: 1.2em;">
                                            {{ $data['session']->timer }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Team</small>
                                    <div class="h1 mb-0">{{ $data['session']->team->team_name }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                <em>No session record yet</em>
                            </div>
                        @endif
                    </div>

                    <hr>

                    <!-- Limit Section -->
                    <div class="mb-3">
                        <h4 class="text-secondary font-weight-bold">
                            <i class="fas fa-hourglass-half"></i> LIMIT
                        </h4>
                        @if($data['limit'])
                            <div class="h1 mb-0">
                                <span class="badge badge-danger" style="font-size: 1.1em;">
                                    {{ $data['limit'] }}
                                </span>
                            
                            </div>
                        @else
                            <div class="text-muted">
                                <em>Set BTO to calculate limit</em>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100" role="group">
                        <button type="button" 
                                class="btn btn-primary"
                                data-toggle="modal"
                                data-target="#updateBtoModal"
                                data-track="{{ $trackNumber }}"
                                title="Update BTO">
                            <i class="fas fa-trophy"></i> Update BTO
                        </button>
                        <button type="button"
                                class="btn btn-warning"
                                data-toggle="modal"
                                data-target="#updateSesiModal"
                                data-track="{{ $trackNumber }}"
                                data-session="{{ $data['current_session'] }}"
                                title="Update Session">
                            <i class="fas fa-clock"></i> Update SESI
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Info Box -->
<div class="alert alert-info mt-3">
    <h5><i class="icon fas fa-info-circle"></i> About Track Management</h5>
    <ul class="mb-0">
        <li><strong>BTO (Best Time Overall):</strong> The best time throughout the entire tournament for a specific track.</li>
        <li><strong>Session:</strong> The best time for the current session (Session {{ $tournament->current_bto_session }}).</li>
        <li><strong>Limit:</strong> Calculated as BTO + 1:50. This is the maximum allowed time for qualifying.</li>
        <li><strong>Update BTO:</strong> Record a new best overall time for this track. Only times better than the current BTO can be recorded.</li>
        <li><strong>Update SESI:</strong> Record a new best session time for this track. Only times better than the current session record can be recorded.</li>
    </ul>
</div>

<!-- Update BTO Modal -->
<div class="modal fade" id="updateBtoModal" tabindex="-1" role="dialog" aria-labelledby="updateBtoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('tournament.best_times.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="updateBtoModalLabel">
                        <i class="fas fa-trophy"></i> Update BTO - Track <span id="btoTrackNumber"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="track" id="btoTrackInput">
                    <input type="hidden" name="scope" value="OVERALL">
                    <input type="hidden" name="redirect_to" value="tournament.tracks.index">
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Note:</strong>
                        You can only record times that are better than the existing BTO for this track.
                    </div>

                    <div class="form-group">
                        <label for="bto_team_id">Team <span class="text-danger">*</span></label>
                        <select name="team_id" id="bto_team_id" class="form-control select2" required>
                            <option value="">Select Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bto_timer">Timer (seconds:milliseconds) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bto_timer" name="timer"
                            placeholder="e.g., 14:20" pattern="\d{1,2}:\d{2}" required>
                        <small class="form-text text-muted">Format: MM:SS (e.g., 14:20)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update BTO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update SESI Modal -->
<div class="modal fade" id="updateSesiModal" tabindex="-1" role="dialog" aria-labelledby="updateSesiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('tournament.best_times.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="updateSesiModalLabel">
                        <i class="fas fa-clock"></i> Update SESI - Track <span id="sesiTrackNumber"></span> (Session <span id="sesiSessionNumber"></span>)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="track" id="sesiTrackInput">
                    <input type="hidden" name="scope" value="SESSION">
                    <input type="hidden" name="session_number" id="sesiSessionInput">
                    <input type="hidden" name="redirect_to" value="tournament.tracks.index">
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Note:</strong>
                        You can only record times that are better than the existing session record for this track.
                    </div>

                    <div class="form-group">
                        <label for="sesi_team_id">Team <span class="text-danger">*</span></label>
                        <select name="team_id" id="sesi_team_id" class="form-control select2" required>
                            <option value="">Select Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sesi_timer">Timer (seconds:milliseconds) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sesi_timer" name="timer"
                            placeholder="e.g., 14:20" pattern="\d{1,2}:\d{2}" required>
                        <small class="form-text text-muted">Format: MM:SS (e.g., 14:20)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update SESI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-body .h4 {
        line-height: 1.2;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function () {
        // Handle Update BTO button click
        $('.btn-primary[data-target="#updateBtoModal"]').on('click', function () {
            var trackNumber = $(this).data('track');
            
            // Set track number in modal title
            $('#btoTrackNumber').text(trackNumber);
            
            // Set track input value
            $('#btoTrackInput').val(trackNumber);
            
            // Reset form
            $('#bto_team_id').val('').trigger('change');
            $('#bto_timer').val('');
        });

        // Handle Update SESI button click
        $('.btn-warning[data-target="#updateSesiModal"]').on('click', function () {
            var trackNumber = $(this).data('track');
            var sessionNumber = $(this).data('session');
            
            // Set track and session numbers in modal title
            $('#sesiTrackNumber').text(trackNumber);
            $('#sesiSessionNumber').text(sessionNumber);
            
            // Set input values
            $('#sesiTrackInput').val(trackNumber);
            $('#sesiSessionInput').val(sessionNumber);
            
            // Reset form
            $('#sesi_team_id').val('').trigger('change');
            $('#sesi_timer').val('');
        });

        // Reset BTO modal on close
        $('#updateBtoModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
        });

        // Reset SESI modal on close
        $('#updateSesiModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
        });
    });
</script>
@stop
