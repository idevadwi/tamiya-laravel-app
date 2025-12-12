@extends('adminlte::page')

@section('title', 'Tournament Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tournament Details</h1>
        <div>
            <a href="{{ route('tournaments.edit', $tournament->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('tournaments.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tournament Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Tournament Name</th>
                            <td>{{ $tournament->tournament_name }}</td>
                        </tr>
                        <tr>
                            <th>Vendor Name</th>
                            <td>{{ $tournament->vendor_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($tournament->status === 'ACTIVE')
                                    <span class="badge badge-success">{{ $tournament->status }}</span>
                                @elseif($tournament->status === 'COMPLETED')
                                    <span class="badge badge-info">{{ $tournament->status }}</span>
                                @elseif($tournament->status === 'CANCELLED')
                                    <span class="badge badge-danger">{{ $tournament->status }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $tournament->status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Track Number</th>
                            <td>{{ $tournament->track_number }}</td>
                        </tr>
                        <tr>
                            <th>Max Racers Per Team</th>
                            <td>{{ $tournament->max_racer_per_team }}</td>
                        </tr>
                        <tr>
                            <th>Champion Number</th>
                            <td>{{ $tournament->champion_number }}</td>
                        </tr>
                        <tr>
                            <th>Current Stage</th>
                            <td>{{ $tournament->current_stage }}</td>
                        </tr>
                        <tr>
                            <th>Current BTO Session</th>
                            <td>{{ $tournament->current_bto_session }}</td>
                        </tr>
                        <tr>
                            <th>BTO Number</th>
                            <td>{{ $tournament->bto_number }}</td>
                        </tr>
                        <tr>
                            <th>BTO Session Number</th>
                            <td>{{ $tournament->bto_session_number }}</td>
                        </tr>
                        <tr>
                            <th>Best Race Enabled</th>
                            <td>
                                @if($tournament->best_race_enabled)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Best Race Number</th>
                            <td>{{ $tournament->best_race_number }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $tournament->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{ $tournament->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('tournaments.edit', $tournament->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Tournament
                    </a>
                    <form action="{{ route('tournaments.destroy', $tournament->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this tournament?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Tournament
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop

