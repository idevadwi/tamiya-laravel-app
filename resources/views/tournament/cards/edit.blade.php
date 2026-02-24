@extends('adminlte::page')

@section('title', 'Edit Card Assignment')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Edit Card Assignment</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.cards.index') }}" class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Back to Cards
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assignment Details</h3>
            </div>
            <form action="{{ route('tournament.cards.update', $card->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Card info (read-only) --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold text-muted small">Card No</label>
                            <p class="mb-0">
                                @if($card->card_no)
                                    <span class="badge badge-secondary" style="font-size:1rem;">{{ $card->card_no }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-8">
                            <label class="font-weight-bold text-muted small">Card Code (RFID)</label>
                            <p class="mb-0"><code>{{ $card->card_code }}</code></p>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="racer_id">Racer <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('racer_id') is-invalid @enderror"
                            id="racer_id" name="racer_id" required>
                            <option value="">-- Select a racer --</option>
                            @foreach($racers as $racer)
                                <option value="{{ $racer->id }}"
                                    {{ old('racer_id', $assignment->racer_id) == $racer->id ? 'selected' : '' }}>
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
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror"
                            id="status" name="status" required>
                            <option value="ACTIVE" {{ old('status', $assignment->status) == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="LOST"   {{ old('status', $assignment->status) == 'LOST'   ? 'selected' : '' }}>LOST</option>
                            <option value="BANNED" {{ old('status', $assignment->status) == 'BANNED' ? 'selected' : '' }}>BANNED</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Assignment
                    </button>
                    <a href="{{ route('tournament.cards.index') }}" class="btn btn-default ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap4' });
    });
</script>
@stop
