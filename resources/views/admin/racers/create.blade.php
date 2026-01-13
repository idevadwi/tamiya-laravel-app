@extends('adminlte::page')

@section('title', 'Create Racer')

@section('content_header')
<h1>Create New Racer (Master Data)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Racer Details</h3>
            </div>
            <form action="{{ route('admin.racers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="racer_name">Racer Name</label>
                        <input type="text" class="form-control @error('racer_name') is-invalid @enderror"
                            id="racer_name" name="racer_name" placeholder="Enter racer name"
                            value="{{ old('racer_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="team_id">Team (Global Assignment)</label>
                        <select class="form-control select2 @error('team_id') is-invalid @enderror" id="team_id"
                            name="team_id" required>
                            <option value="">Select a team...</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->team_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Assigning a team here updates their global team. This might
                            affect other tournaments if the racer is moved.</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Racer Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="card_code">Initial Card Code (Optional)</label>
                        <input type="text" class="form-control @error('card_code') is-invalid @enderror" id="card_code"
                            name="card_code" placeholder="Scan or enter card code" value="{{ old('card_code') }}">
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create Racer</button>
                    <a href="{{ route('admin.racers.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        bsCustomFileInput.init();
    });
</script>
@stop