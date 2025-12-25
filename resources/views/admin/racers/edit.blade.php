@extends('adminlte::page')

@section('title', 'Edit Racer')

@section('content_header')
    <h1>Edit Racer (Master Data)</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Edit Racer Details</h3>
                </div>
                <form action="{{ route('admin.racers.update', $racer->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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
                            <input type="text" 
                                   class="form-control @error('racer_name') is-invalid @enderror" 
                                   id="racer_name" 
                                   name="racer_name" 
                                   value="{{ old('racer_name', $racer->racer_name) }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="team_id">Team (Global Assignment)</label>
                            <select class="form-control select2 @error('team_id') is-invalid @enderror" 
                                    id="team_id" 
                                    name="team_id" 
                                    required>
                                <option value="">Select a team...</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id', $racer->team_id) == $team->id ? 'selected' : '' }}>
                                        {{ $team->team_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image">Racer Image</label>
                            @if($racer->image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($racer->image) }}" alt="Current Image" style="height: 100px;">
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image">
                                <label class="custom-file-label" for="image">Choose file to replace</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Racer</button>
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
