@extends('adminlte::page')

@section('title', 'Team Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Team: {{ $team->team_name }}</h1>
    <div>
        <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-warning mr-2">
            <i class="fas fa-edit"></i> Edit Team
        </a>
        <a href="{{ route('admin.teams.index') }}" class="btn btn-default">Back to List</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#racers" data-toggle="tab">Racers</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tournaments" data-toggle="tab">Tournament
                            History</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Racers Tab -->
                    <div class="active tab-pane" id="racers">
                        <div class="mb-3">
                            <a href="{{ route('admin.racers.create') }}?team_id={{ $team->id }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add New Racer
                            </a>
                        </div>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Racer Name</th>
                                    <th>Image</th>
                                    <th>Active Cards</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($racers as $racer)
                                    <tr>
                                        <td>{{ $racer->racer_name }}</td>
                                        <td>
                                            @if($racer->image)
                                                <img src="{{ Storage::url($racer->image) }}" alt="{{ $racer->racer_name }}"
                                                    style="height: 30px;">
                                            @else
                                                <span class="text-muted">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $racer->cards_count }}</span>
                                        </td>
                                        <td>{{ $racer->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('admin.racers.show', $racer->id) }}"
                                                class="btn btn-xs btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.racers.edit', $racer->id) }}"
                                                class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($racers->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">No racers in this team.</td>
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
                                    <th>Tournament Name</th>
                                    <th>Status</th>
                                    <th>Joined At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tournaments as $tournament)
                                    <tr>
                                        <td>{{ $tournament->tournament_name }}</td>
                                        <td>
                                            @if($tournament->status == 'ACTIVE')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $tournament->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $team->tournamentParticipants->where('tournament_id', $tournament->id)->first()->created_at->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                                @if($tournaments->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center">This team has not participated in any
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