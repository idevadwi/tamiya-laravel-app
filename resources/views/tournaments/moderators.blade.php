@extends('adminlte::page')

@section('title', 'Tournament Moderators')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Moderators: {{ $tournament->tournament_name }}</h1>
        <a href="{{ route('tournaments.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Tournaments
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign New Moderator</h3>
                </div>
                <form action="{{ route('tournaments.moderators.assign', $tournament->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="user_id">Select Moderator</label>
                            <select class="form-control @error('user_id') is-invalid @enderror" 
                                    id="user_id" 
                                    name="user_id" 
                                    required>
                                <option value="">-- Select a Moderator --</option>
                                @foreach($allModerators as $moderator)
                                    @if(!$tournament->moderators->contains($moderator->id))
                                        <option value="{{ $moderator->id }}">
                                            {{ $moderator->email }} ({{ $moderator->phone }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Assign Moderator
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assigned Moderators</h3>
                </div>
                <div class="card-body">
                    @if($tournament->moderators->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tournament->moderators as $moderator)
                                        <tr>
                                            <td>{{ $moderator->email }}</td>
                                            <td>{{ $moderator->phone }}</td>
                                            <td>
                                                <form action="{{ route('tournaments.moderators.remove', [$tournament->id, $moderator->id]) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to remove this moderator?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No moderators assigned to this tournament.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
