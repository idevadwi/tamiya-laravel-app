@extends('adminlte::page')

@section('title', 'Master Data: Teams')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Master Data: Teams</h1>
        <p class="text-muted mb-0">Manage all teams globally.</p>
    </div>
    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Team
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
        <h3 class="card-title">All Teams</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('admin.teams.index') }}" class="form-inline">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Search team name..."
                        value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.teams.index') }}" class="btn btn-default" title="Clear">
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
                        <th>Team Name</th>
                        <th>Total Racers</th>
                        <th>Tournaments Participated</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teams as $team)
                        <tr>
                            <td>{{ $team->team_name }}</td>
                            <td>
                                <span class="badge badge-info">{{ $team->racers_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $team->tournament_participants_count }}</span>
                            </td>
                            <td>{{ $team->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.teams.show', $team->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.teams.destroy', $team->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('DELETE TEAM PERMANENTLY?\n\nCannot delete if the team is participating in tournaments.\n\nAre you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Team">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No teams found.
                                <a href="{{ route('admin.teams.create') }}">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($teams->hasPages())
        <div class="card-footer">
            {{ $teams->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@stop

@section('css')
@stop

@section('js')
@stop