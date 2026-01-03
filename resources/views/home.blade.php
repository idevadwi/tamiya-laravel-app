@extends('adminlte::page')

@section('title', 'Select Tournament')

@section('content_header')
    <h1>Select Tournament</h1>
@stop

@section('content')
    @php
        $isAdmin = auth()->check() && auth()->user()->hasRole('ADMINISTRATOR');
        $isModerator = auth()->check() && auth()->user()->hasRole('MODERATOR');
    @endphp

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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(hasActiveTournament())
        <div class="alert alert-info">
            <strong>Active Tournament:</strong> {{ getActiveTournament()->tournament_name }}
            <a href="{{ route('home') }}" class="btn btn-sm btn-primary float-right">
                Change Tournament
            </a>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Available Tournaments</h3>
                    @if($isAdmin)
                        <div class="card-tools">
                            <a href="{{ route('tournaments.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Create New Tournament
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($isModerator && ! $isAdmin)
                        <div class="alert alert-info">
                            Showing tournaments assigned to you.
                        </div>
                    @endif
                    @if($tournaments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tournament Name</th>
                                        <th>Vendor Name</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tournaments as $tournament)
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
                                            <td>{{ $tournament->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <form action="{{ route('tournaments.select') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check"></i> Select
                                                    </button>
                                                </form>
                                                @if($isAdmin)
                                                    <a href="{{ route('tournaments.show', $tournament->id) }}" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">No tournaments found.</p>
                            @if($isAdmin)
                                <a href="{{ route('tournaments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Your First Tournament
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                @if($tournaments->hasPages())
                    <div class="card-footer">
                        {{ $tournaments->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
