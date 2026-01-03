@extends('adminlte::page')

@section('title', 'Team Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Team Details</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <div>
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editTeamModal">
            <i class="fas fa-edit"></i> Edit Team
        </button>
        <a href="{{ route('tournament.teams.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Teams
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Team Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Team Name</th>
                        <td>{{ $team->team_name }}</td>
                    </tr>
                    <tr>
                        <th>Total Racers</th>
                        <td>
                            <span class="badge badge-info">{{ $racers->count() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Active Racers in Tournament</th>
                        <td>
                            <span class="badge badge-success">{{ count($activeRacerIds) }} </span> / 
                            <span class="badge badge-warning"> {{ $tournament->max_racer_per_team ?? 1 }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Racers in Team</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                        data-target="#addRacerModal">
                        <i class="fas fa-plus"></i> Add Racer
                    </button>
                </div>
            </div>
            <div class="card-body" id="racers-list">
                @if($racers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Racer Name</th>
                                    <th>Status</th>
                                    {{-- <th>Image</th> --}}
                                    <th>Cards</th>
                                    {{-- <th>Created At</th> --}}
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($racers as $racer)
                                    <tr id="racer-row-{{ $racer->id }}">
                                        <td>{{ $racer->racer_name }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm toggle-status-btn {{ in_array($racer->id, $activeRacerIds) ? 'btn-success' : 'btn-secondary' }}"
                                                data-racer-id="{{ $racer->id }}" data-racer-name="{{ $racer->racer_name }}"
                                                data-is-active="{{ in_array($racer->id, $activeRacerIds) ? 'true' : 'false' }}"
                                                title="Click to toggle status">
                                                @if(in_array($racer->id, $activeRacerIds))
                                                    <i class="fas fa-check-circle"></i> Active
                                                @else
                                                    <i class="fas fa-times-circle"></i> Inactive
                                                @endif
                                            </button>
                                        </td>
                                        {{-- <td>
                                            @if($racer->image_url)
                                                <img src="{{ $racer->image_url }}" alt="{{ $racer->racer_name }}"
                                                    class="img-circle img-size-32">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td> --}}
                                        <td>
                                            <span class="badge badge-info">{{ $racer->cards_count }}</span>
                                        </td>
                                        {{-- <td>{{ $racer->created_at->format('Y-m-d H:i') }}</td> --}}
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning edit-racer-btn"
                                                    data-racer-id="{{ $racer->id }}" data-racer-name="{{ $racer->racer_name }}"
                                                    data-card-code="{{ $racer->cards->first()->card_code ?? '' }}"
                                                    data-card-id="{{ $racer->cards->first()->id ?? '' }}" title="Edit Racer">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-racer-btn"
                                                    data-racer-id="{{ $racer->id }}" data-racer-name="{{ $racer->racer_name }}"
                                                    data-card-count="{{ $racer->cards_count }}" title="Delete Racer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">No racers in this team yet.</p>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addRacerModal">
                            <i class="fas fa-user-plus"></i> Add First Racer
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Racer Modal -->
<div class="modal fade" id="addRacerModal" tabindex="-1" role="dialog" aria-labelledby="addRacerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRacerModalLabel">Add New Racer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRacerForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="team_id" value="{{ $team->id }}">

                    @php
                        $currentRacerCount = $racers->count();
                        $maxRacersPerTeam = $tournament->max_racer_per_team ?? 1;
                        $canAddMore = $currentRacerCount < $maxRacersPerTeam;
                    @endphp

                    @if(!$canAddMore)
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Limit Reached!</strong> This team has reached the maximum limit of
                            <strong>{{ $maxRacersPerTeam }}</strong> racer(s) per team.
                            You cannot add more racers to this team.
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            Team has <strong>{{ $currentRacerCount }}</strong> of <strong>{{ $maxRacersPerTeam }}</strong>
                            racer(s).
                            @if($maxRacersPerTeam - $currentRacerCount == 1)
                                <strong>1 more racer</strong> can be added.
                            @else
                                <strong>{{ $maxRacersPerTeam - $currentRacerCount }} more racers</strong> can be added.
                            @endif
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="racer_name">Racer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="racer_name" name="racer_name" required {{ !$canAddMore ? 'disabled' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="image">Racer Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" {{ !$canAddMore ? 'disabled' : '' }}>
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Optional: JPEG, PNG, JPG, GIF, SVG (Max: 2MB)</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="card_code">Card Code</label>
                        <input type="text" class="form-control" id="card_code" name="card_code"
                            placeholder="Optional: Auto-assign card to racer" {{ !$canAddMore ? 'disabled' : '' }}>
                        <small class="form-text text-muted">If provided, a card will be automatically created and
                            assigned to this racer with ACTIVE status.</small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitRacerBtn" {{ !$canAddMore ? 'disabled' : '' }}>
                        <i class="fas fa-save"></i> Add Racer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Racer Modal -->
<div class="modal fade" id="editRacerModal" tabindex="-1" role="dialog" aria-labelledby="editRacerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRacerModalLabel">Edit Racer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editRacerForm">
                @csrf
                <input type="hidden" id="edit_racer_id" name="racer_id">
                <input type="hidden" id="edit_card_id" name="card_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_racer_name">Racer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_racer_name" name="racer_name" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_card_code">Card Code</label>
                        <input type="text" class="form-control" id="edit_card_code" name="card_code"
                            placeholder="Enter card code">
                        <small class="form-text text-muted">
                            <span id="card-status-text"></span>
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateRacerBtn">
                        <i class="fas fa-save"></i> Update Racer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" role="dialog" aria-labelledby="editTeamModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeamModalLabel">Edit Team</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editTeamForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_team_name">Team Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_team_name" name="team_name"
                            value="{{ $team->team_name }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updateTeamBtn">
                        <i class="fas fa-save"></i> Update Team
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {

        // Handle edit team form submission
        $('#editTeamForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = $('#updateTeamBtn');
            const originalBtnText = submitBtn.html();

            // Reset validation states
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            const formData = {
                team_name: $('#edit_team_name').val(),
                _method: 'PUT'
            };

            $.ajax({
                url: '{{ route("tournament.teams.update", $team->id) }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Close modal
                    $('#editTeamModal').modal('hide');

                    // Show success message and reload
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Team updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Reload the page to refresh team info after a short delay
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors || {};
                        $.each(errors, function (field, messages) {
                            const input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            const feedback = input.siblings('.invalid-feedback');
                            if (feedback.length) {
                                feedback.text(messages[0]);
                            } else {
                                input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                            }
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please fix the validation errors.'
                        });
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while updating the team.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        });

        // Reset form when edit modal is closed
        $('#editTeamModal').on('hidden.bs.modal', function () {
            const form = $('#editTeamForm');
            form[0].reset();
            $('#edit_team_name').val('{{ $team->team_name }}');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            $('#updateTeamBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Team');
        });

        // Handle toggle racer status button
        $('.toggle-status-btn').on('click', function () {
            const btn = $(this);
            const racerId = btn.data('racer-id');
            const racerName = btn.data('racer-name');
            const isActive = btn.data('is-active') === true || btn.data('is-active') === 'true';
            const originalHtml = btn.html();

            // Confirm before toggling
            const action = isActive ? 'deactivate' : 'activate';
            Swal.fire({
                title: 'Confirm',
                text: `Are you sure you want to ${action} racer "${racerName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, ' + action + ' it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

                    $.ajax({
                        url: '/tournament/racers/' + racerId + '/toggle-status',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                // Update button appearance
                                const newIsActive = response.is_active === true || response.is_active === 'true';
                                btn.data('is-active', newIsActive ? 'true' : 'false');

                                if (newIsActive) {
                                    btn.removeClass('btn-secondary').addClass('btn-success');
                                    btn.html('<i class="fas fa-check-circle"></i> Active');
                                } else {
                                    btn.removeClass('btn-success').addClass('btn-secondary');
                                    btn.html('<i class="fas fa-times-circle"></i> Inactive');
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: `Racer "${racerName}" ${newIsActive ? 'activated' : 'deactivated'} successfully!`,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Reload page to update active racer count after a short delay
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'An error occurred.'
                                });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function (xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred while toggling racer status.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Handle edit racer button
        $('.edit-racer-btn').on('click', function () {
            const racerId = $(this).data('racer-id');
            const racerName = $(this).data('racer-name');
            const cardCode = $(this).data('card-code');
            const cardId = $(this).data('card-id');

            // Populate modal fields
            $('#edit_racer_id').val(racerId);
            $('#edit_racer_name').val(racerName);
            $('#edit_card_code').val(cardCode);
            $('#edit_card_id').val(cardId);

            // Update card status text
            if (cardCode) {
                $('#card-status-text').html('<i class="fas fa-info-circle text-info"></i> Current card: <strong>' + cardCode + '</strong>. Leave empty to remove card or enter new code to update.');
            } else {
                $('#card-status-text').html('<i class="fas fa-info-circle text-muted"></i> No card assigned. Enter a card code to create and assign one.');
            }

            // Reset validation states
            $('#editRacerForm').find('.is-invalid').removeClass('is-invalid');
            $('#editRacerForm').find('.invalid-feedback').text('');

            // Show modal
            $('#editRacerModal').modal('show');
        });

        // Handle delete racer button
        $('.delete-racer-btn').on('click', function () {
            const racerId = $(this).data('racer-id');
            const racerName = $(this).data('racer-name');
            const cardCount = $(this).data('card-count');

            let confirmMessage = 'Are you sure you want to delete racer "' + racerName + '"?';
            if (cardCount > 0) {
                confirmMessage += ' This will also delete ' + cardCount + ' associated card(s).';
            }

            Swal.fire({
                title: 'Confirm Deletion',
                text: confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteBtn = $('.delete-racer-btn[data-racer-id="' + racerId + '"]');
                    const originalHtml = deleteBtn.html();

                    // Disable button and show loading
                    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '/tournament/racers/' + racerId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                // Remove the row with animation
                                $('#racer-row-' + racerId).fadeOut(300, function () {
                                    $(this).remove();

                                    // Check if table is empty
                                    if ($('#racers-list tbody tr').length === 0) {
                                        $('#racers-list').html(
                                            '<div class="text-center py-5">' +
                                            '<p class="text-muted">No racers in this team yet.</p>' +
                                            '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#addRacerModal">' +
                                            '<i class="fas fa-user-plus"></i> Add First Racer' +
                                            '</button>' +
                                            '</div>'
                                        );
                                    }
                                });

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message || 'Racer deleted successfully!',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'An error occurred.'
                                });
                                deleteBtn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function (xhr) {
                            const message = xhr.responseJSON?.message || 'An error occurred while deleting the racer.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                            deleteBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Handle file input label
        $('.custom-file-input').on('change', function () {
            let fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
        });

        // Handle form submission
        $('#addRacerForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = $('#submitRacerBtn');
            const originalBtnText = submitBtn.html();

            // Reset validation states
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            // Create FormData for file upload
            const formData = new FormData(this);

            // Add CSRF token (already in form, but ensure it's there)
            if (!formData.has('_token')) {
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            }

            $.ajax({
                url: '{{ route("tournament.racers.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        // Close modal
                        $('#addRacerModal').modal('hide');

                        // Reset form
                        form[0].reset();
                        $('.custom-file-label').html('Choose file');

                        // Show success message and reload
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Racer added successfully!',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Reload the page to refresh racers list after a short delay
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'An error occurred.'
                        });
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors || {};
                        $.each(errors, function (field, messages) {
                            const input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            const feedback = input.siblings('.invalid-feedback');
                            if (feedback.length) {
                                feedback.text(messages[0]);
                            } else {
                                input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                            }
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please fix the validation errors.'
                        });
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while adding the racer.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        });

        // Reset form when add modal is closed
        $('#addRacerModal').on('hidden.bs.modal', function () {
            const form = $('#addRacerForm');
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            $('.custom-file-label').html('Choose file');
            $('#submitRacerBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Add Racer');
        });

        // Handle edit racer form submission
        $('#editRacerForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const racerId = $('#edit_racer_id').val();
            const submitBtn = $('#updateRacerBtn');
            const originalBtnText = submitBtn.html();

            // Reset validation states
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            // Disable submit button
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            const formData = {
                racer_name: $('#edit_racer_name').val(),
                card_code: $('#edit_card_code').val(),
                card_id: $('#edit_card_id').val()
            };

            $.ajax({
                url: '/tournament/racers/' + racerId + '/update-with-card',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        // Close modal
                        $('#editRacerModal').modal('hide');

                        // Show success message and reload
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Racer updated successfully!',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Reload the page to refresh racers list after a short delay
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'An error occurred.'
                        });
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors || {};
                        $.each(errors, function (field, messages) {
                            const input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            const feedback = input.siblings('.invalid-feedback');
                            if (feedback.length) {
                                feedback.text(messages[0]);
                            }
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please fix the validation errors.'
                        });
                    } else {
                        const message = xhr.responseJSON?.message || 'An error occurred while updating the racer.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                }
            });
        });

        // Reset form when edit modal is closed
        $('#editRacerModal').on('hidden.bs.modal', function () {
            const form = $('#editRacerForm');
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            $('#updateRacerBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Racer');
        });
    });
</script>
@stop