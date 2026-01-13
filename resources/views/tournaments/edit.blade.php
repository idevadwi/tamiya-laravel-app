@extends('adminlte::page')

@section('title', 'Edit Tournament')

@section('content_header')
    <h1>Edit Tournament</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tournament Information</h3>
        </div>
        <form action="{{ route('tournaments.update', $tournament->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tournament_name">Tournament Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('tournament_name') is-invalid @enderror" 
                                   id="tournament_name" 
                                   name="tournament_name" 
                                   value="{{ old('tournament_name', $tournament->tournament_name) }}" 
                                   required>
                            @error('tournament_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vendor_name">Vendor Name</label>
                            <input type="text" 
                                   class="form-control @error('vendor_name') is-invalid @enderror" 
                                   id="vendor_name" 
                                   name="vendor_name" 
                                   value="{{ old('vendor_name', $tournament->vendor_name) }}">
                            @error('vendor_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status">
                                <option value="PLANNED" {{ old('status', $tournament->status) == 'PLANNED' ? 'selected' : '' }}>PLANNED</option>
                                <option value="ACTIVE" {{ old('status', $tournament->status) == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                <option value="COMPLETED" {{ old('status', $tournament->status) == 'COMPLETED' ? 'selected' : '' }}>COMPLETED</option>
                                <option value="CANCELLED" {{ old('status', $tournament->status) == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="track_number">Track Number</label>
                            <input type="number" 
                                   class="form-control @error('track_number') is-invalid @enderror" 
                                   id="track_number" 
                                   name="track_number" 
                                   value="{{ old('track_number', $tournament->track_number) }}" 
                                   min="1">
                            @error('track_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="max_racer_per_team">Max Racers Per Team</label>
                            <input type="number" 
                                   class="form-control @error('max_racer_per_team') is-invalid @enderror" 
                                   id="max_racer_per_team" 
                                   name="max_racer_per_team" 
                                   value="{{ old('max_racer_per_team', $tournament->max_racer_per_team) }}" 
                                   min="1">
                            @error('max_racer_per_team')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="current_stage">Current Stage</label>
                            <input type="number" 
                                   class="form-control @error('current_stage') is-invalid @enderror" 
                                   id="current_stage" 
                                   name="current_stage" 
                                   value="{{ old('current_stage', $tournament->current_stage) }}" 
                                   min="0">
                            @error('current_stage')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="current_bto_session">Current BTO Session</label>
                            <input type="number" 
                                   class="form-control @error('current_bto_session') is-invalid @enderror" 
                                   id="current_bto_session" 
                                   name="current_bto_session" 
                                   value="{{ old('current_bto_session', $tournament->current_bto_session) }}" 
                                   min="0">
                            @error('current_bto_session')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="champion_number">Champion Number</label>
                            <input type="number" 
                                   class="form-control @error('champion_number') is-invalid @enderror" 
                                   id="champion_number" 
                                   name="champion_number" 
                                   value="{{ old('champion_number', $tournament->champion_number) }}" 
                                   min="1">
                            @error('champion_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bto_number">BTO Number</label>
                            <input type="number" 
                                   class="form-control @error('bto_number') is-invalid @enderror" 
                                   id="bto_number" 
                                   name="bto_number" 
                                   value="{{ old('bto_number', $tournament->bto_number) }}" 
                                   min="1">
                            @error('bto_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bto_session_number">BTO Session Number</label>
                            <input type="number" 
                                   class="form-control @error('bto_session_number') is-invalid @enderror" 
                                   id="bto_session_number" 
                                   name="bto_session_number" 
                                   value="{{ old('bto_session_number', $tournament->bto_session_number) }}" 
                                   min="0">
                            @error('bto_session_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="best_race_number">Best Race Number</label>
                            <input type="number" 
                                   class="form-control @error('best_race_number') is-invalid @enderror" 
                                   id="best_race_number" 
                                   name="best_race_number" 
                                   value="{{ old('best_race_number', $tournament->best_race_number) }}" 
                                   min="1">
                            @error('best_race_number')
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
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="best_race_enabled" 
                                       name="best_race_enabled" 
                                       value="1"
                                       {{ old('best_race_enabled', $tournament->best_race_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="best_race_enabled">
                                    Best Race Enabled
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Tournament
                </button>
                <a href="{{ route('tournaments.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

