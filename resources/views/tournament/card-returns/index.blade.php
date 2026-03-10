@extends('adminlte::page')

@section('title', 'Card Returns')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Card Returns</h1>
            <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge badge-secondary badge-lg p-2">Total: <strong id="statTotal">{{ $totalCards }}</strong></span>
            <span class="badge badge-success badge-lg p-2">Returned: <strong id="statReturned">{{ $returnedCards }}</strong></span>
            <span class="badge badge-warning badge-lg p-2">Pending: <strong id="statPending">{{ $pendingCards }}</strong></span>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Left: Input Form --}}
        <div class="col-md-5">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-undo-alt"></i> Scan Card to Return</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Focus on the input field below</li>
                            <li>Tap or scan the card</li>
                            <li>Press Enter to mark as returned</li>
                        </ul>
                    </div>

                    <form id="cardReturnForm">
                        @csrf
                        <div class="form-group">
                            <label class="d-block mb-1">Input Type <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle mb-3" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="input_type" id="type_card_code" value="card_code" checked> Card Code
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="input_type" id="type_card_no" value="card_no"> Card No
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="card_input" id="card_input_label">Card Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="card_input"
                                   placeholder="Scan or enter card code..."
                                   autofocus
                                   required>
                            <small class="form-text text-muted">
                                The input will auto-clear after successful submission.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-undo-alt"></i> Return Card
                        </button>
                    </form>

                    <div id="messageContainer"></div>
                </div>
            </div>

            {{-- Recent Returns --}}
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Recent Returns</h3>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    <div id="recentReturns">
                        <p class="text-muted text-center">No returns yet this session</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Full History Table --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Return History</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" id="tableSearch" class="form-control" placeholder="Search card / racer...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" id="historyTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Card No</th>
                                <th>Racer</th>
                                <th>Team</th>
                                <th class="text-center">Status</th>
                                <th>Returned At</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr id="row-{{ $assignment->card_id }}"
                                    class="{{ $assignment->returned_at ? 'table-success' : '' }}">
                                    <td>
                                        <span class="font-weight-bold">{{ $assignment->card->card_no ?? '-' }}</span>
                                        @if($assignment->card->card_code)
                                            <br><small class="text-muted">{{ $assignment->card->card_code }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->racer->racer_name }}</td>
                                    <td>{{ $assignment->racer->team->team_name }}</td>
                                    <td class="text-center">
                                        @if($assignment->returned_at)
                                            <span class="badge badge-success" id="badge-{{ $assignment->card_id }}">Returned</span>
                                        @else
                                            <span class="badge badge-warning" id="badge-{{ $assignment->card_id }}">Pending</span>
                                        @endif
                                    </td>
                                    <td id="returnedAt-{{ $assignment->card_id }}">
                                        {{ $assignment->returned_at ? $assignment->returned_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="text-right">
                                        @if($assignment->returned_at)
                                            <button class="btn btn-xs btn-outline-secondary undo-btn"
                                                    data-card-id="{{ $assignment->card_id }}"
                                                    data-card-no="{{ $assignment->card->card_no ?? $assignment->card->card_code }}"
                                                    title="Undo Return">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @else
                                            <span id="undo-{{ $assignment->card_id }}"></span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No cards assigned in this tournament.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($totalCards > 0)
                    <div class="card-footer text-muted small">
                        {{ $returnedCards }} of {{ $totalCards }} cards returned
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .badge-lg {
        font-size: 0.9rem;
    }

    .return-item {
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
        background: #f8f9fa;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to   { transform: translateX(0); opacity: 1; }
    }

    .return-item h6 {
        margin-bottom: 4px;
        color: #28a745;
        font-weight: 600;
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
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .table-success td { background-color: #d4edda !important; }

    #historyTable tr { transition: background-color 0.4s ease; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const form          = $('#cardReturnForm');
    const cardInput     = $('#card_input');
    const cardInputLabel = $('#card_input_label');
    const messageContainer = $('#messageContainer');
    const recentReturns = $('#recentReturns');

    let statReturned = {{ $returnedCards }};
    let statPending  = {{ $pendingCards }};

    // Auto-focus
    cardInput.focus();

    // Toggle label/placeholder on input type change
    $('input[name="input_type"]').on('change', function() {
        if ($(this).val() === 'card_no') {
            cardInputLabel.html('Card No <span class="text-danger">*</span>');
            cardInput.attr('placeholder', 'Enter card number...');
        } else {
            cardInputLabel.html('Card Code <span class="text-danger">*</span>');
            cardInput.attr('placeholder', 'Scan or enter card code...');
        }
        cardInput.val('').focus();
    });

    // Form submit
    form.on('submit', function(e) {
        e.preventDefault();

        const inputType  = $('input[name="input_type"]:checked').val();
        const inputValue = cardInput.val().trim();

        if (!inputValue) {
            showMessage('Please enter a ' + (inputType === 'card_no' ? 'card number' : 'card code') + '.', 'error');
            return;
        }

        cardInput.prop('disabled', true);

        const postData = {
            _token:     '{{ csrf_token() }}',
            input_type: inputType,
        };
        postData[inputType] = inputValue;

        $.ajax({
            url:    '{{ route("tournament.card-returns.store") }}',
            method: 'POST',
            data:   postData,
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    addRecentReturn(response.data);
                    updateTableRow(response.data);
                    updateStats(1);
                    cardInput.val('');
                    playSuccessSound();
                } else {
                    showMessage(response.message, 'error');
                }
                cardInput.prop('disabled', false).focus();
            },
            error: function(xhr) {
                let errorMessage = 'Failed to process card return. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstError = Object.values(xhr.responseJSON.errors)[0];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                }
                showMessage(errorMessage, 'error');
                cardInput.val('');
                cardInput.prop('disabled', false).focus();
            }
        });
    });

    // Undo button (delegated, handles dynamically added buttons too)
    $(document).on('click', '.undo-btn', function() {
        const btn    = $(this);
        const cardId = btn.data('card-id');
        const cardNo = btn.data('card-no');

        if (!confirm('Undo return for card ' + cardNo + '?')) return;

        $.ajax({
            url:    '{{ route("tournament.card-returns.undo") }}',
            method: 'POST',
            data:   { _token: '{{ csrf_token() }}', card_id: cardId },
            success: function(response) {
                if (response.success) {
                    // Revert row
                    const row = $('#row-' + cardId);
                    row.removeClass('table-success');
                    $('#badge-' + cardId).removeClass('badge-success').addClass('badge-warning').text('Pending');
                    $('#returnedAt-' + cardId).text('-');
                    btn.remove();
                    $('#undo-' + cardId).html('');
                    updateStats(-1);
                }
            },
            error: function() {
                alert('Failed to undo return. Please try again.');
            }
        });
    });

    // Table search
    $('#tableSearch').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('#historyTable tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    });

    // Re-focus on page click
    $(document).on('click', function(e) {
        if (!$(e.target).is('input, button, a')) {
            cardInput.focus();
        }
    });

    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-danger-custom';
        const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
        messageContainer.html(`<div class="${alertClass}">${icon} ${message}</div>`);
        if (type === 'success') {
            setTimeout(function() {
                messageContainer.fadeOut(300, function() { messageContainer.html('').show(); });
            }, 3000);
        }
    }

    function addRecentReturn(data) {
        if (recentReturns.find('.return-item').length === 0) {
            recentReturns.html('');
        }
        const html = `
            <div class="return-item">
                <h6><i class="fas fa-check-circle"></i> Card Returned</h6>
                <div><strong>${data.racer_name}</strong> &mdash; ${data.team_name}</div>
                <div class="text-muted small">Card No: ${data.card_no ?? data.card_code} &bull; ${data.returned_at}</div>
            </div>`;
        recentReturns.prepend(html);
        const items = recentReturns.find('.return-item');
        if (items.length > 10) items.last().fadeOut(300, function() { $(this).remove(); });
    }

    function updateTableRow(data) {
        // Find row by card_no text match
        $('#historyTable tbody tr').each(function() {
            const cardNoCell = $(this).find('td:first .font-weight-bold').text().trim();
            const cardCodeSmall = $(this).find('td:first small').text().trim();
            if (cardNoCell === data.card_no || cardCodeSmall === data.card_code) {
                const cardId = $(this).attr('id').replace('row-', '');
                $(this).addClass('table-success');
                $('#badge-' + cardId).removeClass('badge-warning').addClass('badge-success').text('Returned');
                $('#returnedAt-' + cardId).text(data.returned_at.substring(0, 16));
                $('#undo-' + cardId).html(
                    `<button class="btn btn-xs btn-outline-secondary undo-btn"
                             data-card-id="${cardId}"
                             data-card-no="${data.card_no ?? data.card_code}"
                             title="Undo Return">
                        <i class="fas fa-undo"></i>
                    </button>`
                );
            }
        });
    }

    function updateStats(delta) {
        statReturned += delta;
        statPending  -= delta;
        $('#statReturned').text(statReturned);
        $('#statPending').text(statPending);
    }

    function playSuccessSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 800;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.1);
        } catch (e) {}
    }
});
</script>
@stop
