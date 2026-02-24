@extends('adminlte::page')

@section('title', 'Card Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Card: {{ $card->card_no ?? $card->card_code }}</h1>
    <div>
        <a href="{{ route('admin.cards.edit', $card->id) }}" class="btn btn-warning mr-2">
            <i class="fas fa-edit"></i> Edit Card
        </a>
        <a href="{{ route('admin.cards.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">
                    @if($card->card_no)
                        <span class="badge badge-secondary" style="font-size:1.2rem;">{{ $card->card_no }}</span>
                    @else
                        <span class="text-muted">No card no.</span>
                    @endif
                </h3>
                <p class="text-muted text-center">
                    <span class="badge badge-{{ $card->status == 'ACTIVE' ? 'success' : ($card->status == 'BANNED' ? 'danger' : 'warning') }}">
                        {{ $card->status }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Card Code</b>
                        <span class="float-right"><code>{{ $card->card_code }}</code></span>
                    </li>
                    <li class="list-group-item">
                        <b>Current Coupons</b>
                        <span class="float-right">{{ number_format($card->coupon) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Created At</b>
                        <span class="float-right">{{ $card->created_at->format('Y-m-d H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i> Tournament History
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $card->tournamentAssignments->count() }} tournament(s)</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($card->tournamentAssignments->isEmpty())
                    <div class="p-3 text-muted text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        This card has not been assigned to any tournament yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tournament</th>
                                    <th>Racer</th>
                                    <th>Team</th>
                                    <th>Status</th>
                                    <th>Assigned At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($card->tournamentAssignments as $assignment)
                                    <tr>
                                        <td>
                                            {{ $assignment->tournament->tournament_name ?? '-' }}
                                        </td>
                                        <td>
                                            @if($assignment->racer)
                                                <a href="{{ route('admin.racers.show', $assignment->racer->id) }}">
                                                    {{ $assignment->racer->racer_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->racer && $assignment->racer->team)
                                                {{ $assignment->racer->team->team_name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $assignment->status == 'ACTIVE' ? 'success' : ($assignment->status == 'BANNED' ? 'danger' : 'warning') }}">
                                                {{ $assignment->status }}
                                            </span>
                                        </td>
                                        <td>{{ $assignment->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
