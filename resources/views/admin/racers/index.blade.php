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
            @php
                $sortField = request('sort', 'created_at');
                $sortDir   = request('direction', 'desc');
                $nextDir   = $sortDir === 'asc' ? 'desc' : 'asc';
                $sortIcon  = fn($col) => $sortField === $col
                    ? '<i class="fas fa-sort-' . ($sortDir === 'asc' ? 'up' : 'down') . '"></i>'
                    : '<i class="fas fa-sort text-muted"></i>';
                $sortUrl   = fn($col) => route('admin.racers.index', array_merge(
                    request()->except(['sort', 'direction', 'page']),
                    ['sort' => $col, 'direction' => $sortField === $col ? $nextDir : 'asc']
                ));
            @endphp
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('racer_name') }}" class="text-dark">{!! $sortIcon('racer_name') !!} Racer Name</a></th>
                        <th>Team</th>
                        {{-- <th>Image</th> --}}
                        <th><a href="{{ $sortUrl('cards_count') }}" class="text-dark">{!! $sortIcon('cards_count') !!} Total Cards</a></th>
                        <th><a href="{{ $sortUrl('tournament_racer_participants_count') }}" class="text-dark">{!! $sortIcon('tournament_racer_participants_count') !!} Tournaments</a></th>
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
                                        id="delete-form-{{ $racer->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete"
                                                title="Delete Racer"
                                                data-form-id="delete-form-{{ $racer->id }}"
                                                data-item="{{ $racer->racer_name }}">
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
{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Delete Racer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to permanently delete racer:</p>
                <p class="font-weight-bold" id="deleteItemName"></p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle"></i>
                    <small>Cannot delete if racer is participating in tournaments.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
@stop

@section('js')
<script>
    var pendingFormId = null;

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pendingFormId = this.getAttribute('data-form-id');
            document.getElementById('deleteItemName').textContent = this.getAttribute('data-item');
            $('#deleteModal').modal('show');
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (pendingFormId) {
            document.getElementById(pendingFormId).submit();
        }
    });
</script>
@stop