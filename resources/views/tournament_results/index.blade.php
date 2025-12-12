@extends('adminlte::page')

@section('title', 'Tournament Results')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Tournament Results</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
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
            <h3 class="card-title">Tournament Configuration</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Champions:</strong> {{ $tournament->champion_number }}
                </div>
                <div class="col-md-3">
                    <strong>BTO Numbers:</strong> {{ $tournament->bto_number }}
                </div>
                <div class="col-md-3">
                    <strong>Track Numbers:</strong> {{ $tournament->track_number }}
                </div>
                <div class="col-md-3">
                    <strong>Best Race Enabled:</strong> 
                    @if($tournament->best_race_enabled)
                        <span class="badge badge-success">Yes ({{ $tournament->best_race_number }})</span>
                    @else
                        <span class="badge badge-secondary">No</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('tournament_results.store') }}" method="POST" id="resultsForm">
        @csrf

        {{-- Main Champions --}}
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-trophy"></i> Main Champions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($categories as $category)
                        @if($category['type'] == 'champion')
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="{{ $category['key'] }}">
                                        <i class="fas fa-medal"></i> {{ $category['label'] }}
                                    </label>
                                    <select name="results[{{ $category['key'] }}][team_id]" 
                                            id="{{ $category['key'] }}" 
                                            class="form-control select2">
                                        <option value="">Select Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" 
                                                {{ isset($existingResults[$category['key']]) && $existingResults[$category['key']]->first()->team_id == $team->id ? 'selected' : '' }}>
                                                {{ $team->team_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="results[{{ $category['key'] }}][category]" value="{{ $category['key'] }}">
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- BTO Champions --}}
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-flag-checkered"></i> BTO (Best Track Owner) Champions</h3>
            </div>
            <div class="card-body">
                @php
                    $btoCategories = array_filter($categories, function($cat) {
                        return $cat['type'] == 'bto';
                    });
                    $btoByNumber = [];
                    foreach ($btoCategories as $cat) {
                        preg_match('/bto_champions_(\d+)_track_(\d+)/', $cat['key'], $matches);
                        $btoNum = $matches[1];
                        if (!isset($btoByNumber[$btoNum])) {
                            $btoByNumber[$btoNum] = [];
                        }
                        $btoByNumber[$btoNum][] = $cat;
                    }
                @endphp

                @foreach($btoByNumber as $btoNum => $btoCats)
                    <h5 class="mb-3">BTO Champions {{ $btoNum }}</h5>
                    <div class="row mb-4">
                        @foreach($btoCats as $category)
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="{{ $category['key'] }}">{{ $category['label'] }}</label>
                                    <select name="results[{{ $category['key'] }}][team_id]" 
                                            id="{{ $category['key'] }}" 
                                            class="form-control select2">
                                        <option value="">Select Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" 
                                                {{ isset($existingResults[$category['key']]) && $existingResults[$category['key']]->first()->team_id == $team->id ? 'selected' : '' }}>
                                                {{ $team->team_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="results[{{ $category['key'] }}][category]" value="{{ $category['key'] }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Best Race Champions --}}
        @if($tournament->best_race_enabled)
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title"><i class="fas fa-crown"></i> Best Race Champions (Auto-Calculated)</h3>
                </div>
                <div class="card-body">
                    @if(count($bestRaceData) > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Auto-calculated based on Stage 2 races.</strong> 
                            The system counts how many races each team won in Stage 2.
                        </div>
                        
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Team</th>
                                        <th>Total Wins (Stage 2 Races)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bestRaceData as $index => $teamWin)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $teamWin->team->team_name }}</td>
                                            <td><span class="badge badge-success">{{ $teamWin->win_count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            No Stage 2 races found yet. Best race champions will be calculated automatically once Stage 2 races are recorded.
                        </div>
                    @endif

                    <div class="row">
                        @foreach($categories as $category)
                            @if($category['type'] == 'best_race')
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="{{ $category['key'] }}">{{ $category['label'] }}</label>
                                        <select name="results[{{ $category['key'] }}][team_id]" 
                                                id="{{ $category['key'] }}" 
                                                class="form-control select2">
                                            <option value="">Select Team</option>
                                            @foreach($teams as $team)
                                                @php
                                                    $isAutoSelected = false;
                                                    $rankIndex = (int) str_replace('best_race_champions_', '', $category['key']) - 1;
                                                    if (isset($bestRaceData[$rankIndex]) && $bestRaceData[$rankIndex]->team_id == $team->id) {
                                                        $isAutoSelected = true;
                                                    }
                                                    $existingSelected = isset($existingResults[$category['key']]) && $existingResults[$category['key']]->first()->team_id == $team->id;
                                                @endphp
                                                <option value="{{ $team->id }}" 
                                                    {{ ($isAutoSelected || $existingSelected) ? 'selected' : '' }}>
                                                    {{ $team->team_name }}
                                                    @if($isAutoSelected)
                                                        (Auto)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="results[{{ $category['key'] }}][category]" value="{{ $category['key'] }}">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Tournament Results
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select Team',
                allowClear: true
            });

            // Form submission handler
            $('#resultsForm').on('submit', function(e) {
                // Show loading state
                $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            });
        });
    </script>
@stop

