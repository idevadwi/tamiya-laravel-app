@extends('adminlte::page')

@section('title', 'Master Data: Racers')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Master Data: Racers</h1>
        <p class="text-muted mb-0">Manage all racers globally.</p>
    </div>
    <a href="{{ route('admin.racers.create') }}" class="btn btn-primary">
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
        <h3 class="card-title">All Racers</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('admin.racers.index') }}" class="form-inline">
                <div class="form-group mr-2">
                    <select name="team_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Teams</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->team_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search racer name..."
                        value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search') || request('team_id'))
                            <a href="{{ route('admin.racers.index') }}" class="btn btn-default" title="Clear">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Racer Name</th>
                        <th>Team</th>
                        {{-- <th>Image</th> --}}
                        <th>Total Cards</th>
                        <th>Tournaments</th>
                        {{-- <th>Created At</th> --}}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racers as $racer)
                        <tr>
                            <td>{{ $racer->racer_name }}</td>
                            <td>
                                @if($racer->team)
                                    <a href="{{ route('admin.teams.show', $racer->team_id) }}">
                                        {{ $racer->team->team_name }}
                                    </a>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            {{-- <td>
                                @if($racer->image)
                                    <img src="{{ Storage::url($racer->image) }}" alt="{{ $racer->racer_name }}"
                                        style="height: 30px;">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td> --}}
                            <td>
                                <span class="badge badge-info">{{ $racer->cards_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $racer->tournament_racer_participants_count }}</span>
                            </td>
                            {{-- <td>{{ $racer->created_at->format('Y-m-d') }}</td> --}}
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.racers.show', $racer->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i> Details
                                    </a>
                                    <a href="{{ route('admin.racers.edit', $racer->id) }}" class="btn btn-sm btn-warning ml-2"
                                        title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.racers.destroy', $racer->id) }}" method="POST"
                                        class="d-inline ml-2"
                                        onsubmit="return confirm('DELETE RACER PERMANENTLY?\n\nCannot delete if racer is participating in tournaments.\n\nAre you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Racer">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                No racers found.
                                <a href="{{ route('admin.racers.create') }}">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($racers->hasPages())
        <div class="card-footer">
            {{ $racers->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@stop

@section('css')
@stop

@section('js')
@stop