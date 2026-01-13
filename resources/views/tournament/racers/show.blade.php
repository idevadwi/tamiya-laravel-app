@extends('adminlte::page')

@section('title', 'Racer Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Racer Details</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <div>
        <a href="{{ route('tournament.racers.edit', $racer->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('tournament.racers.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Racers
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Racer Information</h3>
            </div>
            <div class="card-body text-center">
                @if($racer->image_url)
                    <img src="{{ $racer->image_url }}" alt="{{ $racer->racer_name }}" class="img-circle img-size-128 mb-3">
                @else
                    <div class="img-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3"
                        style="width: 128px; height: 128px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                @endif
                <h4>{{ $racer->racer_name }}</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Team</th>
                        <td>
                            @if($racer->team)
                                <span class="badge badge-info">{{ $racer->team->team_name }}</span>
                            @else
                                <span class="text-muted">No team</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Number of Cards</th>
                        <td>
                            <span class="badge badge-warning">{{ $cards->count() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $racer->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $racer->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('tournament.cards.create', ['racer_id' => $racer->id]) }}"
                    class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-credit-card"></i> Add Card
                </a>
                <a href="{{ route('tournament.racers.edit', $racer->id) }}" class="btn btn-secondary btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit Racer
                </a>
                <form action="{{ route('tournament.racers.destroy', $racer->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this racer?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-trash"></i> Delete Racer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cards</h3>
                <div class="card-tools">
                    <a href="{{ route('tournament.cards.create', ['racer_id' => $racer->id]) }}"
                        class="btn btn-sm btn-warning">
                        <i class="fas fa-plus"></i> Add Card
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($cards->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Card Code</th>
                                    <th>Status</th>
                                    <th>Coupons</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cards as $card)
                                    <tr>
                                        <td><code>{{ $card->card_code }}</code></td>
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
                                            <a href="{{ route('tournament.cards.show', $card->id) }}"
                                                class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">No cards assigned to this racer yet.</p>
                        <a href="{{ route('tournament.cards.create', ['racer_id' => $racer->id]) }}"
                            class="btn btn-warning">
                            <i class="fas fa-credit-card"></i> Add First Card
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
@stop