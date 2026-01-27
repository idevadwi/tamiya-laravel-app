@extends('adminlte::page')

@section('title', 'Edit Scanner Device')

@section('content_header')
<h1>Edit Scanner Device</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Edit Device Details</h3>
            </div>
            <form action="{{ route('admin.scanner-devices.update', $scannerDevice->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="device_code">Device Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('device_code') is-invalid @enderror" id="device_code"
                            name="device_code" value="{{ old('device_code', $scannerDevice->device_code) }}" required>
                        @error('device_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="device_name">Device Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('device_name') is-invalid @enderror" id="device_name"
                            name="device_name" value="{{ old('device_name', $scannerDevice->device_name) }}" required>
                        @error('device_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tournament_id">Link to Tournament</label>
                        <select class="form-control @error('tournament_id') is-invalid @enderror" id="tournament_id" name="tournament_id">
                            <option value="">-- Not Linked --</option>
                            @foreach($tournaments as $tournament)
                                <option value="{{ $tournament->id }}" {{ old('tournament_id', $scannerDevice->tournament_id) == $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->tournament_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('tournament_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="ACTIVE" {{ old('status', $scannerDevice->status) == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status', $scannerDevice->status) == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="MAINTENANCE" {{ old('status', $scannerDevice->status) == 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="firmware_version">Firmware Version</label>
                        <input type="text" class="form-control @error('firmware_version') is-invalid @enderror" id="firmware_version"
                            name="firmware_version" value="{{ old('firmware_version', $scannerDevice->firmware_version) }}">
                        @error('firmware_version')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes"
                            name="notes" rows="3">{{ old('notes', $scannerDevice->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Last Seen</label>
                        <p class="form-control-static">
                            @if($scannerDevice->last_seen_at)
                                {{ $scannerDevice->last_seen_at->format('Y-m-d H:i:s') }}
                                ({{ $scannerDevice->last_seen_at->diffForHumans() }})
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Created At</label>
                        <p class="form-control-static">{{ $scannerDevice->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Device</button>
                    <a href="{{ route('admin.scanner-devices.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
