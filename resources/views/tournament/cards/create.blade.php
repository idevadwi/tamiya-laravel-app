@extends('adminlte::page')

@section('title', 'Create Card')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Create New Card</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.cards.index') }}" class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Back to Cards
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card Information</h3>
    </div>
    <form action="{{ route('tournament.cards.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="card_id">Card No <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('card_id') is-invalid @enderror" id="card_id"
                            name="card_id" required>
                            <option value="">-- Select a card --</option>
                            @foreach($availableCards as $card)
                                <option value="{{ $card->id }}" {{ old('card_id') == $card->id ? 'selected' : '' }}>
                                    #{{ $card->card_no }}
                                </option>
                            @endforeach
                        </select>
                        @error('card_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Only unassigned cards are shown
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="racer_id">Racer <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('racer_id') is-invalid @enderror" id="racer_id"
                            name="racer_id" required>
                            <option value="">-- Select a racer --</option>
                            @foreach($racers as $racer)
                                <option value="{{ $racer->id }}" {{ old('racer_id', $selectedRacerId) == $racer->id ? 'selected' : '' }}>
                                    {{ $racer->racer_name }}
                                    @if($racer->team)
                                        ({{ $racer->team->team_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('racer_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Select a racer from the active tournament
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Card
            </button>
            <a href="{{ route('tournament.cards.index') }}" class="btn btn-default">
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
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@stop