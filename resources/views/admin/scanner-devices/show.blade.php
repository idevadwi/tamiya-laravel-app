@extends('adminlte::page')

@section('title', 'Scanner Device Details')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Device: {{ $scannerDevice->device_name }}</h1>
    <div>
        <a href="{{ route('admin.scanner-devices.edit', $scannerDevice->id) }}" class="btn btn-warning mr-2">
            <i class="fas fa-edit"></i> Edit Device
        </a>
        <a href="{{ route('admin.scanner-devices.index') }}" class="btn btn-default">Back to List</a>
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

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Device Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Device Code</th>
                        <td><code>{{ $scannerDevice->device_code }}</code></td>
                    </tr>
                    <tr>
                        <th>Device Name</th>
                        <td>{{ $scannerDevice->device_name }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($scannerDevice->status == 'ACTIVE')
                                <span class="badge badge-success">Active</span>
                            @elseif($scannerDevice->status == 'INACTIVE')
                                <span class="badge badge-warning">Inactive</span>
                            @else
                                <span class="badge badge-info">Maintenance</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Firmware Version</th>
                        <td>{{ $scannerDevice->firmware_version ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Last Seen</th>
                        <td>
                            @if($scannerDevice->last_seen_at)
                                {{ $scannerDevice->last_seen_at->format('Y-m-d H:i:s') }}
                                <br><small class="text-muted">{{ $scannerDevice->last_seen_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $scannerDevice->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Notes</th>
                        <td>{{ $scannerDevice->notes ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card {{ $scannerDevice->tournament ? 'card-success' : 'card-secondary' }}">
            <div class="card-header">
                <h3 class="card-title">Tournament Link</h3>
            </div>
            <div class="card-body">
                @if($scannerDevice->tournament)
                    <div class="alert alert-success">
                        <i class="fas fa-link"></i> Currently linked to:
                        <strong>{{ $scannerDevice->tournament->tournament_name }}</strong>
                        <br>
                        <small>Status: {{ $scannerDevice->tournament->status }}</small>
                    </div>

                    <form action="{{ route('admin.scanner-devices.unlink', $scannerDevice->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Unlink this device from the tournament?')">
                            <i class="fas fa-unlink"></i> Unlink from Tournament
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This device is not linked to any tournament.
                        <br>
                        <small>The device cannot create races until it is linked.</small>
                    </div>
                @endif

                <hr>

                <h5>Link to Different Tournament</h5>
                <form action="{{ route('admin.scanner-devices.link', $scannerDevice->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <select class="form-control" name="tournament_id" required>
                            <option value="">-- Select Tournament --</option>
                            @foreach($tournaments as $tournament)
                                <option value="{{ $tournament->id }}">{{ $tournament->tournament_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-link"></i> Link to Tournament
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-code"></i> API Endpoint</h3>
            </div>
            <div class="card-body">
                <p><strong>Endpoint:</strong></p>
                <code>POST {{ url('/api/scanner/race') }}</code>

                <p class="mt-3"><strong>Headers:</strong></p>
                <pre>X-Device-Code: {{ $scannerDevice->device_code }}
Content-Type: application/json</pre>

                <p><strong>Body:</strong></p>
                <pre>{"card_code": "CARD123"}</pre>

                <p><strong>Test with cURL:</strong></p>
                <pre style="font-size: 11px;">curl -X POST {{ url('/api/scanner/race') }} \
  -H "Content-Type: application/json" \
  -H "X-Device-Code: {{ $scannerDevice->device_code }}" \
  -d '{"card_code":"YOUR_CARD_CODE"}'</pre>
            </div>
        </div>
    </div>
</div>
@stop
