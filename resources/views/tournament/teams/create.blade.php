@extends('adminlte::page')

@section('title', 'Create Team')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Add Team to Tournament</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Teams
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header p-2">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link active" href="#create-new" data-toggle="tab">
                        <i class="fas fa-plus-circle"></i> Create New Team
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#add-existing" data-toggle="tab">
                        <i class="fas fa-list"></i> Add Existing Team
                        @if($availableTeams->count() > 0)
                            <span class="badge badge-info">{{ $availableTeams->count() }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Create New Team Tab -->
                <div class="tab-pane active" id="create-new">
                    <form action="{{ route('tournament.teams.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="create">
                        
                        <div class="form-group">
                            <label for="team_name">Team Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('team_name') is-invalid @enderror" 
                                   id="team_name" 
                                   name="team_name" 
                                   value="{{ old('team_name') }}" 
                                   placeholder="Enter team name"
                                   required>
                            @error('team_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">
                                Create a brand new team for this tournament.
                            </small>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Team
                            </button>
                            <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Add Existing Team Tab -->
                <div class="tab-pane" id="add-existing">
                    @if($availableTeams->count() > 0)
                        <form action="{{ route('tournament.teams.store') }}" method="POST" id="existingTeamForm">
                            @csrf
                            <input type="hidden" name="mode" value="existing">
                            
                            <div class="form-group">
                                <label for="existing_team_id">Select Team <span class="text-danger">*</span></label>
                                <select class="form-control @error('existing_team_id') is-invalid @enderror" 
                                        id="existing_team_id" 
                                        name="existing_team_id" 
                                        required>
                                    <option value="">-- Choose a team --</option>
                                    @foreach($availableTeams as $team)
                                        <option value="{{ $team->id }}" 
                                                data-racer-count="{{ $team->racers_count }}"
                                                {{ old('existing_team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->team_name }} ({{ $team->racers_count }} racer{{ $team->racers_count != 1 ? 's' : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('existing_team_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">
                                    Select a team from previous tournaments to add to this tournament.
                                </small>
                            </div>

                            <!-- Racer Selection Section -->
                            <div id="racerSelectionSection" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Maximum Racers:</strong> This tournament allows up to <strong>{{ $tournament->max_racer_per_team }}</strong> racer(s) per team.
                                </div>

                                <div class="form-group">
                                    <label>Select Active Racers <span class="text-danger">*</span></label>
                                    <div id="racerCheckboxes" class="border rounded p-3">
                                        <!-- Racer checkboxes will be dynamically inserted here -->
                                    </div>
                                    @error('selected_racers')
                                        <span class="text-danger">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Select which racers from this team will participate in this tournament.
                                        <span id="selectedCount" class="font-weight-bold"></span>
                                    </small>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-plus"></i> Add Team to Tournament
                                </button>
                                <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>No teams available.</strong> 
                            All existing teams are already in this tournament, or no teams exist yet. 
                            Please create a new team instead.
                        </div>
                        <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to Teams
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .nav-pills .nav-link {
        border-radius: 5px;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const maxRacers = {{ $tournament->max_racer_per_team }};
    const teamsData = @json($availableTeams->keyBy('id'));
    
    // Handle error display based on which tab had the error
    @if($errors->has('team_name'))
        // Stay on create new tab
        $('.nav-link[href="#create-new"]').tab('show');
    @elseif($errors->has('existing_team_id') || $errors->has('selected_racers'))
        // Switch to existing team tab
        $('.nav-link[href="#add-existing"]').tab('show');
    @endif

    // Add Select2 to the team dropdown for better UX (if available)
    if (typeof $.fn.select2 !== 'undefined') {
        $('#existing_team_id').select2({
            theme: 'bootstrap4',
            placeholder: '-- Choose a team --',
            allowClear: true
        });
    }

    // Handle team selection change
    $('#existing_team_id').on('change', function() {
        const teamId = $(this).val();
        const $racerSection = $('#racerSelectionSection');
        const $racerCheckboxes = $('#racerCheckboxes');
        
        if (teamId && teamsData[teamId]) {
            const team = teamsData[teamId];
            const racers = team.racers || [];
            
            if (racers.length > 0) {
                // Show racer selection section
                $racerSection.show();
                
                // Clear previous checkboxes
                $racerCheckboxes.empty();
                
                // Add checkboxes for each racer
                racers.forEach(function(racer, index) {
                    const checked = racers.length <= maxRacers ? 'checked' : '';
                    const checkboxHtml = `
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" 
                                   class="custom-control-input racer-checkbox" 
                                   id="racer_${racer.id}" 
                                   name="selected_racers[]" 
                                   value="${racer.id}" 
                                   ${checked}>
                            <label class="custom-control-label" for="racer_${racer.id}">
                                ${racer.racer_name}
                            </label>
                        </div>
                    `;
                    $racerCheckboxes.append(checkboxHtml);
                });
                
                updateSelectedCount();
                
                // Add change event to checkboxes
                $('.racer-checkbox').on('change', function() {
                    const checkedCount = $('.racer-checkbox:checked').length;
                    
                    if (checkedCount > maxRacers) {
                        $(this).prop('checked', false);
                        alert(`You can only select up to ${maxRacers} racer(s) for this tournament.`);
                    }
                    
                    updateSelectedCount();
                });
            } else {
                $racerSection.hide();
                $racerCheckboxes.empty();
            }
        } else {
            $racerSection.hide();
            $racerCheckboxes.empty();
        }
    });
    
    // Update selected count display
    function updateSelectedCount() {
        const checkedCount = $('.racer-checkbox:checked').length;
        const totalCount = $('.racer-checkbox').length;
        const $selectedCount = $('#selectedCount');
        
        if (checkedCount === 0) {
            $selectedCount.html('<span class="text-danger">(0 selected - please select at least 1 racer)</span>');
        } else if (checkedCount > maxRacers) {
            $selectedCount.html(`<span class="text-danger">(${checkedCount}/${totalCount} selected - exceeds maximum of ${maxRacers})</span>`);
        } else {
            $selectedCount.html(`<span class="text-success">(${checkedCount}/${totalCount} selected)</span>`);
        }
    }
    
    // Form validation before submit
    $('#existingTeamForm').on('submit', function(e) {
        const teamId = $('#existing_team_id').val();
        
        if (teamId && teamsData[teamId]) {
            const team = teamsData[teamId];
            const racers = team.racers || [];
            
            if (racers.length > 0) {
                const checkedCount = $('.racer-checkbox:checked').length;
                
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one racer to participate in this tournament.');
                    return false;
                }
                
                if (checkedCount > maxRacers) {
                    e.preventDefault();
                    alert(`You can only select up to ${maxRacers} racer(s) for this tournament.`);
                    return false;
                }
            }
        }
    });
    
    // Trigger change event if there's an old value (for validation errors)
    @if(old('existing_team_id'))
        $('#existing_team_id').trigger('change');
    @endif
});
</script>
@stop

