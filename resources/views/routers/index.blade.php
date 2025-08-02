@extends('adminlte::page')

@section('title', 'Router Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Router Management</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('routers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Router
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Routers</h3>
        </div>
        <div class="card-body">
            @if($routers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Port</th>
                                <th>Connection</th>
                                <th>CPU Load</th>
                                <th>Memory Usage</th>
                                <th>Active PPP</th>
                                <th>Uptime</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routers as $router)
                                <tr id="router-{{ $router->id }}">
                                    <td>{{ $router->name }}</td>
                                    <td>{{ $router->ip_address }}</td>
                                    <td>{{ $router->port }}</td>
                                    <td>
                                        @if($router->status === 'active')
                                            <span class="badge badge-success connection-status" data-router-id="{{ $router->id }}">
                                                <i class="fas fa-spinner fa-spin"></i> Checking...
                                            </span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="cpu-load" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="memory-usage" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar memory-progress" data-router-id="{{ $router->id }}" 
                                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="active-ppp" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="uptime" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </td>
                                    <td>{{ $router->description ?? '-' }}</td>
                                    <td>{{ $router->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('routers.show', $router) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('routers.monitor', $router) }}" class="btn btn-success btn-sm" title="Monitor">
                                                <i class="fas fa-desktop"></i>
                                            </a>
                                            <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('routers.destroy', $router) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this router?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center">
                    {{ $routers->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-network-wired fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No routers found</h5>
                    <p class="text-muted">Add your first MikroTik router to get started.</p>
                    <a href="{{ route('routers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Router
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Load router status for each router
    @if($routers->count() > 0)
        @foreach($routers as $router)
            @if($router->status === 'active')
                loadRouterStatus('{{ $router->id }}');
            @else
                // Set inactive status
                updateRouterStatus('{{ $router->id }}', {
                    connected: false,
                    cpu_load: 'N/A',
                    memory_usage: 'N/A',
                    memory_usage_percent: 0,
                    active_ppp_sessions: 'N/A',
                    uptime: 'N/A',
                    error: 'Router is inactive'
                });
            @endif
        @endforeach
    @endif
});

function loadRouterStatus(routerId) {
    $.ajax({
        url: `/routers/${routerId}/status`,
        method: 'GET',
        timeout: 30000, // 30 seconds timeout
        success: function(data) {
            updateRouterStatus(routerId, data);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load router status for router ' + routerId + ':', error);
            updateRouterStatus(routerId, {
                connected: false,
                cpu_load: 'N/A',
                memory_usage: 'N/A',
                memory_usage_percent: 0,
                active_ppp_sessions: 'N/A',
                uptime: 'N/A',
                error: 'Connection timeout'
            });
        }
    });
}

function updateRouterStatus(routerId, data) {
    // Update connection status
    const connectionStatus = $(`.connection-status[data-router-id="${routerId}"]`);
    if (data.connected) {
        connectionStatus.removeClass('badge-danger badge-warning')
                      .addClass('badge-success')
                      .html('<i class="fas fa-check"></i> Online');
    } else {
        connectionStatus.removeClass('badge-success badge-warning')
                      .addClass('badge-danger')
                      .html('<i class="fas fa-times"></i> Offline');
    }

    // Update CPU load
    const cpuLoad = $(`.cpu-load[data-router-id="${routerId}"]`);
    if (data.connected && data.cpu_load !== 'N/A') {
        const cpuPercent = parseInt(data.cpu_load);
        let cpuClass = 'text-success';
        if (cpuPercent > 70) cpuClass = 'text-danger';
        else if (cpuPercent > 50) cpuClass = 'text-warning';
        
        cpuLoad.html(`<span class="${cpuClass}"><i class="fas fa-microchip"></i> ${data.cpu_load}</span>`);
    } else {
        cpuLoad.html('<span class="text-muted"><i class="fas fa-times"></i> N/A</span>');
    }

    // Update memory usage
    const memoryUsage = $(`.memory-usage[data-router-id="${routerId}"]`);
    const memoryProgress = $(`.memory-progress[data-router-id="${routerId}"]`);
    
    if (data.connected && data.memory_usage !== 'N/A') {
        const memoryPercent = data.memory_usage_percent || 0;
        let memoryClass = 'bg-success';
        if (memoryPercent > 80) memoryClass = 'bg-danger';
        else if (memoryPercent > 60) memoryClass = 'bg-warning';
        
        memoryUsage.html(`<small><i class="fas fa-memory"></i> ${data.memory_usage}</small>`);
        memoryProgress.removeClass('bg-success bg-warning bg-danger')
                     .addClass(memoryClass)
                     .css('width', memoryPercent + '%')
                     .attr('aria-valuenow', memoryPercent);
    } else {
        memoryUsage.html('<span class="text-muted"><i class="fas fa-times"></i> N/A</span>');
        memoryProgress.css('width', '0%').attr('aria-valuenow', 0);
    }

    // Update active PPP sessions
    const activePpp = $(`.active-ppp[data-router-id="${routerId}"]`);
    if (data.connected && data.active_ppp_sessions !== 'N/A') {
        activePpp.html(`<span class="badge badge-info"><i class="fas fa-users"></i> ${data.active_ppp_sessions}</span>`);
    } else {
        activePpp.html('<span class="text-muted"><i class="fas fa-times"></i> N/A</span>');
    }

    // Update uptime
    const uptime = $(`.uptime[data-router-id="${routerId}"]`);
    if (data.connected && data.uptime !== 'N/A') {
        uptime.html(`<small><i class="fas fa-clock"></i> ${formatUptime(data.uptime)}</small>`);
    } else {
        uptime.html('<span class="text-muted"><i class="fas fa-times"></i> N/A</span>');
    }
}

function formatUptime(uptime) {
    // Format uptime string to be more readable
    if (typeof uptime === 'string') {
        // Handle RouterOS uptime format like "1d2h3m4s"
        let formatted = uptime.replace(/(\d+)w/g, '$1w ')
                             .replace(/(\d+)d/g, '$1d ')
                             .replace(/(\d+)h/g, '$1h ')
                             .replace(/(\d+)m/g, '$1m ')
                             .replace(/(\d+)s/g, '$1s');
        return formatted.trim();
    }
    return uptime;
}
</script>

<!-- Custom CSS for router status -->
<style>
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.9rem;
}

.progress {
    background-color: #e9ecef;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    margin: 0 1px;
}

/* Make table responsive on small screens */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>
@stop
