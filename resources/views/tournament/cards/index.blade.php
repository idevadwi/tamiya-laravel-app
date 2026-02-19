@extends('adminlte::page')

@section('title', 'Cards')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Cards</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.cards.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Card
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
                    <input type="text" name="search" class="form-control" placeholder="Search card code..."
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

        {{-- Bulk action toolbar --}}
        <div id="bulkActionBar" class="d-none mb-2">
            <div class="d-flex align-items-center bg-light border rounded px-3 py-2">
                <span class="mr-3 font-weight-bold">
                    <span id="selectedCount">0</span> selected
                </span>
                <button type="button" class="btn btn-sm btn-danger" id="btnBulkDelete">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-secondary ml-2" id="btnClearSelection">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        {{-- <th style="width: 36px;">
                            <input type="checkbox" id="checkAll" title="Select all">
                        </th> --}}
                        <th>
                            <a href="{{ $sortUrl('card_no') }}" class="text-dark text-decoration-none">
                                Card No{!! $sortIcon('card_no') !!}
                            </a>
                        </th>
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
                        {{-- <th>Coupons</th> --}}
                        {{-- <th>Created At</th> --}}
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr>
                            {{-- <td>
                                <input type="checkbox" class="card-checkbox" value="{{ $card->id }}">
                            </td> --}}
                            <td>{{ $card->card_no }}</td>
                            <td>
                                @if($card->racer)
                                    {{ $card->racer->racer_name }}
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($card->racer && $card->racer->team)
                                    <span class="badge badge-info">{{ $card->racer->team->team_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($card->status === 'ACTIVE')
                                    <span class="badge badge-success">{{ $card->status }}</span>
                                @elseif($card->status === 'LOST')
                                    <span class="badge badge-warning">{{ $card->status }}</span>
                                @elseif($card->status === 'BANNED')
                                    <span class="badge badge-danger">{{ $card->status }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $card->status }}</span>
                                @endif
                            </td>
                            {{-- <td>{{ $card->coupon }}</td> --}}
                            {{-- <td>{{ $card->created_at->format('Y-m-d H:i') }}</td> --}}
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tournament.cards.show', $card->id) }}" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('tournament.cards.edit', $card->id) }}" class="btn btn-sm btn-warning ml-2"
                                        title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('tournament.cards.destroy', $card->id) }}" method="POST"
                                        class="d-inline ml-2"
                                        id="delete-form-{{ $card->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-sm btn-danger btn-delete"
                                                title="Delete"
                                                data-form-id="delete-form-{{ $card->id }}"
                                                data-item="{{ $card->card_no }}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                No cards found in this tournament.
                                <a href="{{ route('tournament.cards.create') }}">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cards->hasPages())
        <div class="card-footer">
            {{ $cards->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

{{-- Hidden bulk-delete form --}}
<form id="bulkDeleteForm" method="POST" action="{{ route('tournament.cards.bulk-destroy') }}" class="d-none">
    @csrf
    <div id="bulkDeleteInputs"></div>
</form>

{{-- Bulk Delete Confirmation Modal --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Delete Selected Cards
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to permanently delete <strong id="bulkDeleteCount"></strong> card(s)?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle"></i>
                    <small>This action cannot be undone.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                    <i class="fas fa-trash"></i> Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Delete Card
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to permanently delete card:</p>
                <p class="font-weight-bold" id="deleteItemName"></p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle"></i>
                    <small>This action cannot be undone.</small>
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
    // ── Single delete ──────────────────────────────────────────────
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

    // ── Bulk delete ────────────────────────────────────────────────
    var checkAll     = document.getElementById('checkAll');
    var checkboxes   = document.querySelectorAll('.card-checkbox');
    var bulkBar      = document.getElementById('bulkActionBar');
    var selectedCount= document.getElementById('selectedCount');

    function getSelectedIds() {
        return Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
    }

    function updateBulkBar() {
        var ids = getSelectedIds();
        selectedCount.textContent = ids.length;
        bulkBar.classList.toggle('d-none', ids.length === 0);
        checkAll.indeterminate = ids.length > 0 && ids.length < checkboxes.length;
        checkAll.checked = ids.length === checkboxes.length && checkboxes.length > 0;
    }

    checkAll.addEventListener('change', function () {
        checkboxes.forEach(c => c.checked = this.checked);
        updateBulkBar();
    });

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', updateBulkBar);
    });

    document.getElementById('btnClearSelection').addEventListener('click', function () {
        checkboxes.forEach(c => c.checked = false);
        checkAll.checked = false;
        updateBulkBar();
    });

    document.getElementById('btnBulkDelete').addEventListener('click', function () {
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        document.getElementById('bulkDeleteCount').textContent = ids.length;
        $('#bulkDeleteModal').modal('show');
    });

    document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function () {
        var ids = getSelectedIds();
        var container = document.getElementById('bulkDeleteInputs');
        container.innerHTML = '';
        ids.forEach(function (id) {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'card_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('bulkDeleteForm').submit();
    });
</script>
@stop