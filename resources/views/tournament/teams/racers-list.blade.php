@extends('adminlte::page')

@section('title', 'Racer List')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Racer List</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <a href="{{ route('tournament.teams.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Teams
    </a>
</div>
@stop

@section('content')
@php
    $sortIcon = function ($col) use ($sort, $direction) {
        if ($sort === $col) {
            return $direction === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
        }
        return ' <i class="fas fa-sort text-muted"></i>';
    };
    $sortDir = fn ($col) => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
    $sortUrl = fn ($col) => route('tournament.teams.racers-list', ['sort' => $col, 'direction' => $sortDir($col)]);
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Active Racers</h3>
        <div class="card-tools">
            <span class="badge badge-info">{{ $racerParticipants->count() }} racers</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
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
                            <a href="{{ $sortUrl('card_no') }}" class="text-dark text-decoration-none">
                                Card No{!! $sortIcon('card_no') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('card_code') }}" class="text-dark text-decoration-none">
                                Card Code{!! $sortIcon('card_code') !!}
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($racerParticipants as $participant)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $participant->racer_name ?? '-' }}</td>
                            <td>{{ $participant->team_name ?? '-' }}</td>
                            <td>{{ $participant->card_no ?? '-' }}</td>
                            <td>{{ $participant->card_code ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No active racers found in this tournament.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
