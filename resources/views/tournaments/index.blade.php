@extends('adminlte::page')

@section('title', 'Tournaments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tournaments</h1>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Tournament
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Tournaments</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tournament Name</th>
                            <th>Vendor Name</th>
                            <th>Status</th>
                            <th>Track Number</th>
                            <th>Max Racers/Team</th>
                            <th>Champions</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournaments as $tournament)
                            <tr>
                                <td>{{ $tournament->tournament_name }}</td>
                                <td>{{ $tournament->vendor_name ?? 'N/A' }}</td>
                                <td>
                                    @if($tournament->status === 'ACTIVE')
                                        <span class="badge badge-success">{{ $tournament->status }}</span>
                                    @elseif($tournament->status === 'COMPLETED')
                                        <span class="badge badge-info">{{ $tournament->status }}</span>
                                    @elseif($tournament->status === 'CANCELLED')
                                        <span class="badge badge-danger">{{ $tournament->status }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $tournament->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $tournament->track_number }}</td>
                                <td>{{ $tournament->max_racer_per_team }}</td>
                                <td>{{ $tournament->champion_number }}</td>
                                <td>{{ $tournament->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tournaments.show', $tournament->id) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tournaments.edit', $tournament->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('tournaments.settings', $tournament->id) }}" 
                                           class="btn btn-sm btn-secondary" title="Settings">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        @if(auth()->user()->roles()->where('role_name', 'ADMINISTRATOR')->exists())
                                        <a href="{{ route('tournaments.moderators', $tournament->id) }}" 
                                           class="btn btn-sm btn-info" title="Manage Moderators">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('tournaments.destroy', $tournament->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this tournament?');">
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
                                <td colspan="8" class="text-center">No tournaments found. <a href="{{ route('tournaments.create') }}">Create one now</a>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tournaments->hasPages())
            <div class="card-footer">
                {{ $tournaments->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

