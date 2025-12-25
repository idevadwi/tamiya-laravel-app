@extends('adminlte::page')

@section('title', 'Edit Team')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Edit Team</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Back to Teams
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Team Information</h3>
    </div>
    <form action="{{ route('tournament.teams.update', $team->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="team_name">Team Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('team_name') is-invalid @enderror" id="team_name"
                    name="team_name" value="{{ old('team_name', $team->team_name) }}" placeholder="Enter team name"
                    required>
                @error('team_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Team
            </button>
            <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
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