@extends('adminlte::page')

@section('title', 'Edit Racer')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Edit Racer</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <a href="{{ route('racers.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Racers
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Racer Information</h3>
        </div>
        <form action="{{ route('racers.update', $racer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="racer_name">Racer Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('racer_name') is-invalid @enderror" 
                                   id="racer_name" 
                                   name="racer_name" 
                                   value="{{ old('racer_name', $racer->racer_name) }}" 
                                   placeholder="Enter racer name"
                                   required>
                            @error('racer_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="team_id">Team</label>
                            <select class="form-control @error('team_id') is-invalid @enderror" 
                                    id="team_id" 
                                    name="team_id">
                                <option value="">No Team</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" 
                                            {{ old('team_id', $racer->team_id) == $team->id ? 'selected' : '' }}>
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
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image">Racer Image</label>
                            @if($racer->image_url)
                                <div class="mb-2">
                                    <img src="{{ $racer->image_url }}" 
                                         alt="{{ $racer->racer_name }}" 
                                         class="img-thumbnail" 
                                         style="max-width: 200px; max-height: 200px;">
                                    <p class="text-muted small">Current image</p>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('image') is-invalid @enderror" 
                                       id="image" 
                                       name="image"
                                       accept="image/*">
                                <label class="custom-file-label" for="image">Choose new file (optional)</label>
                                @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Leave empty to keep current image. Allowed types: jpeg, png, jpg, gif, svg. Max size: 2MB
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="image-preview" class="mt-3" style="display: none;">
                            <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                            <p class="text-muted small">New image preview</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Racer
                </button>
                <a href="{{ route('racers.index') }}" class="btn btn-default">
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
    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            document.getElementById('image-preview').style.display = 'none';
        }
    });

    // Update custom file input label
    document.getElementById('image').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Choose file';
        document.querySelector('.custom-file-label').textContent = fileName;
    });
</script>
@stop

