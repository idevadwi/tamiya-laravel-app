@extends('adminlte::page')

@section('title', 'Racers')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Racers</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.racers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Racer
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Racers in Tournament</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('tournament.racers.index') }}" class="form-inline">
                <div class="input-group input-group-sm mr-2" style="width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search racer name..."
                        value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group input-group-sm">
                    <select name="team_id" class="form-control" onchange="this.form.submit()" style="width: 180px;">
                        <option value="">All Teams</option>
                        <option value="unassigned" {{ request('team_id') == 'unassigned' ? 'selected' : '' }}>Unassigned
                        </option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->team_name }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('team_id') || request('search'))
                        <div class="input-group-append">
                            <a href="{{ route('tournament.racers.index') }}" class="btn btn-default" title="Clear filters">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Racer Name</th>
                        <th>Team</th>
                        <th>Cards</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racers as $racer)
                        <tr>
                            <td>
                                @if($racer->image_url)
                                    <img src="{{ $racer->image_url }}" alt="{{ $racer->racer_name }}"
                                        class="img-circle img-size-32">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $racer->racer_name }}</td>
                            <td>
                                @if($racer->team)
                                    <span class="badge badge-info">{{ $racer->team->team_name }}</span>
                                @else
                                    <span class="text-muted">No team</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-warning">{{ $racer->cards_count }}</span>
                            </td>
                            <td>{{ $racer->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tournament.racers.show', $racer->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tournament.racers.edit', $racer->id) }}"
                                        class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tournament.racers.destroy', $racer->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this racer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                No racers found in this tournament.
                                <a href="{{ route('tournament.racers.create') }}">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($racers->hasPages())
        <div class="card-footer">
            {{ $racers->links() }}
        </div>
    @endif
</div>
@stop

@section('js')
@stop