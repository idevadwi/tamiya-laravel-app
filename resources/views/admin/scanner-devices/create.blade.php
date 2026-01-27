@extends('adminlte::page')

@section('title', 'Register Scanner Device')

@section('content_header')
<h1>Register New Scanner Device</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Device Details</h3>
            </div>
            <form action="{{ route('admin.scanner-devices.store') }}" method="POST">
                @csrf
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
                            name="device_code" placeholder="e.g., ESP32-A1B2C3D4E5F6 or MAC address" value="{{ old('device_code') }}" required>
                        <small class="form-text text-muted">Use the ESP32's MAC address or a custom identifier.</small>
                        @error('device_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="device_name">Device Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('device_name') is-invalid @enderror" id="device_name"
                            name="device_name" placeholder="e.g., Track 1 Scanner" value="{{ old('device_name') }}" required>
                        <small class="form-text text-muted">A friendly name to identify this device.</small>
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
                                <option value="{{ $tournament->id }}" {{ old('tournament_id') == $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->tournament_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select a tournament for this device to scan races into.</small>
                        @error('tournament_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="ACTIVE" {{ old('status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="MAINTENANCE" {{ old('status') == 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
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
                            name="firmware_version" placeholder="e.g., 1.0.0" value="{{ old('firmware_version') }}">
                        @error('firmware_version')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes"
                            name="notes" rows="3" placeholder="Optional notes about this device">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Register Device</button>
                    <a href="{{ route('admin.scanner-devices.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> ESP32 Setup Guide</h3>
            </div>
            <div class="card-body">
                <h5>1. Get Device Code</h5>
                <p>Use ESP32's MAC address as the device code:</p>
                <pre><code>String deviceCode = WiFi.macAddress();
// Result: "A1:B2:C3:D4:E5:F6"</code></pre>

                <h5>2. Update ESP32 Code</h5>
                <p>Send the device code in the request header:</p>
                <pre><code>http.addHeader("X-Device-Code", deviceCode);
http.addHeader("Content-Type", "application/json");

String body = "{\"card_code\":\"" + cardCode + "\"}";
http.POST("{{ url('/api/scanner/race') }}");</code></pre>

                <h5>3. API Endpoint</h5>
                <p><code>POST {{ url('/api/scanner/race') }}</code></p>

                <h5>4. Response Codes</h5>
                <ul>
                    <li><code>201</code> - Race created successfully</li>
                    <li><code>400</code> - Device not linked or validation error</li>
                    <li><code>404</code> - Device not registered</li>
                    <li><code>403</code> - Device inactive</li>
                    <li><code>409</code> - Race slot already occupied</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop
