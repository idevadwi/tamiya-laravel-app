@extends('adminlte::page')

@section('title', 'Best Times')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Best Times</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createBestTimeModal">
            <i class="fas fa-plus"></i> Record New Best Time
        </button>
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

    <!-- Filters Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Best Times</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('best_times.index') }}" class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="track">Track</label>
                        <select name="track" id="track" class="form-control">
                            <option value="">All Tracks</option>
                            @foreach($tracks as $track)
                                <option value="{{ $track }}" {{ request('track') == $track ? 'selected' : '' }}>
                                    Track {{ $track }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="scope">Scope</label>
                        <select name="scope" id="scope" class="form-control">
                            <option value="">All Scopes</option>
                            <option value="OVERALL" {{ request('scope') == 'OVERALL' ? 'selected' : '' }}>Overall</option>
                            <option value="SESSION" {{ request('scope') == 'SESSION' ? 'selected' : '' }}>Session</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="session_number">Session</label>
                        <select name="session_number" id="session_number" class="form-control">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session }}" {{ request('session_number') == $session ? 'selected' : '' }}>
                                    Session {{ $session }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="team_id">Team</label>
                        <select name="team_id" id="team_id" class="form-control">
                            <option value="">All Teams</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->team_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            @if(request()->anyFilled(['track', 'scope', 'session_number', 'team_id']))
                                <a href="{{ route('best_times.index') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Best Times Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Best Race Lap Times</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Track</th>
                            <th>Timer</th>
                            <th>Scope</th>
                            <th>Session</th>
                            <th>Recorded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bestTimes as $bestTime)
                            <tr>
                                <td>
                                    <strong>{{ $bestTime->team->team_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">Track {{ $bestTime->track }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success" style="font-size: 1.1em; font-weight: bold;">
                                        {{ $bestTime->timer }}
                                    </span>
                                </td>
                                <td>
                                    @if($bestTime->scope === 'OVERALL')
                                        <span class="badge badge-primary">
                                            <i class="fas fa-trophy"></i> OVERALL
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> SESSION
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($bestTime->session_number)
                                        <span class="badge badge-secondary">
                                            Session {{ $bestTime->session_number }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $bestTime->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-warning edit-best-time" 
                                                data-id="{{ $bestTime->id }}"
                                                data-team-id="{{ $bestTime->team_id }}"
                                                data-track="{{ $bestTime->track }}"
                                                data-timer="{{ $bestTime->timer }}"
                                                data-scope="{{ $bestTime->scope }}"
                                                data-session="{{ $bestTime->session_number }}"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('best_times.destroy', $bestTime->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this best time record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No best times recorded yet. 
                                    <a href="#" data-toggle="modal" data-target="#createBestTimeModal">Record one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bestTimes->hasPages())
            <div class="card-footer">
                {{ $bestTimes->links() }}
            </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="alert alert-info">
        <h5><i class="icon fas fa-info-circle"></i> About Best Times</h5>
        <ul class="mb-0">
            <li><strong>Overall:</strong> The best time throughout the entire tournament for a team on a specific track.</li>
            <li><strong>Session:</strong> The best time for a specific session (currently Session {{ $tournament->current_bto_session }}).</li>
            <li><strong>Auto-Update:</strong> When a session time beats the overall time, the overall time is automatically updated.</li>
            <li><strong>Timer Format:</strong> Times are recorded as seconds:milliseconds (e.g., 14:20, 13:11).</li>
            <li><strong>Validation:</strong> You can only record times that are better than existing records.</li>
        </ul>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createBestTimeModal" tabindex="-1" role="dialog" aria-labelledby="createBestTimeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('best_times.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title" id="createBestTimeModalLabel">
                            <i class="fas fa-plus"></i> Record New Best Time
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_team_id">Team <span class="text-danger">*</span></label>
                                    <select name="team_id" id="create_team_id" class="form-control" required>
                                        <option value="">Select Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_track">Track <span class="text-danger">*</span></label>
                                    <select name="track" id="create_track" class="form-control" required>
                                        <option value="">Select Track</option>
                                        @foreach($tracks as $track)
                                            <option value="{{ $track }}">Track {{ $track }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_timer">Timer (seconds:milliseconds) <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="create_timer" 
                                           name="timer" 
                                           placeholder="e.g., 14:20"
                                           pattern="\d{1,2}:\d{2}"
                                           required>
                                    <small class="form-text text-muted">Format: MM:SS (e.g., 14:20)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_scope">Scope <span class="text-danger">*</span></label>
                                    <select name="scope" id="create_scope" class="form-control" required>
                                        <option value="">Select Scope</option>
                                        <option value="OVERALL">Overall</option>
                                        <option value="SESSION">Session {{ $tournament->current_bto_session }}</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <strong>Overall:</strong> Best for entire tournament<br>
                                        <strong>Session:</strong> Best for current session ({{ $tournament->current_bto_session }})
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Note:</strong> 
                            You can only record times that are better than existing records for the same track and scope.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Best Time
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editBestTimeModal" tabindex="-1" role="dialog" aria-labelledby="editBestTimeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="editBestTimeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editBestTimeModalLabel">
                            <i class="fas fa-edit"></i> Edit Best Time
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_team_id">Team <span class="text-danger">*</span></label>
                                    <select name="team_id" id="edit_team_id" class="form-control" required>
                                        <option value="">Select Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_track">Track <span class="text-danger">*</span></label>
                                    <select name="track" id="edit_track" class="form-control" required>
                                        <option value="">Select Track</option>
                                        @foreach($tracks as $track)
                                            <option value="{{ $track }}">Track {{ $track }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_timer">Timer (seconds:milliseconds) <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="edit_timer" 
                                           name="timer" 
                                           placeholder="e.g., 14:20"
                                           pattern="\d{1,2}:\d{2}"
                                           required>
                                    <small class="form-text text-muted">Format: MM:SS (e.g., 14:20)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_scope">Scope <span class="text-danger">*</span></label>
                                    <select name="scope" id="edit_scope" class="form-control" required>
                                        <option value="">Select Scope</option>
                                        <option value="OVERALL">Overall</option>
                                        <option value="SESSION">Session</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="edit_session_number_group" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_session_number">Session Number <span class="text-danger">*</span></label>
                                    <select name="session_number" id="edit_session_number" class="form-control">
                                        <option value="">Select Session</option>
                                        @for($i = 1; $i <= $tournament->bto_session_number; $i++)
                                            <option value="{{ $i }}">Session {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> Note:</strong> 
                            You can only update to times that are better than existing records for the same track and scope.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Best Time
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Handle edit button click
        $('.edit-best-time').on('click', function() {
            var id = $(this).data('id');
            var teamId = $(this).data('team-id');
            var track = $(this).data('track');
            var timer = $(this).data('timer');
            var scope = $(this).data('scope');
            var session = $(this).data('session');
            
            // Set form action
            $('#editBestTimeForm').attr('action', '/best_times/' + id);
            
            // Populate form fields
            $('#edit_team_id').val(teamId);
            $('#edit_track').val(track);
            $('#edit_timer').val(timer);
            $('#edit_scope').val(scope);
            
            // Handle session number visibility
            if (scope === 'SESSION') {
                $('#edit_session_number_group').show();
                $('#edit_session_number').prop('required', true);
                $('#edit_session_number').val(session);
            } else {
                $('#edit_session_number_group').hide();
                $('#edit_session_number').prop('required', false);
            }
            
            // Show modal
            $('#editBestTimeModal').modal('show');
        });
        
        // Handle scope change in edit modal
        $('#edit_scope').on('change', function() {
            if ($(this).val() === 'SESSION') {
                $('#edit_session_number_group').show();
                $('#edit_session_number').prop('required', true);
            } else {
                $('#edit_session_number_group').hide();
                $('#edit_session_number').prop('required', false);
                $('#edit_session_number').val('');
            }
        });
        
        // Reset create modal on close
        $('#createBestTimeModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
        });
        
        // Reset edit modal on close
        $('#editBestTimeModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $('#edit_session_number_group').hide();
        });
    });
</script>
@stop
