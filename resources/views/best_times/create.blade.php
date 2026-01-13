@extends('adminlte::page')

@section('title', 'Record New Best Time')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Record New Best Time</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.best_times.index') }}" class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Back to Best Times
    </a>
</div>
@stop

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Best Time Information</h3>
    </div>
    <form action="{{ route('tournament.best_times.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="team_id">Team <span class="text-danger">*</span></label>
                        <select name="team_id" id="team_id" class="form-control @error('team_id') is-invalid @enderror"
                            required>
                            <option value="">Select Team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->team_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="track">Track <span class="text-danger">*</span></label>
                        <select name="track" id="track" class="form-control @error('track') is-invalid @enderror"
                            required>
                            <option value="">Select Track</option>
                            @foreach($tracks as $track)
                                <option value="{{ $track }}" {{ old('track') == $track ? 'selected' : '' }}>
                                    Track {{ $track }}
                                </option>
                            @endforeach
                        </select>
                        @error('track')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="timer">Timer (seconds:milliseconds) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('timer') is-invalid @enderror" id="timer"
                            name="timer" value="{{ old('timer') }}" placeholder="e.g., 14:20 or 13:11"
                            pattern="\d{1,2}:\d{2}" required>
                        @error('timer')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Format: MM:SS (e.g., 14:20 means 14 seconds and 20 milliseconds)
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="scope">Scope <span class="text-danger">*</span></label>
                        <select name="scope" id="scope" class="form-control @error('scope') is-invalid @enderror"
                            required>
                            <option value="">Select Scope</option>
                            <option value="OVERALL" {{ old('scope') == 'OVERALL' ? 'selected' : '' }}>Overall</option>
                            <option value="SESSION" {{ old('scope') == 'SESSION' ? 'selected' : '' }}>Session</option>
                        </select>
                        @error('scope')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            <strong>Overall:</strong> Best time for the entire tournament<br>
                            <strong>Session:</strong> Best time for a specific session
                        </small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" id="session_number_group" style="display: none;">
                        <label for="session_number">Session Number</label>
                        <select name="session_number" id="session_number"
                            class="form-control @error('session_number') is-invalid @enderror">
                            <option value="">Select Session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session }}" {{ old('session_number') == $session ? 'selected' : '' }}>
                                    Session {{ $session }}
                                </option>
                            @endforeach
                        </select>
                        @error('session_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Required when scope is SESSION
                        </small>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-3">
                <h5><i class="icon fas fa-info-circle"></i> Important Notes</h5>
                <ul class="mb-0">
                    <li>If recording a <strong>SESSION</strong> time that beats the current <strong>OVERALL</strong>
                        time, the system will automatically update the OVERALL record.</li>
                    <li>Timer format is seconds:milliseconds (e.g., 14:20 = 14.20 seconds).</li>
                    <li>Sessions typically run for 1 hour each, and up to 10 sessions can be tracked.</li>
                </ul>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Record Best Time
            </button>
            <a href="{{ route('tournament.best_times.index') }}" class="btn btn-default">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    $(document).ready(function () {
        // Show/hide session number field based on scope selection
        $('#scope').on('change', function () {
            if ($(this).val() === 'SESSION') {
                $('#session_number_group').show();
                $('#session_number').prop('required', true);
            } else {
                $('#session_number_group').hide();
                $('#session_number').prop('required', false);
                $('#session_number').val('');
            }
        });

        // Trigger on page load if there's an old value
        if ($('#scope').val() === 'SESSION') {
            $('#session_number_group').show();
            $('#session_number').prop('required', true);
        }
    });
</script>
@stop