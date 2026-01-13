@extends('adminlte::page')

@section('title', 'Add New Race')

@php
    $assignedStage = $tournament->current_stage + 1;
@endphp

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Add New Race</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        <p class="text-muted mb-0">Current Stage: <strong>{{ $tournament->current_stage }}</strong></p>
        <p class="text-muted mb-0">Stage Assigned: <strong>{{ $assignedStage }}</strong></p>
        <p class="text-muted mb-0">Track Number: <strong>{{ $tournament->track_number }}</strong>
            ({{ $tournament->track_number * 3 }} lanes available)</p>
    </div>
    <a href="{{ route('tournament.races.index', request()->only(['stage', 'track', 'team_id'])) }}"
        class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Back to Races
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Race Information</h3>
    </div>
    <form action="{{ route('tournament.races.store') }}" method="POST">
        @csrf
        <input type="hidden" name="stage" value="{{ old('stage', $assignedStage) }}">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> When you add a new race, it will be automatically assigned to:
                <ul class="mb-0 mt-2">
                    <li><strong>Stage:</strong> {{ $assignedStage }}</li>
                    <li><strong>Track & Lane:</strong> Automatically calculated based on existing races in the stage
                    </li>
                    <li><strong>Racer & Team:</strong> Automatically retrieved from the selected card</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="card_id">Card <span class="text-danger">*</span></label>
                <select class="form-control @error('card_id') is-invalid @enderror" id="card_id" name="card_id"
                    required>
                    <option value="">Select a card...</option>
                    @foreach($cards as $card)
                        <option value="{{ $card->id }}" {{ old('card_id') == $card->id ? 'selected' : '' }}>
                            {{ $card->card_code }}
                            @if($card->racer)
                                - {{ $card->racer->racer_name }}
                                @if($card->racer->team)
                                    ({{ $card->racer->team->team_name }})
                                @endif
                            @else
                                - Unassigned
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('card_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <small class="form-text text-muted">
                    Select a card from the active tournament. Only ACTIVE cards are shown.
                </small>
            </div>

            @if($cards->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No active cards available!</strong> Please create cards first or activate existing cards.
                </div>
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary" {{ $cards->isEmpty() ? 'disabled' : '' }}>
                <i class="fas fa-save"></i> Create Race
            </button>
            <a href="{{ route('tournament.races.index', request()->only(['stage', 'track', 'team_id'])) }}"
                class="btn btn-default">
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