@extends('adminlte::page')

@section('title', 'Router Details')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Router Details</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Routers
                </a>
                <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Router Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Name</th>
                            <td>{{ $router->name }}</td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td>{{ $router->ip_address }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ $router->username }}</td>
                        </tr>
                        <tr>
                            <th>API Port</th>
                            <td>{{ $router->port }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($router->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $router->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $router->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{ $router->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Router
                    </a>
                    
                    <form action="{{ route('routers.destroy', $router) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this router? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Router
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assigned Users</h3>
                </div>
                <div class="card-body">
                    @if($router->users->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($router->users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $user->name }}
                                    <span class="badge badge-primary">{{ $user->role->display_name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No users assigned to this router.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
