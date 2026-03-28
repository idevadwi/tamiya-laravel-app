@extends('adminlte::page')

@section('title', 'Races Table')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Races Table</h1>
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
            <h3 class="card-title">Race Records</h3>
            <div class="card-tools">
                <form method="GET" action="{{ route('tournament.races.tableView') }}" class="form-inline" id="stageFilterForm">
                    <div class="input-group input-group-sm">
                        <select name="stage" class="form-control" onchange="this.form.submit()" style="width: 120px;">
                            <option value="">Select Stage</option>
                            @foreach($stages as $stageNum)
                                <option value="{{ $stageNum }}" {{ $selectedStage == $stageNum ? 'selected' : '' }}>
                                    Stage {{ $stageNum }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($selectedStage)
                <form id="bulkDeleteForm" action="{{ route('tournament.races.bulkDelete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="stage" value="{{ $selectedStage }}">

                    <div class="mb-2 d-flex align-items-center">
                        <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled onclick="return confirmBulkDelete()">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <span class="ml-2 text-muted" id="selectedCount">0 selected</span>
                    </div>

                    @if($races->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No races found for Stage {{ $selectedStage }}.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="checkAll" title="Select all">
                                        </th>
                                        <th>Race No</th>
                                        <th>Lane</th>
                                        <th>Racer Name</th>
                                        <th>Team Name</th>
                                        <th>Card Code</th>
                                        <th>Card No</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($races as $race)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="race_ids[]" value="{{ $race->id }}" class="row-checkbox">
                                            </td>
                                            <td>{{ $race->race_no }}</td>
                                            <td>{{ $race->lane }}</td>
                                            <td>{{ $race->racer->racer_name ?? '-' }}</td>
                                            <td>{{ $race->team->team_name ?? '-' }}</td>
                                            <td>{{ $race->card->card_code ?? '-' }}</td>
                                            <td>{{ $race->card->card_no ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mt-1">Total: {{ $races->count() }} record(s)</p>
                    @endif
                </form>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Please select a stage to view races.
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function () {
    // Check all / uncheck all
    $('#checkAll').on('change', function () {
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    // Individual checkbox change
    $(document).on('change', '.row-checkbox', function () {
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
        $('#checkAll').prop('checked', checked === total);
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.row-checkbox:checked').length;
        $('#selectedCount').text(count + ' selected');
        $('#bulkDeleteBtn').prop('disabled', count === 0);
    }
});

function confirmBulkDelete() {
    const count = $('.row-checkbox:checked').length;
    return confirm('Are you sure you want to delete ' + count + ' race(s)? This cannot be undone.');
}
</script>
@stop
