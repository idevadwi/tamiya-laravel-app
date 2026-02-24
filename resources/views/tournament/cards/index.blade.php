@extends('adminlte::page')

@section('title', 'Cards')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Cards</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.cards.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Assign Card
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
        <h3 class="card-title">Cards in Tournament</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('tournament.cards.index') }}" class="form-inline" id="cardFilterForm">
                <div class="input-group input-group-sm mr-2" style="width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search card no / code..."
                        value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group input-group-sm mr-2">
                    <select name="team_id" class="form-control" onchange="this.form.submit()" style="width: 150px;">
                        <option value="">All Teams</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->team_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group input-group-sm mr-2">
                    <select name="racer_id" class="form-control" onchange="this.form.submit()" style="width: 200px;">
                        <option value="">All Racers</option>
                        @foreach($racers as $racer)
                            <option value="{{ $racer->id }}" {{ request('racer_id') == $racer->id ? 'selected' : '' }}>
                                {{ $racer->racer_name }} ({{ $racer->team->team_name ?? 'No Team' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group input-group-sm">
                    <select name="status" class="form-control" onchange="this.form.submit()" style="width: 120px;">
                        <option value="">All Status</option>
                        <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="LOST" {{ request('status') == 'LOST' ? 'selected' : '' }}>LOST</option>
                        <option value="BANNED" {{ request('status') == 'BANNED' ? 'selected' : '' }}>BANNED</option>
                    </select>
                    @if(request('team_id') || request('racer_id') || request('status') || request('search'))
                        <div class="input-group-append">
                            <a href="{{ route('tournament.cards.index') }}" class="btn btn-default"
                                title="Clear all filters">
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
            $sortUrl = fn ($col) => route('tournament.cards.index', array_merge(
                request()->except(['sort', 'direction', 'page']),
                ['sort' => $col, 'direction' => $sortDir($col)]
            ));
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ $sortUrl('card_no') }}" class="text-dark text-decoration-none">
                                Card No{!! $sortIcon('card_no') !!}
                            </a>
                        </th>
                        <th>Card Code</th>
                        <th>
                            <a href="{{ $sortUrl('racer_name') }}" class="text-dark text-decoration-none">
                                Racer{!! $sortIcon('racer_name') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('team_name') }}" class="text-dark text-decoration-none">
                                Team{!! $sortIcon('team_name') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('status') }}" class="text-dark text-decoration-none">
                                Status{!! $sortIcon('status') !!}
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td>
                                @if($assignment->card && $assignment->card->card_no)
                                    <span class="badge badge-secondary">{{ $assignment->card->card_no }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->card)
                                    <code>{{ $assignment->card->card_code }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->racer)
                                    {{ $assignment->racer->racer_name }}
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->racer && $assignment->racer->team)
                                    <span class="badge badge-info">{{ $assignment->racer->team->team_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->status === 'ACTIVE')
                                    <span class="badge badge-success">{{ $assignment->status }}</span>
                                @elseif($assignment->status === 'LOST')
                                    <span class="badge badge-warning">{{ $assignment->status }}</span>
                                @elseif($assignment->status === 'BANNED')
                                    <span class="badge badge-danger">{{ $assignment->status }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $assignment->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tournament.cards.show', $assignment->card_id) }}"
                                        class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('tournament.cards.edit', $assignment->card_id) }}"
                                        class="btn btn-sm btn-warning ml-2" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('tournament.cards.destroy', $assignment->card_id) }}"
                                        method="POST"
                                        class="d-inline ml-2"
                                        id="delete-form-{{ $assignment->card_id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete"
                                                title="Remove assignment"
                                                data-form-id="delete-form-{{ $assignment->card_id }}"
                                                data-item="{{ $assignment->card->card_no ?? $assignment->card->card_code }}">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                No cards assigned in this tournament.
                                <a href="{{ route('tournament.cards.create') }}">Assign one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($assignments->hasPages())
        <div class="card-footer">
            {{ $assignments->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Remove Card Assignment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Remove the assignment for card: <strong id="deleteItemName"></strong>?</p>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle"></i>
                    <small>The card itself will not be deleted — it can be reassigned in another tournament.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Remove Assignment
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
