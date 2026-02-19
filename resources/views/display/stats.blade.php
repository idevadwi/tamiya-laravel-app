@extends('adminlte::master')

@section('adminlte_css')
@stack('css')
@yield('css')
@stop

@section('classes_body', 'layout-top-nav')

@section('body')
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-dark" style="background:#1a237e;">
        <div class="container-fluid">
            <span class="navbar-brand font-weight-bold">
                <i class="fas fa-flag-checkered mr-2"></i>{{ $tournament->tournament_name }}
            </span>
            <div class="ml-auto d-flex align-items-center" style="gap:8px;">
                <a href="{{ route('display.races', $tournament->slug) }}"
                   class="btn btn-sm btn-outline-light">
                    <i class="fas fa-list-ol mr-1"></i> Race Schedule
                </a>
                <span class="navbar-text text-light" style="opacity:.6;">
                    <i class="fas fa-chart-bar mr-1"></i> Statistics
                </span>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper" style="margin-left:0; background:#f4f6f9;">

        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0 font-weight-bold">Tournament Statistics</h4>
                        <small class="text-muted">{{ $tournament->tournament_name }}</small>
                    </div>
                    @if($stages->count() > 0)
                    <span class="badge badge-secondary" style="font-size:13px;">
                        {{ $stages->count() }} Stage(s)
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">

                @if($stages->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> No race data available yet for this tournament.
                </div>
                @else

                {{-- ===== OVERALL BESTS ===== --}}
                <div class="row">
                    <div class="col-lg-6 col-md-6 mb-3">
                        <div class="card card-success card-outline h-100">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-trophy text-warning mr-1"></i> Best Race (All Stages)
                                </h3>
                            </div>
                            <div class="card-body text-center py-4">
                                @if($bestRaceOverall)
                                    <div style="font-size:3rem; font-weight:700; color:#28a745; line-height:1;">
                                        {{ $bestRaceOverall->total }}
                                    </div>
                                    <div class="text-muted small mb-2">total races</div>
                                    <h4 class="font-weight-bold mb-0">{{ $bestRaceOverall->team_name }}</h4>
                                @else
                                    <p class="text-muted mt-3">No data</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 mb-3">
                        <div class="card card-primary card-outline h-100">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-1 text-primary"></i> Best Racer (All Stages)
                                </h3>
                            </div>
                            <div class="card-body text-center py-4">
                                @if($bestRacerOverall)
                                    <div style="font-size:3rem; font-weight:700; color:#007bff; line-height:1;">
                                        {{ $bestRacerOverall->total }}
                                    </div>
                                    <div class="text-muted small mb-2">total races</div>
                                    <h4 class="font-weight-bold mb-0">{{ $bestRacerOverall->racer_name }}</h4>
                                    <small class="text-muted">{{ $bestRacerOverall->team_name }}</small>
                                @else
                                    <p class="text-muted mt-3">No data</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== PER STAGE LEADERBOARDS ===== --}}
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-layer-group mr-1"></i> Per Stage Leaderboard
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs px-3 pt-2" id="stageTabs" role="tablist">
                            @foreach($stages as $i => $stage)
                            <li class="nav-item">
                                <a class="nav-link {{ $i === 0 ? 'active' : '' }}"
                                   data-toggle="tab"
                                   href="#stage-tab-{{ $stage }}"
                                   role="tab">
                                    Stage {{ $stage }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <div class="tab-content p-3">
                            @foreach($stages as $i => $stage)
                            <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="stage-tab-{{ $stage }}" role="tabpanel">
                                <div class="row">
                                    {{-- Best Race this stage --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="card mb-0 shadow-sm">
                                            <div class="card-header p-2" style="background:#e8f5e9;">
                                                <h6 class="mb-0 font-weight-bold text-success">
                                                    <i class="fas fa-trophy mr-1"></i> Best Race — Stage {{ $stage }}
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="32">#</th>
                                                            <th>Team</th>
                                                            <th width="70" class="text-center">Races</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($bestRacePerStage[$stage] as $idx => $item)
                                                        <tr class="{{ $idx === 0 ? 'table-success' : '' }}">
                                                            <td>
                                                                @if($idx === 0)
                                                                    <i class="fas fa-trophy text-warning"></i>
                                                                @else
                                                                    {{ $idx + 1 }}
                                                                @endif
                                                            </td>
                                                            <td class="{{ $idx === 0 ? 'font-weight-bold' : '' }}">
                                                                {{ $item->team_name }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-{{ $idx === 0 ? 'success' : 'secondary' }}">
                                                                    {{ $item->total }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-2">No data</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Best Racer this stage --}}
                                    <div class="col-md-6 mb-3">
                                        <div class="card mb-0 shadow-sm">
                                            <div class="card-header p-2" style="background:#e3f2fd;">
                                                <h6 class="mb-0 font-weight-bold text-primary">
                                                    <i class="fas fa-user mr-1"></i> Best Racer — Stage {{ $stage }}
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="32">#</th>
                                                            <th>Racer</th>
                                                            <th>Team</th>
                                                            <th width="70" class="text-center">Races</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($bestRacerPerStage[$stage] as $idx => $item)
                                                        <tr class="{{ $idx === 0 ? 'table-primary' : '' }}">
                                                            <td>
                                                                @if($idx === 0)
                                                                    <i class="fas fa-trophy text-warning"></i>
                                                                @else
                                                                    {{ $idx + 1 }}
                                                                @endif
                                                            </td>
                                                            <td class="{{ $idx === 0 ? 'font-weight-bold' : '' }}">
                                                                {{ $item->racer_name }}
                                                            </td>
                                                            <td class="text-muted small">{{ $item->team_name }}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-{{ $idx === 0 ? 'primary' : 'secondary' }}">
                                                                    {{ $item->total }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-2">No data</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ===== TEAM DRILLDOWN ===== --}}
                <div class="card card-outline card-warning mb-4">
                    <div class="card-header d-flex align-items-center" style="gap:12px; flex-wrap:wrap;">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-search mr-1"></i> Team Drilldown
                        </h3>
                        <div class="ml-auto">
                            <select id="teamSelect" class="form-control form-control-sm" style="min-width:200px;">
                                <option value="">— Select Team —</option>
                                @foreach($teamsMap as $teamId => $teamName)
                                <option value="{{ $teamId }}">{{ $teamName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="drilldown-placeholder" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            Select a team above to view detailed statistics
                        </div>
                        <div id="drilldown-content" style="display:none;">
                            <h5 id="drilldown-team-name" class="font-weight-bold mb-3"></h5>
                            <div id="drilldown-stage-btns" class="mb-3"></div>
                            <div id="drilldown-stats"></div>
                        </div>
                    </div>
                </div>

                @endif {{-- end stages not empty --}}

            </div>
        </div>
    </div>

    <footer class="main-footer" style="margin-left:0;">
        <div class="container-fluid">
            <strong>&copy; {{ date('Y') }} Racelane Mini 4WD Race App</strong>
        </div>
    </footer>
</div>

<script>
const TEAM_STATS = @json($teamStatsData);

let selectedTeamId = null;
let selectedStage   = null;

document.getElementById('teamSelect').addEventListener('change', function () {
    selectedTeamId = this.value || null;
    selectedStage  = null;
    if (!selectedTeamId) { showPlaceholder(); return; }
    renderDrilldown();
});

function showPlaceholder() {
    document.getElementById('drilldown-placeholder').style.display = 'block';
    document.getElementById('drilldown-content').style.display    = 'none';
}

function renderDrilldown() {
    if (!selectedTeamId || !TEAM_STATS[selectedTeamId]) { showPlaceholder(); return; }

    const team   = TEAM_STATS[selectedTeamId];
    const stages = Object.keys(team.stages).sort((a, b) => parseInt(a) - parseInt(b));

    if (!selectedStage || !team.stages[selectedStage]) {
        selectedStage = stages[0];
    }

    document.getElementById('drilldown-placeholder').style.display = 'none';
    document.getElementById('drilldown-content').style.display     = 'block';
    document.getElementById('drilldown-team-name').textContent     = team.name;

    // Stage buttons
    let stageBtns = '';
    stages.forEach(stage => {
        const active = stage === selectedStage ? 'btn-warning' : 'btn-outline-secondary';
        stageBtns += `<button class="btn btn-sm ${active} mr-1 mb-1" onclick="selectStage('${stage}')">Stage ${stage}</button>`;
    });
    document.getElementById('drilldown-stage-btns').innerHTML = stageBtns;

    renderStageStats(team, selectedStage);
}

function selectStage(stage) {
    selectedStage = stage;
    renderDrilldown();
}

function renderStageStats(team, stage) {
    const data = team.stages[stage];
    if (!data) {
        document.getElementById('drilldown-stats').innerHTML = '<p class="text-muted">No data for this stage.</p>';
        return;
    }

    // Total races box
    let html = `
    <div class="row mb-3">
        <div class="col-sm-4 col-6">
            <div class="small-box bg-teal mb-0">
                <div class="inner">
                    <h3>${data.total}</h3>
                    <p>Total Races (Stage ${stage})</p>
                </div>
                <div class="icon"><i class="fas fa-flag-checkered"></i></div>
            </div>
        </div>
    </div>`;

    // By Racer table
    const racers = Object.values(data.by_racer).sort((a, b) => b.count - a.count);
    html += `
    <div class="row">
        <div class="col-md-5 mb-3">
            <div class="card shadow-sm mb-0">
                <div class="card-header p-2 bg-light">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-user mr-1 text-primary"></i> Races by Racer
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Racer</th><th class="text-center" width="80">Races</th></tr>
                        </thead>
                        <tbody>`;

    racers.forEach((racer, i) => {
        const badge = i === 0 ? 'badge-primary' : 'badge-secondary';
        html += `<tr>
            <td>${escHtml(racer.name)}</td>
            <td class="text-center"><span class="badge ${badge}">${racer.count}</span></td>
        </tr>`;
    });
    html += `</tbody></table></div></div></div>`;

    // By Lane table
    const lanes = Object.keys(data.by_lane).sort();
    if (lanes.length > 0) {
        html += `
        <div class="col-md-7 mb-3">
            <div class="card shadow-sm mb-0">
                <div class="card-header p-2 bg-light">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-road mr-1 text-success"></i> Races by Lane
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 text-center">
                        <thead class="thead-light"><tr>`;
        lanes.forEach(lane => { html += `<th>Lane ${lane}</th>`; });
        html += `</tr></thead><tbody><tr>`;
        lanes.forEach(lane => {
            html += `<td><span class="badge badge-success badge-lg">${data.by_lane[lane]}</span></td>`;
        });
        html += `</tr></tbody></table></div></div></div>`;
    }

    html += `</div>`; // close row

    // By Racer + Lane matrix
    const racerLanes = Object.values(data.by_racer_lane);
    if (racerLanes.length > 0 && lanes.length > 0) {
        racerLanes.sort((a, b) => {
            const aT = Object.values(a.lanes).reduce((s, v) => s + v, 0);
            const bT = Object.values(b.lanes).reduce((s, v) => s + v, 0);
            return bT - aT;
        });

        html += `
        <div class="card shadow-sm mb-3">
            <div class="card-header p-2 bg-light">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-table mr-1 text-warning"></i> Races by Racer &amp; Lane
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Racer</th>`;
        lanes.forEach(lane => { html += `<th class="text-center">Lane ${lane}</th>`; });
        html += `<th class="text-center">Total</th></tr></thead><tbody>`;

        racerLanes.forEach(racer => {
            const total = Object.values(racer.lanes).reduce((s, v) => s + v, 0);
            html += `<tr><td>${escHtml(racer.name)}</td>`;
            lanes.forEach(lane => {
                const cnt = racer.lanes[lane] || 0;
                html += `<td class="text-center">
                    ${cnt > 0 ? `<span class="badge badge-warning">${cnt}</span>` : '<span class="text-muted">—</span>'}
                </td>`;
            });
            html += `<td class="text-center font-weight-bold">${total}</td></tr>`;
        });

        html += `</tbody></table></div></div></div>`;
    }

    document.getElementById('drilldown-stats').innerHTML = html;
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str)));
    return d.innerHTML;
}
</script>

<style>
    .content-wrapper { margin-left: 0 !important; }
    .main-footer     { margin-left: 0 !important; }
    .badge-lg { font-size: 0.9rem; padding: 5px 10px; }
    .small-box { border-radius: 8px; }
    .card-outline .card-header { border-bottom: 1px solid rgba(0,0,0,.125); }
    @media (max-width: 576px) {
        .card-header { flex-wrap: wrap; gap: 8px; }
        #teamSelect { width: 100% !important; }
    }
</style>

@section('adminlte_js')
@stack('js')
@yield('js')
@stop

@stop
