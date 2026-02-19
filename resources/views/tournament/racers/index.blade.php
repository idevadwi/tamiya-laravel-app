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
        @php
            $sortIcon = function ($col) use ($sort, $direction) {
                if ($sort === $col) {
                    return $direction === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
                }
                return ' <i class="fas fa-sort text-muted"></i>';
            };
            $sortDir = fn ($col) => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            $sortUrl = fn ($col) => route('tournament.racers.index', array_merge(
                request()->except(['sort', 'direction', 'page']),
                ['sort' => $col, 'direction' => $sortDir($col)]
            ));
        @endphp
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ $sortUrl('racer_name') }}" class="text-dark text-decoration-none">
                                Racer Name{!! $sortIcon('racer_name') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('team_name') }}" class="text-dark text-decoration-none">
                                Team{!! $sortIcon('team_name') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('cards_count') }}" class="text-dark text-decoration-none">
                                Cards{!! $sortIcon('cards_count') !!}
                            </a>
                        </th>
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
                                    <span class="badge badge-info">{{ $racer->team->team_name }}</span>
                                @else
                                    <span class="text-muted">No team</span>
                                @endif
                            </td>
                            <td>
                                @forelse($racer->cards as $card)
                                    <span class="badge badge-warning">{{ $card->card_no }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            {{-- <td>{{ $racer->created_at->format('Y-m-d H:i') }}</td> --}}
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tournament.racers.show', $racer->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('tournament.racers.edit', $racer->id) }}"
                                        class="btn btn-sm btn-warning ml-2" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('tournament.racers.destroy', $racer->id) }}" method="POST"
                                        class="d-inline ml-2"
                                        id="delete-form-{{ $racer->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete"
                                                title="Delete"
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
                            <td colspan="5" class="text-center">
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
<style>
    thead th a { white-space: nowrap; }
    thead th a:hover { text-decoration: none; opacity: 0.8; }
</style>
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