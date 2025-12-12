@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Email:</dt>
                <dd class="col-sm-9">{{ $user->email }}</dd>

                <dt class="col-sm-3">Phone:</dt>
                <dd class="col-sm-9">{{ $user->phone }}</dd>

                <dt class="col-sm-3">Roles:</dt>
                <dd class="col-sm-9">
                    @foreach($user->roles as $role)
                        @if($role->role_name === 'ADMINISTRATOR')
                            <span class="badge badge-danger">{{ $role->role_name }}</span>
                        @elseif($role->role_name === 'MODERATOR')
                            <span class="badge badge-warning">{{ $role->role_name }}</span>
                        @else
                            <span class="badge badge-secondary">{{ $role->role_name }}</span>
                        @endif
                    @endforeach
                </dd>

                <dt class="col-sm-3">Created At:</dt>
                <dd class="col-sm-9">{{ $user->created_at->format('Y-m-d H:i:s') }}</dd>

                <dt class="col-sm-3">Updated At:</dt>
                <dd class="col-sm-9">{{ $user->updated_at->format('Y-m-d H:i:s') }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
