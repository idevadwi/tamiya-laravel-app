@extends('adminlte::page')

@section('title', 'Racer Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Racer: {{ $racer->racer_name }}</h1>
    <div>
        <a href="{{ route('admin.racers.edit', $racer->id) }}" class="btn btn-warning mr-2">
            <i class="fas fa-edit"></i> Edit Racer
        </a>
        <a href="{{ route('admin.racers.index') }}" class="btn btn-default">Back to List</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    @if($racer->image)
                        <img class="profile-user-img img-fluid img-circle" src="{{ Storage::url($racer->image) }}"
                            alt="User profile picture" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center bg-gray-light"
                            style="width: 150px; height: 150px; margin: 0 auto; border-radius: 50%;">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $racer->racer_name }}</h3>

                <p class="text-muted text-center">
                    @if($racer->team)
                        <a href="{{ route('admin.teams.show', $racer->team_id) }}">{{ $racer->team->team_name }}</a>
                    @else
                        Unassigned
                    @endif
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Cards</b> <a class="float-right">{{ $racer->cards->count() }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Tournaments</b> <a class="float-right">{{ $racer->tournamentRacerParticipants->count() }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#cards" data-toggle="tab">Cards</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tournaments" data-toggle="tab">Tournament
                            History</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Cards Tab -->
                    <div class="active tab-pane" id="cards">
                        <div class="mb-3">
                            <a href="{{ route('admin.cards.create') }}?racer_id={{ $racer->id }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add New Card
                            </a>
                        </div>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Card Code</th>
                                    <th>Status</th>
                                    <th>Coupons</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($racer->cards as $card)
                                    <tr>
                                        <td>{{ $card->card_code }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $card->status == 'ACTIVE' ? 'success' : ($card->status == 'BANNED' ? 'danger' : 'warning') }}">
                                                {{ $card->status }}
                                            </span>
                                        </td>
                                        <td>{{ $card->coupon }}</td>
                                        <td>
                                            <a href="{{ route('admin.cards.edit', $card->id) }}"
                                                class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($racer->cards->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center">No cards assigned to this racer.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Tournaments Tab -->
                    <div class="tab-pane" id="tournaments">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tournament</th>
                                    <th>Team Used</th>
                                    <th>Joined At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($racer->tournamentRacerParticipants as $participant)
                                    <tr>
                                        <td>{{ $participant->tournament->tournament_name }}</td>
                                        <td>
                                            @if($participant->team)
                                                <a href="{{ route('admin.teams.show', $participant->team_id) }}">
                                                    {{ $participant->team->team_name }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $participant->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                                @if($racer->tournamentRacerParticipants->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center">This racer has not participated in any
                                            tournaments yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop