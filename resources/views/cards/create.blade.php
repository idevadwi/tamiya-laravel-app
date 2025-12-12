@extends('adminlte::page')

@section('title', 'Create Card')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Create New Card</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <a href="{{ route('cards.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Cards
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Card Information</h3>
        </div>
        <form action="{{ route('cards.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="card_code">Card Code <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('card_code') is-invalid @enderror" 
                                   id="card_code" 
                                   name="card_code" 
                                   value="{{ old('card_code') }}" 
                                   placeholder="Enter card code"
                                   required>
                            @error('card_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">
                                Unique identifier for the card
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="racer_id">Racer</label>
                            <select class="form-control @error('racer_id') is-invalid @enderror" 
                                    id="racer_id" 
                                    name="racer_id">
                                <option value="">Unassigned</option>
                                @foreach($racers as $racer)
                                    <option value="{{ $racer->id }}" 
                                            {{ old('racer_id', $selectedRacerId) == $racer->id ? 'selected' : '' }}>
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
                                Select a racer from the active tournament (optional)
                            </small>
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
                                <option value="ACTIVE" {{ old('status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                <option value="LOST" {{ old('status') == 'LOST' ? 'selected' : '' }}>LOST</option>
                                <option value="BANNED" {{ old('status') == 'BANNED' ? 'selected' : '' }}>BANNED</option>
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
                            <label for="coupon">Coupons</label>
                            <input type="number" 
                                   class="form-control @error('coupon') is-invalid @enderror" 
                                   id="coupon" 
                                   name="coupon" 
                                   value="{{ old('coupon', 0) }}" 
                                   min="0">
                            @error('coupon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Card
                </button>
                <a href="{{ route('cards.index') }}" class="btn btn-default">
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

