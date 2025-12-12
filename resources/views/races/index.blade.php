@extends('adminlte::page')

@section('title', 'Races')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Races</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
            <p class="text-muted mb-0">Current Stage: <strong>{{ $tournament->current_stage }}</strong></p>
        </div>
        <div class="d-flex">
            <div class="btn-group mr-2">
                <a href="{{ route('races.index', array_merge(request()->all(), ['view' => 'team'])) }}" class="btn btn-default {{ $viewMode === 'team' ? 'active' : '' }}">
                    Races - Team Only
                </a>
                <a href="{{ route('races.index', array_merge(request()->all(), ['view' => 'with_racer'])) }}" class="btn btn-default {{ $viewMode === 'with_racer' ? 'active' : '' }}">
                    Races - With Racer
                </a>
            </div>
            <a href="{{ route('races.create', request()->only(['stage', 'track', 'team_id'])) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Race
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
            <h3 class="card-title">Races in Tournament</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('races.index') }}" class="form-inline" id="raceFilterForm">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    <div class="input-group input-group-sm mr-2">
                        <select name="stage" class="form-control" onchange="this.form.submit()" style="width: 120px;">
                            <option value="">Select Stage</option>
                            @foreach($stages as $stageNum)
                                <option value="{{ $stageNum }}" {{ $selectedStage == $stageNum ? 'selected' : '' }}>
                                    Stage {{ $stageNum }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($selectedStage)
                        <a href="{{ route('races.create', ['stage' => $selectedStage]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Race
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body">
            @if($selectedStage)
                @php
                    $trackNumber = $tournament->track_number ?? 2;
                    $lanesPerTrack = 3;
                    $totalLanes = $trackNumber * $lanesPerTrack;
                @endphp
                
                <div class="table-responsive race-schedule-table">
                    <table class="table race-schedule-table-inner">
                        <thead>
                            <tr>
                                <th rowspan="3" class="race-called-header">
                                    <i class="fas fa-check"></i>
                                </th>
                                <th rowspan="3" class="race-no-header">
                                    RACE NO
                                </th>
                                <th colspan="{{ $trackNumber * $lanesPerTrack }}" class="stage-header">
                                    STAGE {{ $selectedStage }}
                                </th>
                            </tr>
                            <tr>
                                @for($track = 1; $track <= $trackNumber; $track++)
                                    <th colspan="{{ $lanesPerTrack }}" class="track-header track-{{ $track }}">
                                        TRACK {{ $track }}
                                    </th>
                                @endfor
                            </tr>
                            <tr>
                                @for($track = 1; $track <= $trackNumber; $track++)
                                    @for($laneIndex = 0; $laneIndex < $lanesPerTrack; $laneIndex++)
                                        @php
                                            $laneLetter = chr(65 + (($track - 1) * $lanesPerTrack) + $laneIndex);
                                        @endphp
                                        <th class="lane-header track-{{ $track }}">
                                            {{ $laneLetter }}
                                        </th>
                                    @endfor
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for($raceNo = 1; $raceNo <= $maxRaceNo; $raceNo++)
                                @php
                                    // Check if this race is called by checking any race in this race_no
                                    $isCalled = false;
                                    if (isset($racesByStage[$selectedStage][$raceNo])) {
                                        foreach ($racesByStage[$selectedStage][$raceNo] as $raceInRow) {
                                            if ($raceInRow && $raceInRow->is_called) {
                                                $isCalled = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <tr class="{{ $isCalled ? 'race-called-row' : '' }}">
                                    <td class="race-called-cell">
                                        <input type="checkbox" 
                                               class="race-called-checkbox" 
                                               data-stage="{{ $selectedStage }}"
                                               data-race-no="{{ $raceNo }}"
                                               {{ $isCalled ? 'checked' : '' }}>
                                    </td>
                                    <td class="race-no-cell">
                                        {{ $raceNo }}
                                    </td>
                                    @for($track = 1; $track <= $trackNumber; $track++)
                                        @php
                                            $teamIdsForTrack = [];
                                            for ($laneIdx = 0; $laneIdx < $lanesPerTrack; $laneIdx++) {
                                                $laneLetterForCheck = chr(65 + (($track - 1) * $lanesPerTrack) + $laneIdx);
                                                $raceForCheck = $racesByStage[$selectedStage][$raceNo][$laneLetterForCheck] ?? null;
                                                $teamIdsForTrack[] = $raceForCheck && $raceForCheck->team ? $raceForCheck->team->id : null;
                                            }
                                            $hasTripleSameTeam = count(array_filter($teamIdsForTrack)) === $lanesPerTrack
                                                && count(array_unique($teamIdsForTrack)) === 1;
                                        @endphp
                                        @for($laneIndex = 0; $laneIndex < $lanesPerTrack; $laneIndex++)
                                            @php
                                                $laneLetter = chr(65 + (($track - 1) * $lanesPerTrack) + $laneIndex);
                                                $race = $racesByStage[$selectedStage][$raceNo][$laneLetter] ?? null;
                                            @endphp
                                            <td class="race-cell track-{{ $track }} {{ $race && $race->team ? 'has-team' : 'empty-cell' }} {{ $hasTripleSameTeam ? 'triple-team' : '' }}">
                                                @if($race && $race->team)
                                                    @php
                                                        $badgeLabel = strtoupper($race->team->team_name);
                                                        if ($viewMode === 'with_racer' && $race->racer) {
                                                            $badgeLabel = strtoupper($race->team->team_name . ' - ' . $race->racer->racer_name);
                                                        }
                                                    @endphp
                                                    <span class="team-badge track-{{ $track }} {{ $hasTripleSameTeam ? 'triple-team-badge' : '' }}">
                                                        {{ $badgeLabel }}
                                                    </span>
                                                @endif
                                            </td>
                                        @endfor
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Please select a stage to view races.
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
    /* Pastel Color Palette for Tracks */
    :root {
        /* Track 1 - Pastel Pink */
        --track-1-header: #FFE0B2;
        --track-1-lane: #FFF3E0;
        --track-1-cell: #FFF8F0;
        --track-1-cell-filled: #FFF3E0;
        --track-1-badge: #FB8C00;
        --track-1-border: #FFCC80;
        --race-stage-header-height: 56px;
        --race-track-header-height: 42px;
        --race-lane-header-height: 38px;
        
        /* Track 2 - Pastel Blue */
        --track-2-header: #B3E5FC;
        --track-2-lane: #E1F5FE;
        --track-2-cell: #F0FAFF;
        --track-2-cell-filled: #E3F7FF;
        --track-2-badge: #039BE5;
        --track-2-border: #81D4FA;
        
        /* Track 3 - Pastel Green */
        --track-3-header: #C8E6C9;
        --track-3-lane: #E8F5E9;
        --track-3-cell: #F1F8F2;
        --track-3-cell-filled: #E8F5E9;
        --track-3-badge: #43A047;
        --track-3-border: #A5D6A7;
        
        /* Track 4 - Pastel Purple */
        --track-4-header: #E1BEE7;
        --track-4-lane: #F3E5F5;
        --track-4-cell: #FAF0FC;
        --track-4-cell-filled: #F3E5F5;
        --track-4-badge: #8E24AA;
        --track-4-border: #CE93D8;
        
        /* Track 5 - Pastel Orange */
        --track-5-header: #F8BBD9;
        --track-5-lane: #FCE4EC;
        --track-5-cell: #FFF0F5;
        --track-5-cell-filled: #FFEBF0;
        --track-5-badge: #E91E63;
        --track-5-border: #F48FB1;
        
        /* Track 6 - Pastel Teal */
        --track-6-header: #B2DFDB;
        --track-6-lane: #E0F2F1;
        --track-6-cell: #F0FAF9;
        --track-6-cell-filled: #E0F2F1;
        --track-6-badge: #00897B;
        --track-6-border: #80CBC4;
    }

    .race-schedule-table {
        padding: 20px 0;
        background: #F5F5F5;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
        max-height: 75vh;
        overflow: auto;
        position: relative;
        border-radius: 12px;
    }

    .race-schedule-table-inner {
        width: 100%;
        margin: 0 auto;
        border-collapse: separate;
        border-spacing: 0;
        background: #ffffff;
        border-radius: 8px;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    /* Sticky header */
    .race-schedule-table-inner thead th {
        position: sticky;
        z-index: 90;
        background: #ffffff;
    }

    .race-schedule-table-inner thead tr:nth-child(1) th {
        top: 0;
        z-index: 99;
    }

    .race-schedule-table-inner thead tr:nth-child(2) th {
        top: var(--race-stage-header-height);
        z-index: 98;
    }

    .race-schedule-table-inner thead tr:nth-child(3) th {
        top: calc(var(--race-stage-header-height) + var(--race-track-header-height));
        z-index: 97;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    .race-schedule-table-inner thead tr {
        position: relative;
    }

    /* Stage Header */
    .stage-header {
        background: #78909C;
        color: #ffffff;
        text-align: center;
        font-weight: 700;
        font-size: 20px;
        padding: 16px 12px;
        letter-spacing: 1px;
        border: none;
        height: var(--race-stage-header-height);
    }

    /* Track Header - Base */
    .track-header {
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        padding: 12px 8px;
        color: #37474F;
        height: var(--race-track-header-height);
    }

    /* Track Header Colors */
    .track-header.track-1 { background: var(--track-1-header); border: 1px solid var(--track-1-border); }
    .track-header.track-2 { background: var(--track-2-header); border: 1px solid var(--track-2-border); }
    .track-header.track-3 { background: var(--track-3-header); border: 1px solid var(--track-3-border); }
    .track-header.track-4 { background: var(--track-4-header); border: 1px solid var(--track-4-border); }
    .track-header.track-5 { background: var(--track-5-header); border: 1px solid var(--track-5-border); }
    .track-header.track-6 { background: var(--track-6-header); border: 1px solid var(--track-6-border); }

    /* Lane Header - Base */
    .lane-header {
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 8px;
        min-width: 100px;
        color: #455A64;
        height: var(--race-lane-header-height);
    }

    /* Lane Header Colors */
    .lane-header.track-1 { background: var(--track-1-lane); border: 1px solid var(--track-1-border); }
    .lane-header.track-2 { background: var(--track-2-lane); border: 1px solid var(--track-2-border); }
    .lane-header.track-3 { background: var(--track-3-lane); border: 1px solid var(--track-3-border); }
    .lane-header.track-4 { background: var(--track-4-lane); border: 1px solid var(--track-4-border); }
    .lane-header.track-5 { background: var(--track-5-lane); border: 1px solid var(--track-5-border); }
    .lane-header.track-6 { background: var(--track-6-lane); border: 1px solid var(--track-6-border); }

    /* Race Called Header */
    .race-called-header {
        background: #ECEFF1;
        color: #37474F;
        text-align: center;
        vertical-align: middle;
        font-weight: 700;
        font-size: 16px;
        width: 50px;
        padding: 12px 8px;
        border: 1px solid #CFD8DC;
        position: sticky;
        left: 0;
        z-index: 95;
    }

    /* Race Called Cell */
    .race-called-cell {
        background: #FAFAFA;
        text-align: center;
        padding: 14px 8px;
        border: 1px solid #E0E0E0;
        min-width: 50px;
        position: sticky;
        left: 0;
        z-index: 10;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.03);
    }

    .race-called-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    /* Called Row Styling */
    .race-called-row {
        background: #e8f5e9 !important;
    }

    .race-called-row .race-no-cell,
    .race-called-row .race-called-cell {
        background: #c8e6c9 !important;
    }

    .race-called-row .race-cell {
        opacity: 0.7;
    }

    /* Race No Header */
    .race-no-header {
        background: #ECEFF1;
        color: #37474F;
        text-align: center;
        vertical-align: middle;
        font-weight: 700;
        font-size: 14px;
        width: 90px;
        padding: 12px 8px;
        border: 1px solid #CFD8DC;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        position: sticky;
        left: 50px;
        z-index: 95;
    }

    /* Race No Cell */
    .race-no-cell {
        background: #FAFAFA;
        text-align: center;
        font-weight: 700;
        font-size: 15px;
        padding: 14px 8px;
        border: 1px solid #E0E0E0;
        color: #37474F;
        min-width: 90px;
        position: sticky;
        left: 50px;
        z-index: 10;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.03);
    }

    /* Race Cell - Base */
    .race-cell {
        text-align: center;
        padding: 12px 8px;
        min-width: 120px;
        height: 50px;
        vertical-align: middle;
        border: 1px solid #E0E0E0;
        transition: all 0.2s ease;
        position: relative;
        z-index: 1;
    }

    /* Triple same-team highlight */
    .race-cell.triple-team {
        background: #ffebee !important;
        border-color: #ef9a9a !important;
    }

    .team-badge.triple-team-badge,
    .race-cell.triple-team .team-badge {
        background: #e53935 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(229, 57, 53, 0.35);
    }

    /* Race Cell Colors - Empty */
    .race-cell.empty-cell.track-1 { background: var(--track-1-cell); border-color: var(--track-1-border); }
    .race-cell.empty-cell.track-2 { background: var(--track-2-cell); border-color: var(--track-2-border); }
    .race-cell.empty-cell.track-3 { background: var(--track-3-cell); border-color: var(--track-3-border); }
    .race-cell.empty-cell.track-4 { background: var(--track-4-cell); border-color: var(--track-4-border); }
    .race-cell.empty-cell.track-5 { background: var(--track-5-cell); border-color: var(--track-5-border); }
    .race-cell.empty-cell.track-6 { background: var(--track-6-cell); border-color: var(--track-6-border); }

    /* Race Cell Colors - Has Team */
    .race-cell.has-team.track-1 { background: var(--track-1-cell-filled); border-color: var(--track-1-border); cursor: pointer; }
    .race-cell.has-team.track-2 { background: var(--track-2-cell-filled); border-color: var(--track-2-border); cursor: pointer; }
    .race-cell.has-team.track-3 { background: var(--track-3-cell-filled); border-color: var(--track-3-border); cursor: pointer; }
    .race-cell.has-team.track-4 { background: var(--track-4-cell-filled); border-color: var(--track-4-border); cursor: pointer; }
    .race-cell.has-team.track-5 { background: var(--track-5-cell-filled); border-color: var(--track-5-border); cursor: pointer; }
    .race-cell.has-team.track-6 { background: var(--track-6-cell-filled); border-color: var(--track-6-border); cursor: pointer; }

    .race-cell.has-team:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 5;
        position: relative;
    }

    /* Team Badge - Base */
    .team-badge {
        display: inline-block;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        transition: all 0.2s ease;
    }

    /* Team Badge Colors */
    .team-badge.track-1 { background: var(--track-1-badge); }
    .team-badge.track-2 { background: var(--track-2-badge); }
    .team-badge.track-3 { background: var(--track-3-badge); }
    .team-badge.track-4 { background: var(--track-4-badge); }
    .team-badge.track-5 { background: var(--track-5-badge); }
    .team-badge.track-6 { background: var(--track-6-badge); }

    .race-cell.has-team:hover .team-badge {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Table Row Hover Effect */
    tbody tr:hover .race-no-cell {
        background: #ECEFF1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .race-schedule-table {
            padding: 10px 0;
        }

        .stage-header {
            font-size: 16px;
            padding: 12px 8px;
        }

        .track-header {
            font-size: 12px;
            padding: 10px 6px;
        }

        .lane-header {
            font-size: 11px;
            padding: 8px 6px;
            min-width: 80px;
        }

        .race-no-cell {
            font-size: 13px;
            padding: 10px 6px;
            min-width: 70px;
        }

        .race-cell {
            min-width: 90px;
            padding: 10px 6px;
        }

        .team-badge {
            padding: 6px 12px;
            font-size: 11px;
        }
    }

    /* Print styles */
    @media print {
        .race-schedule-table {
            background: #ffffff;
            box-shadow: none;
        }

        .race-schedule-table-inner {
            box-shadow: none;
        }

        .race-cell.has-team:hover {
            transform: none;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Handle race called checkbox
    $('.race-called-checkbox').on('change', function() {
        const checkbox = $(this);
        const stage = checkbox.data('stage');
        const raceNo = checkbox.data('race-no');
        const isCalled = checkbox.is(':checked');
        const row = checkbox.closest('tr');

        // Send AJAX request
        $.ajax({
            url: '{{ route("races.toggleCalled") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                stage: stage,
                race_no: raceNo,
                is_called: isCalled ? 1 : 0
            },
            success: function(response) {
                // Toggle row styling
                if (isCalled) {
                    row.addClass('race-called-row');
                } else {
                    row.removeClass('race-called-row');
                }
            },
            error: function(xhr) {
                // Revert checkbox on error
                checkbox.prop('checked', !isCalled);
                alert('Failed to update race status. Please try again.');
            }
        });
    });
});
</script>
@stop
