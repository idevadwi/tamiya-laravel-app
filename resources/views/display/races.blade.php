@extends('adminlte::master')

@section('adminlte_css')
@stack('css')
@yield('css')
@stop

@section('classes_body', 'layout-top-nav')

@section('body')
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-flag-checkered mr-2"></i>
                {{ $tournament->tournament_name }}
            </span>
            <div class="ml-auto d-flex align-items-center" style="gap:8px;">
                <a href="{{ route('display.stats', $tournament->slug) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-chart-bar mr-1"></i> Statistics
                </a>
                <span class="navbar-text text-muted" style="font-size:13px;">
                    Stage {{ $selectedStage ?? '–' }}
                </span>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper" style="margin-left: 0; background: #f4f6f9;">
        <div class="content-header">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <div>
                        <h4 class="mb-0" style="font-weight: 700;">Race Schedule</h4>
                        <small class="text-muted">{{ $tournament->tournament_name }}</small>
                    </div>
                    <!-- Stage selector & view toggle -->
                    <form method="GET" action="{{ route('display.races', $tournament->slug) }}" class="d-flex align-items-center" style="gap: 6px; flex-wrap: wrap;">
                        <input type="hidden" name="view" value="{{ $viewMode }}">
                        <select name="stage" class="form-control form-control-sm" onchange="this.form.submit()" style="width: 130px;">
                            <option value="">Select Stage</option>
                            @foreach($stages as $stageNum)
                                <option value="{{ $stageNum }}" {{ $selectedStage == $stageNum ? 'selected' : '' }}>
                                    Stage {{ $stageNum }}
                                </option>
                            @endforeach
                        </select>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('display.races', array_merge([$tournament->slug], request()->except('view'), ['view' => 'team'])) }}"
                               class="btn btn-{{ $viewMode === 'team' ? 'primary' : 'outline-primary' }}">
                                Team
                            </a>
                            <a href="{{ route('display.races', array_merge([$tournament->slug], request()->except('view'), ['view' => 'with_racer'])) }}"
                               class="btn btn-{{ $viewMode === 'with_racer' ? 'primary' : 'outline-primary' }}">
                                + Racer
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container" style="max-width: 800px">
                @if($selectedStage)
                    @php
                        $trackNumber = $tournament->track_number ?? 2;
                        $lanesPerTrack = 3;
                    @endphp

                    <div class="race-table-wrap">
                        <div class="table-responsive">
                            <table class="table race-table">
                                <thead>
                                    <tr>
                                        <th rowspan="3" class="th-check"></th>
                                        <th rowspan="3" class="th-raceno">NO</th>
                                        <th colspan="{{ $trackNumber * $lanesPerTrack }}" class="th-stage">
                                            STAGE {{ $selectedStage }}
                                        </th>
                                    </tr>
                                    <tr>
                                        @for($track = 1; $track <= $trackNumber; $track++)
                                            <th colspan="{{ $lanesPerTrack }}" class="th-track th-track-{{ $track }}">
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
                                                <th class="th-lane th-track-{{ $track }}">{{ $laneLetter }}</th>
                                            @endfor
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($raceNo = 1; $raceNo <= $maxRaceNo; $raceNo++)
                                        @php
                                            $isCalled = false;
                                            if (isset($racesByStage[$selectedStage][$raceNo])) {
                                                foreach ($racesByStage[$selectedStage][$raceNo] as $r) {
                                                    if ($r && $r->is_called) { $isCalled = true; break; }
                                                }
                                            }
                                        @endphp
                                        <tr class="{{ $isCalled ? 'row-called' : '' }}">
                                            <td class="td-check">
                                                <input type="checkbox" class="vis-checkbox" {{ $isCalled ? 'checked' : '' }}>
                                            </td>
                                            <td class="td-raceno">{{ $raceNo }}</td>
                                            @for($track = 1; $track <= $trackNumber; $track++)
                                                @php
                                                    $teamIdsForTrack = [];
                                                    for ($laneIdx = 0; $laneIdx < $lanesPerTrack; $laneIdx++) {
                                                        $ll = chr(65 + (($track - 1) * $lanesPerTrack) + $laneIdx);
                                                        $rc = $racesByStage[$selectedStage][$raceNo][$ll] ?? null;
                                                        $teamIdsForTrack[] = $rc && $rc->team ? $rc->team->id : null;
                                                    }
                                                    $tripleTeam = count(array_filter($teamIdsForTrack)) === $lanesPerTrack
                                                        && count(array_unique($teamIdsForTrack)) === 1;
                                                @endphp
                                                @for($laneIndex = 0; $laneIndex < $lanesPerTrack; $laneIndex++)
                                                    @php
                                                        $laneLetter = chr(65 + (($track - 1) * $lanesPerTrack) + $laneIndex);
                                                        $race = $racesByStage[$selectedStage][$raceNo][$laneLetter] ?? null;
                                                    @endphp
                                                    <td class="td-cell td-track-{{ $track }} {{ $race && $race->team ? 'has-team' : 'empty-cell' }} {{ $tripleTeam ? 'triple-team' : '' }}">
                                                        @if($race && $race->team)
                                                            @php
                                                                $label = strtoupper($race->team->team_name);
                                                                if ($viewMode === 'with_racer' && $race->racer) {
                                                                    $label = strtoupper($race->team->team_name . ' - ' . $race->racer->racer_name);
                                                                }
                                                            @endphp
                                                            <span class="team-badge badge-track-{{ $track }} {{ $tripleTeam ? 'badge-triple' : '' }}">
                                                                {{ $label }}
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
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please select a stage to view the race schedule.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --t1-header: #FFE0B2; --t1-lane: #FFF3E0; --t1-cell: #FFF8F0; --t1-filled: #FFF3E0;
        --t1-badge: #FB8C00;  --t1-border: #FFCC80;
        --t2-header: #B3E5FC; --t2-lane: #E1F5FE; --t2-cell: #F0FAFF; --t2-filled: #E3F7FF;
        --t2-badge: #039BE5;  --t2-border: #81D4FA;
        --t3-header: #C8E6C9; --t3-lane: #E8F5E9; --t3-cell: #F1F8F2; --t3-filled: #E8F5E9;
        --t3-badge: #43A047;  --t3-border: #A5D6A7;
        --t4-header: #E1BEE7; --t4-lane: #F3E5F5; --t4-cell: #FAF0FC; --t4-filled: #F3E5F5;
        --t4-badge: #8E24AA;  --t4-border: #CE93D8;
        --t5-header: #F8BBD9; --t5-lane: #FCE4EC; --t5-cell: #FFF0F5; --t5-filled: #FFEBF0;
        --t5-badge: #E91E63;  --t5-border: #F48FB1;
        --t6-header: #B2DFDB; --t6-lane: #E0F2F1; --t6-cell: #F0FAF9; --t6-filled: #E0F2F1;
        --t6-badge: #00897B;  --t6-border: #80CBC4;
    }

    body { background: #f4f6f9 !important; }
    .content-wrapper { min-height: 100vh; }

    /* Table wrapper */
    .race-table-wrap {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .race-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }

    /* Sticky columns */
    .th-check, .td-check { position: sticky; left: 0; z-index: 10; }
    .th-raceno, .td-raceno { position: sticky; left: 36px; z-index: 10; }
    thead .th-check, thead .th-raceno { z-index: 20; }

    /* Stage header */
    .th-stage {
        background: #78909C;
        color: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 16px;
        padding: 12px 8px;
        letter-spacing: 1px;
        border: none;
    }

    /* Check header */
    .th-check {
        background: #ECEFF1;
        width: 36px;
        min-width: 36px;
        text-align: center;
        vertical-align: middle;
        padding: 8px 4px;
        border: 1px solid #CFD8DC;
        font-size: 13px;
        color: #37474F;
    }

    /* Race No header */
    .th-raceno {
        background: #ECEFF1;
        width: 44px;
        min-width: 44px;
        text-align: center;
        vertical-align: middle;
        font-weight: 700;
        font-size: 12px;
        padding: 8px 4px;
        border: 1px solid #CFD8DC;
        color: #37474F;
    }

    /* Track headers */
    .th-track {
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 6px;
        color: #37474F;
    }
    .th-track-1 { background: var(--t1-header); border: 1px solid var(--t1-border); }
    .th-track-2 { background: var(--t2-header); border: 1px solid var(--t2-border); }
    .th-track-3 { background: var(--t3-header); border: 1px solid var(--t3-border); }
    .th-track-4 { background: var(--t4-header); border: 1px solid var(--t4-border); }
    .th-track-5 { background: var(--t5-header); border: 1px solid var(--t5-border); }
    .th-track-6 { background: var(--t6-header); border: 1px solid var(--t6-border); }

    /* Lane headers */
    .th-lane {
        text-align: center;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 4px;
        min-width: 80px;
        color: #455A64;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    .th-lane.th-track-1 { background: var(--t1-lane); border: 1px solid var(--t1-border); }
    .th-lane.th-track-2 { background: var(--t2-lane); border: 1px solid var(--t2-border); }
    .th-lane.th-track-3 { background: var(--t3-lane); border: 1px solid var(--t3-border); }
    .th-lane.th-track-4 { background: var(--t4-lane); border: 1px solid var(--t4-border); }
    .th-lane.th-track-5 { background: var(--t5-lane); border: 1px solid var(--t5-border); }
    .th-lane.th-track-6 { background: var(--t6-lane); border: 1px solid var(--t6-border); }

    /* Check cell */
    .td-check {
        background: #FAFAFA;
        text-align: center;
        padding: 10px 4px;
        border: 1px solid #E0E0E0;
        min-width: 36px;
        width: 36px;
    }

    .vis-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Race no cell */
    .td-raceno {
        background: #FAFAFA;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        padding: 10px 6px;
        border: 1px solid #E0E0E0;
        color: #37474F;
        min-width: 44px;
        width: 44px;
        box-shadow: 2px 0 4px rgba(0,0,0,0.04);
    }

    /* Race cell */
    .td-cell {
        text-align: center;
        padding: 8px 6px;
        border: 1px solid #E0E0E0;
        vertical-align: middle;
        min-width: 80px;
        word-break: break-word;
    }
    .td-cell.empty-cell.td-track-1 { background: var(--t1-cell); border-color: var(--t1-border); }
    .td-cell.empty-cell.td-track-2 { background: var(--t2-cell); border-color: var(--t2-border); }
    .td-cell.empty-cell.td-track-3 { background: var(--t3-cell); border-color: var(--t3-border); }
    .td-cell.empty-cell.td-track-4 { background: var(--t4-cell); border-color: var(--t4-border); }
    .td-cell.empty-cell.td-track-5 { background: var(--t5-cell); border-color: var(--t5-border); }
    .td-cell.empty-cell.td-track-6 { background: var(--t6-cell); border-color: var(--t6-border); }

    .td-cell.has-team.td-track-1 { background: var(--t1-filled); border-color: var(--t1-border); }
    .td-cell.has-team.td-track-2 { background: var(--t2-filled); border-color: var(--t2-border); }
    .td-cell.has-team.td-track-3 { background: var(--t3-filled); border-color: var(--t3-border); }
    .td-cell.has-team.td-track-4 { background: var(--t4-filled); border-color: var(--t4-border); }
    .td-cell.has-team.td-track-5 { background: var(--t5-filled); border-color: var(--t5-border); }
    .td-cell.has-team.td-track-6 { background: var(--t6-filled); border-color: var(--t6-border); }

    /* Triple team */
    .td-cell.triple-team { background: #ffebee !important; border-color: #ef9a9a !important; }

    /* Called row */
    .row-called { background: #e8f5e9 !important; }
    .row-called .td-check,
    .row-called .td-raceno { background: #c8e6c9 !important; }
    .row-called .td-cell { opacity: 0.7; }

    /* Team badge */
    .team-badge {
        display: inline-block;
        color: #fff;
        padding: 5px 9px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        white-space: normal;
        word-break: break-word;
        line-height: 1.3;
        max-width: 100%;
    }
    .badge-track-1 { background: var(--t1-badge); }
    .badge-track-2 { background: var(--t2-badge); }
    .badge-track-3 { background: var(--t3-badge); }
    .badge-track-4 { background: var(--t4-badge); }
    .badge-track-5 { background: var(--t5-badge); }
    .badge-track-6 { background: var(--t6-badge); }
    .badge-triple { background: #e53935 !important; box-shadow: 0 2px 6px rgba(229,57,53,0.35); }

    /* Mobile */
    @media (max-width: 576px) {
        .th-stage { font-size: 13px; padding: 8px 4px; }
        .th-track { font-size: 11px; padding: 6px 3px; }
        .th-lane  { font-size: 11px; padding: 5px 3px; min-width: 60px; }
        .td-raceno { font-size: 12px; }
        .td-cell { padding: 6px 3px; min-width: 60px; }
        .team-badge { font-size: 10px; padding: 4px 6px; }
        .content-header { padding: 10px 0; }
    }
</style>
@stop
