@extends('adminlte::page')

@section('title', 'Dashboard Super Admin')

@section('content_header')
    <h1>Dashboard Super Admin</h1>
@stop

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_routers'] }}</h3>
                    <p>Total Routers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <a href="{{ route('routers.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_routers'] }}</h3>
                    <p>Active Routers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('routers.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_users'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_pppoe_secrets'] }}</h3>
                    <p>PPPoE Secrets</p>
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
        <!-- Recent PPPoE Secrets -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent PPPoE Secrets</h3>
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
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_pppoe as $pppoe)
                                    <tr>
                                        <td>{{ $pppoe->username }}</td>
                                        <td>{{ $pppoe->router->name }}</td>
                                        <td>{{ $pppoe->user->name }}</td>
                                        <td>
                                            @if($pppoe->disabled)
                                                <span class="badge badge-danger">Disabled</span>
                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        </td>
                                        <td>{{ $pppoe->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-wifi fa-3x mb-3"></i>
                            <p>No PPPoE secrets created yet.</p>
                            <a href="{{ route('pppoe.create') }}" class="btn btn-primary">Create First PPPoE Secret</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .icon {
            color: rgba(255,255,255,0.8);
        }
    </style>
@stop
