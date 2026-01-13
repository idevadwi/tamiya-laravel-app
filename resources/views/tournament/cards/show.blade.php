@extends('adminlte::page')

@section('title', 'Card Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Card Details</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <div>
        <a href="{{ route('tournament.cards.edit', $card->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('tournament.cards.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Cards
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Card Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Card Code</th>
                        <td><code>{{ $card->card_code }}</code></td>
                    </tr>
                    <tr>
                        <th>Racer</th>
                        <td>
                            @if($card->racer)
                                <a href="{{ route('tournament.racers.show', $card->racer->id) }}">
                                    {{ $card->racer->racer_name }}
                                </a>
                                @if($card->racer->team)
                                    <span class="badge badge-info ml-2">{{ $card->racer->team->team_name }}</span>
                                @endif
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
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
                    </tr>
                    <tr>
                        <th>Coupons</th>
                        <td>{{ $card->coupon }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $card->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $card->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('tournament.cards.edit', $card->id) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit Card
                </a>
                <form action="{{ route('tournament.cards.destroy', $card->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this card?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-trash"></i> Delete Card
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related Information</h3>
            </div>
            <div class="card-body">
                @if($card->racer)
                    <h5>Racer Details</h5>
                    <p>
                        <strong>Name:</strong>
                        <a href="{{ route('tournament.racers.show', $card->racer->id) }}">
                            {{ $card->racer->racer_name }}
                        </a>
                    </p>
                    @if($card->racer->team)
                        <p>
                            <strong>Team:</strong>
                            <a href="{{ route('tournament.teams.show', $card->racer->team->id) }}">
                                {{ $card->racer->team->team_name }}
                            </a>
                        </p>
                    @endif
                    @if($card->racer->image_url)
                        <p>
                            <img src="{{ $card->racer->image_url }}" alt="{{ $card->racer->racer_name }}" class="img-thumbnail"
                                style="max-width: 150px;">
                        </p>
                    @endif
                @else
                    <p class="text-muted">This card is not assigned to any racer.</p>
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