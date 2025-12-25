@extends('adminlte::page')

@section('title', 'Edit Team')

@section('content_header')
<h1>Edit Team (Master Data)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Edit Team Details</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('admin.teams.update', $team->id) }}" method="POST">
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
                        <label for="team_name">Team Name</label>
                        <input type="text" class="form-control @error('team_name') is-invalid @enderror" id="team_name"
                            name="team_name" value="{{ old('team_name', $team->team_name) }}" required>
                        @error('team_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Created At</label>
                        <p class="form-control-static">{{ $team->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Team</button>
                    <a href="{{ route('admin.teams.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop