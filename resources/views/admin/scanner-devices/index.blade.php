@extends('adminlte::page')

@section('title', 'Scanner Devices')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1>Scanner Devices</h1>
        <p class="text-muted mb-0">Manage ESP32 scanner devices and tournament mappings.</p>
    </div>
    <a href="{{ route('admin.scanner-devices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Register New Device
    </a>
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
        <h3 class="card-title">All Devices</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('admin.scanner-devices.index') }}" class="form-inline">
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">All Status</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    <option value="MAINTENANCE" {{ request('status') == 'MAINTENANCE' ? 'selected' : '' }}>Maintenance</option>
                </select>
                <select name="linked" class="form-control form-control-sm mr-2">
                    <option value="">All Devices</option>
                    <option value="yes" {{ request('linked') == 'yes' ? 'selected' : '' }}>Linked</option>
                    <option value="no" {{ request('linked') == 'no' ? 'selected' : '' }}>Unlinked</option>
                </select>
                <input type="text" name="search" class="form-control form-control-sm mr-2"
                    placeholder="Search..." value="{{ request('search') }}" style="width: 150px;">
                <button type="submit" class="btn btn-sm btn-default">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('search') || request('status') || request('linked'))
                    <a href="{{ route('admin.scanner-devices.index') }}" class="btn btn-sm btn-default ml-1" title="Clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Device Code</th>
                        <th>Name</th>
                        <th>Tournament</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td><code>{{ $device->device_code }}</code></td>
                        <td>{{ $device->device_name }}</td>
                        <td>
                            @if($device->tournament)
                                <span class="badge badge-success">{{ $device->tournament->tournament_name }}</span>
                            @else
                                <span class="badge badge-secondary">Not Linked</span>
                            @endif
                        </td>
                        <td>
                            @if($device->status == 'ACTIVE')
                                <span class="badge badge-success">Active</span>
                            @elseif($device->status == 'INACTIVE')
                                <span class="badge badge-warning">Inactive</span>
                            @else
                                <span class="badge badge-info">Maintenance</span>
                            @endif
                        </td>
                        <td>
                            @if($device->last_seen_at)
                                <span title="{{ $device->last_seen_at->format('Y-m-d H:i:s') }}">
                                    {{ $device->last_seen_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.scanner-devices.show', $device->id) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.scanner-devices.edit', $device->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.scanner-devices.destroy', $device->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this device?');">
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
                        <td colspan="6" class="text-center">
                            No scanner devices found.
                            <a href="{{ route('admin.scanner-devices.create') }}">Register one now</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($devices->hasPages())
    <div class="card-footer">
        {{ $devices->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@stop
