@extends('adminlte::page')

@section('title', 'Cards')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Cards</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <a href="{{ route('cards.create') }}" class="btn btn-primary">
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
                <form method="GET" action="{{ route('cards.index') }}" class="form-inline" id="cardFilterForm">
                    <div class="input-group input-group-sm mr-2" style="width: 200px;">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search card code..." 
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
                                <a href="{{ route('cards.index') }}" class="btn btn-default" title="Clear all filters">
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
                            <th>Card Code</th>
                            <th>Racer</th>
                            <th>Team</th>
                            <th>Status</th>
                            <th>Coupons</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td><code>{{ $card->card_code }}</code></td>
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
                                <td>{{ $card->coupon }}</td>
                                <td>{{ $card->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('cards.show', $card->id) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cards.edit', $card->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('cards.destroy', $card->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this card?');">
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
                                <td colspan="7" class="text-center">
                                    No cards found in this tournament. 
                                    <a href="{{ route('cards.create') }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($cards->hasPages())
            <div class="card-footer">
                {{ $cards->links() }}
            </div>
        @endif
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

