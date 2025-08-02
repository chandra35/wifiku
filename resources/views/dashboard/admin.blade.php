@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <h1>Dashboard Admin</h1>
@stop

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-lg-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['accessible_routers'] }}</h3>
                    <p>Accessible Routers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <div class="small-box-footer">
                    <i class="fas fa-info-circle"></i> Routers assigned to you
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['my_pppoe_secrets'] }}</h3>
                    <p>My PPPoE Secrets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <a href="{{ route('pppoe.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Assigned Routers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assigned Routers</h3>
                </div>
                <div class="card-body p-0">
                    @if($userRouters->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userRouters as $router)
                                    <tr>
                                        <td>{{ $router->name }}</td>
                                        <td>{{ $router->ip_address }}</td>
                                        <td>
                                            @if($router->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-network-wired fa-3x mb-3"></i>
                            <p>No routers assigned to you yet.</p>
                            <small>Contact your administrator to get router access.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent PPPoE Secrets -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Recent PPPoE Secrets</h3>
                    <div class="card-tools">
                        <a href="{{ route('pppoe.index') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recent_pppoe->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Router</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_pppoe as $pppoe)
                                    <tr>
                                        <td>{{ $pppoe->username }}</td>
                                        <td>{{ $pppoe->router->name }}</td>
                                        <td>
                                            @if($pppoe->disabled)
                                                <span class="badge badge-danger">Disabled</span>
                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        </td>
                                        <td>{{ $pppoe->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-wifi fa-3x mb-3"></i>
                            <p>You haven't created any PPPoE secrets yet.</p>
                            @if($userRouters->count() > 0)
                                <a href="{{ route('pppoe.create') }}" class="btn btn-primary">Create PPPoE Secret</a>
                            @else
                                <small>You need router access to create PPPoE secrets.</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($userRouters->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('pppoe.create') }}" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create New PPPoE Secret
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('pppoe.index') }}" class="btn btn-info btn-lg btn-block">
                                    <i class="fas fa-list mr-2"></i>
                                    View All My PPPoE Secrets
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .small-box .icon {
            color: rgba(255,255,255,0.8);
        }
    </style>
@stop
