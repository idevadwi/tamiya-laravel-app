@extends('adminlte::page')

@section('title', 'Card Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Card Details</h1>
        <p class="text-muted mb-0">Tournament: <strong>{{ $tournament->tournament_name }}</strong></p>
    </div>
    <div>
        <a href="{{ route('tournament.cards.edit', $card->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('tournament.cards.index') }}" class="btn btn-default ml-2">
            <i class="fas fa-arrow-left"></i> Back to Cards
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Card Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Card No</th>
                        <td>
                            @if($card->card_no)
                                <span class="badge badge-secondary" style="font-size:1rem;">{{ $card->card_no }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Card Code</th>
                        <td><code>{{ $card->card_code }}</code></td>
                    </tr>
                    <tr>
                        <th>Master Status</th>
                        <td>
                            <span class="badge badge-{{ $card->status == 'ACTIVE' ? 'success' : ($card->status == 'BANNED' ? 'danger' : 'warning') }}">
                                {{ $card->status }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assignment in This Tournament</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Racer</th>
                        <td>
                            @if($assignment->racer)
                                <a href="{{ route('tournament.racers.show', $assignment->racer->id) }}">
                                    {{ $assignment->racer->racer_name }}
                                </a>
                                @if($assignment->racer->image_url)
                                    <div class="mt-2">
                                        <img src="{{ $assignment->racer->image_url }}"
                                             alt="{{ $assignment->racer->racer_name }}"
                                             class="img-thumbnail" style="max-width: 80px;">
                                    </div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Team</th>
                        <td>
                            @if($assignment->racer && $assignment->racer->team)
                                <a href="{{ route('tournament.teams.show', $assignment->racer->team->id) }}">
                                    <span class="badge badge-info">{{ $assignment->racer->team->team_name }}</span>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Assignment Status</th>
                        <td>
                            @if($assignment->status === 'ACTIVE')
                                <span class="badge badge-success">{{ $assignment->status }}</span>
                            @elseif($assignment->status === 'LOST')
                                <span class="badge badge-warning">{{ $assignment->status }}</span>
                            @elseif($assignment->status === 'BANNED')
                                <span class="badge badge-danger">{{ $assignment->status }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $assignment->status }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Assigned At</th>
                        <td>{{ $assignment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <form action="{{ route('tournament.cards.destroy', $card->id) }}" method="POST" id="removeForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger btn-sm" id="btnRemove">
                        <i class="fas fa-trash"></i> Remove Assignment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Confirm remove modal --}}
<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Remove Assignment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Remove card <strong>{{ $card->card_no ?? $card->card_code }}</strong> from this tournament?</p>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle"></i>
                    <small>The card itself will not be deleted and can be reassigned in another tournament.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>
    document.getElementById('btnRemove').addEventListener('click', function () {
        $('#removeModal').modal('show');
    });
    document.getElementById('confirmRemoveBtn').addEventListener('click', function () {
        document.getElementById('removeForm').submit();
    });
</script>
@stop
