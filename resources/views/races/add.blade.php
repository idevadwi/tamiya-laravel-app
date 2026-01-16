@extends('adminlte::page')

@section('title', 'Add Race')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Add Race</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
            <p class="text-muted mb-0">Current Stage: <strong>{{ $tournament->current_stage }}</strong></p>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Tap Card to Add Race</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Focus on the input field below</li>
                            <li>Tap or scan the card</li>
                            <li>Press Enter to submit</li>
                            <li>The race will be automatically assigned to Stage {{ $tournament->current_stage + 1 }}</li>
                        </ul>
                    </div>

                    <form id="addRaceForm">
                        @csrf
                        <div class="form-group">
                            <label for="card_code">Card Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="card_code"
                                   name="card_code"
                                   placeholder="Scan or enter card code..."
                                   autofocus
                                   required>
                            <small class="form-text text-muted">
                                The input will auto-clear after successful submission.
                            </small>
                        </div>
                    </form>

                    <div id="messageContainer"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Recent Submissions</h3>
                </div>
                <div class="card-body" style="max-height: 630px; overflow-y: auto;">
                    <div id="recentSubmissions">
                        <p class="text-muted text-center">No submissions yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .submission-item {
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
        background: #f8f9fa;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(-20px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .submission-item h5 {
        margin-bottom: 8px;
        color: #28a745;
        font-weight: 600;
        font-size: 16px;
    }

    .submission-item .race-info {
        font-size: 16px;
        line-height: 1.8;
        color: #495057;
    }

    .submission-item .highlight {
        font-weight: 700;
        color: #212529;
    }
    .submission-item .highlight-race {
        font-size: 24px;
        font-weight: 700;
        color: #212529;
    }

    .submission-item .race-location {
        color: #6c757d;
        font-size: 14px;
        margin-top: 5px;
    }

    .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .alert-success-custom {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        padding: 12px 20px;
        border-radius: 6px;
        margin-bottom: 15px;
        animation: fadeIn 0.3s ease-out;
    }

    .alert-danger-custom {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
        padding: 12px 20px;
        border-radius: 6px;
        margin-bottom: 15px;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const form = $('#addRaceForm');
    const cardCodeInput = $('#card_code');
    const messageContainer = $('#messageContainer');
    const recentSubmissions = $('#recentSubmissions');
    let totalRacesInStage = {{ $totalRacesInStage }};

    // Auto-focus on card code input
    cardCodeInput.focus();

    // Handle form submission
    form.on('submit', function(e) {
        e.preventDefault();

        const cardCode = cardCodeInput.val().trim();

        if (!cardCode) {
            showMessage('Please enter a card code.', 'error');
            return;
        }

        // Disable input during submission
        cardCodeInput.prop('disabled', true);

        // Submit via AJAX
        $.ajax({
            url: '{{ route("tournament.races.addByCard") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                card_code: cardCode
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage(response.message, 'success');

                    // Add to recent submissions
                    addSubmission(response.data);

                    // Clear input
                    cardCodeInput.val('');

                    // Play success sound (optional)
                    playSuccessSound();
                } else {
                    showMessage(response.message, 'error');
                }

                // Re-enable and focus input
                cardCodeInput.prop('disabled', false).focus();
            },
            error: function(xhr) {
                let errorMessage = 'Failed to create race. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;

                    // If card not found, append the card number to the error message
                    if (errorMessage.toLowerCase().includes('card') &&
                        (errorMessage.toLowerCase().includes('not found') ||
                         errorMessage.toLowerCase().includes('tidak ditemukan'))) {
                        errorMessage = `${errorMessage} (Card: ${cardCode})`;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const firstError = Object.values(errors)[0];
                    if (Array.isArray(firstError)) {
                        errorMessage = firstError[0];
                    } else {
                        errorMessage = firstError;
                    }

                    // If card not found error, append the card number
                    if (errorMessage.toLowerCase().includes('card') &&
                        (errorMessage.toLowerCase().includes('not found') ||
                         errorMessage.toLowerCase().includes('tidak ditemukan'))) {
                        errorMessage = `${errorMessage} (Card: ${cardCode})`;
                    }
                }

                showMessage(errorMessage, 'error');

                // Clear input on error
                cardCodeInput.val('');

                // Re-enable and focus input
                cardCodeInput.prop('disabled', false).focus();
            }
        });
    });

    // Function to show message
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-danger-custom';
        const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';

        const alertHtml = `
            <div class="${alertClass}">
                ${icon} ${message}
            </div>
        `;

        messageContainer.html(alertHtml);

        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(function() {
                messageContainer.fadeOut(300, function() {
                    messageContainer.html('').show();
                });
            }, 3000);
        }
    }

    // Function to add submission to recent list
    function addSubmission(data) {
        // Update total races count from server response
        totalRacesInStage = data.total_races_in_stage;

        // Remove "no submissions" message if exists
        const firstSubmission = recentSubmissions.find('.submission-item').length === 0;
        if (firstSubmission) {
            recentSubmissions.html('');
        }

        const raceLocation = `${data.race_no} ${data.lane}`;

        const submissionHtml = `
            <div class="submission-item">
                <h5><i class="fas fa-check-circle"></i> Masukan ke ${totalRacesInStage}</h5>
                <div class="race-info">
                    <div class="highlight-race">Race: ${raceLocation}</div>
                    <span class="highlight-race">${data.team_name}</span> - <span class="highlight-race">${data.racer_name}</span>
                </div>
            </div>
        `;

        // Prepend to recent submissions (newest first)
        recentSubmissions.prepend(submissionHtml);

        // Keep only the last 10 submissions
        const items = recentSubmissions.find('.submission-item');
        if (items.length > 10) {
            items.last().fadeOut(300, function() {
                $(this).remove();
            });
        }
    }

    // Function to play success sound (optional)
    function playSuccessSound() {
        // You can add an audio element or use Web Audio API
        // For now, we'll use a simple beep if available
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Audio not supported or failed
            console.log('Audio playback not supported');
        }
    }

    // Re-focus on input when clicking anywhere on the page
    $(document).on('click', function(e) {
        if (!$(e.target).is('input, button, a')) {
            cardCodeInput.focus();
        }
    });
});
</script>
@stop
