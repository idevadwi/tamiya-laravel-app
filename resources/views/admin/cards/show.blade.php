@extends('adminlte::page')

@section('title', 'Card Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Card: {{ $card->card_code }}</h1>
    <div>
        <a href="{{ route('admin.cards.edit', $card->id) }}" class="btn btn-warning mr-2">
            <i class="fas fa-edit"></i> Edit Card
        </a>
        <a href="{{ route('admin.cards.index') }}" class="btn btn-default">Back to List</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $card->card_code }}</h3>
                <p class="text-muted text-center">
                    <span
                        class="badge badge-{{ $card->status == 'ACTIVE' ? 'success' : ($card->status == 'BANNED' ? 'danger' : 'warning') }}">
                        {{ $card->status }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Assigned Racer</b>
                        <a class="float-right">
                            @if($card->racer)
                                <a
                                    href="{{ route('admin.racers.show', $card->racer_id) }}">{{ $card->racer->racer_name }}</a>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Current Coupons</b> <a class="float-right">{{ number_format($card->coupon) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Created At</b> <a class="float-right">{{ $card->created_at->format('Y-m-d H:i') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop