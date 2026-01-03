@extends('adminlte::page')

@section('title', 'Master Data: Cards')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Master Data: Cards</h1>
            <p class="text-muted mb-0">Manage all cards globally.</p>
        </div>
        <div>
            <a href="{{ route('admin.cards.bulk-create') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-file-csv"></i> Bulk Create
            </a>
            <a href="{{ route('admin.cards.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Card
            </a>
        </div>
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
            <h3 class="card-title">All Cards</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('admin.cards.index') }}" class="form-inline">
                    <div class="form-group mr-2">
                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="LOST" {{ request('status') == 'LOST' ? 'selected' : '' }}>Lost</option>
                            <option value="BANNED" {{ request('status') == 'BANNED' ? 'selected' : '' }}>Banned</option>
                        </select>
                    </div>
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search card code..." 
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('status'))
                                <a href="{{ route('admin.cards.index') }}" class="btn btn-default" title="Clear">
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
                            <th>Card Code</th>
                            <th>Racer</th>
                            <th>Team</th>
                            <th>Status</th>
                            <th>Coupons</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td>{{ $card->card_code }}</td>
                                <td>
                                    @if($card->racer)
                                        <a href="{{ route('admin.racers.show', $card->racer_id) }}">
                                            {{ $card->racer->racer_name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($card->racer && $card->racer->team)
                                        <a href="{{ route('admin.teams.show', $card->racer->team_id) }}">
                                            {{ $card->racer->team->team_name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $card->status == 'ACTIVE' ? 'success' : ($card->status == 'BANNED' ? 'danger' : 'warning') }}">
                                        {{ $card->status }}
                                    </span>
                                </td>
                                <td>{{ $card->coupon }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.cards.edit', $card->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.cards.destroy', $card->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('DELETE CARD PERMANENTLY?\n\nThis action cannot be undone.\n\nAre you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Card">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No cards found. 
                                    <a href="{{ route('admin.cards.create') }}">Create one now</a>.
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
@stop

@section('css')
@stop

@section('js')
@stop
