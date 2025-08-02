@extends('adminlte::page')

@section('title', 'Router Management')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-network-wired mr-2"></i>
                    Router Management
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('routers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i> Add Router
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-routers">{{ $routers->total() }}</h3>
                    <p>Total Routers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="active-routers">0</h3>
                    <p>Active Routers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="total-ppp">0</h3>
                    <p>Total PPP Sessions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="avg-cpu">0%</h3>
                    <p>Average CPU Load</p>
                </div>
                <div class="icon">
                    <i class="fas fa-microchip"></i>
                </div>
            </div>
        </div>
    </div>

    @if($routers->count() > 0)
        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    All Routers
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="searchTable" class="form-control float-right" placeholder="Search routers...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-head-fixed table-hover">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Router</th>
                            <th style="width: 120px;">Connection</th>
                            <th style="width: 100px;">CPU Load</th>
                            <th style="width: 130px;">Memory Usage</th>
                            <th style="width: 80px;">PPP Sessions</th>
                            <th style="width: 100px;">Ping (8.8.8.8)</th>
                            <th style="width: 120px;">Uptime</th>
                            <th class="d-none d-md-table-cell" style="width: 150px;">Description</th>
                            <th class="d-none d-lg-table-cell" style="width: 100px;">Created</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routers as $router)
                            <tr id="router-{{ $router->id }}" class="router-row">
                                <!-- Router Info -->
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-dark">{{ $router->name }}</strong>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $router->ip_address }}:{{ $router->port ?? 8728 }}
                                        </small>
                                    </div>
                                </td>
                                
                                <!-- Connection Status -->
                                <td>
                                    <span class="badge badge-secondary connection-status" data-router-id="{{ $router->id }}">
                                        <i class="fas fa-spinner fa-spin"></i> Checking...
                                    </span>
                                </td>
                                
                                <!-- CPU Load -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="cpu-load mr-2" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success cpu-progress" data-router-id="{{ $router->id }}" 
                                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Memory Usage -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="memory-usage mr-2" data-router-id="{{ $router->id }}">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-info memory-progress" data-router-id="{{ $router->id }}" 
                                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- PPP Sessions -->
                                <td class="text-center">
                                    <span class="badge badge-info active-ppp" data-router-id="{{ $router->id }}">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </td>
                                
                                <!-- Ping to 8.8.8.8 -->
                                <td class="text-center">
                                    <span class="ping-status" data-router-id="{{ $router->id }}">
                                        <i class="fas fa-spinner fa-spin text-muted"></i>
                                    </span>
                                </td>
                                
                                <!-- Uptime -->
                                <td>
                                    <small class="text-muted uptime" data-router-id="{{ $router->id }}">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </small>
                                </td>
                                
                                <!-- Description (hidden on mobile) -->
                                <td class="d-none d-md-table-cell">
                                    <small class="text-muted">{{ Str::limit($router->description ?? '-', 20) }}</small>
                                </td>
                                
                                <!-- Created Date (hidden on mobile/tablet) -->
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted">{{ $router->created_at->format('d/m/Y') }}</small>
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('routers.show', $router) }}" class="btn btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('routers.monitor', $router) }}" class="btn btn-success btn-sm" title="Monitor">
                                            <i class="fas fa-desktop"></i>
                                        </a>
                                        <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="deleteRouter({{ $router->id }}, '{{ $router->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            @if($routers->hasPages())
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="dataTables_info">
                            Showing {{ $routers->firstItem() }} to {{ $routers->lastItem() }} of {{ $routers->total() }} routers
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-right">
                            {{ $routers->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="mb-4">
                        <i class="fas fa-network-wired fa-5x text-muted"></i>
                    </div>
                    <h3 class="text-muted">No Routers Found</h3>
                    <p class="text-muted mb-4">Start by adding your first MikroTik router to manage your network infrastructure.</p>
                    <a href="{{ route('routers.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>Add Your First Router
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<style>
/* Modern Table Styles */
.ping-status {
    font-weight: 500;
    text-align: center;
}

.ping-status i {
    margin-right: 3px;
}

.ping-status .text-success {
    color: #28a745 !important;
}

.ping-status .text-warning {
    color: #ffc107 !important;
}

.ping-status .text-danger {
    color: #dc3545 !important;
}

.table-head-fixed thead th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    transition: all 0.2s ease-in-out;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
    border-top: 1px solid #f1f1f1;
    padding: 0.75rem;
}

/* Progress bars */
.progress {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    margin: 0;
}

.progress-bar {
    transition: width 0.6s ease;
    border-radius: 3px;
}

/* Badge styles */
.badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    font-weight: 500;
}

/* Button group */
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
    margin: 0 1px;
}

/* Small box styles */
.small-box {
    border-radius: 0.5rem;
    transition: transform 0.2s ease-in-out;
}

.small-box:hover {
    transform: translateY(-2px);
}

/* Card styles */
.card {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Search input styles */
.input-group-sm .form-control {
    border-radius: 0.375rem 0 0 0.375rem;
}

.input-group-sm .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

/* Empty state */
.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .table td {
        padding: 0.5rem 0.25rem;
    }
    
    .small-box .inner h3 {
        font-size: 1.5rem;
    }
    
    .small-box .inner p {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .table-responsive {
        border: none;
    }
    
    .card-tools {
        display: none;
    }
    
    .btn-group-sm .btn {
        padding: 0.15rem 0.3rem;
        font-size: 0.65rem;
    }
}

/* Loading animation */
.fa-spinner {
    color: #6c757d;
}

/* Table row animation */
.router-row {
    transition: all 0.2s ease-in-out;
}

/* Progress bar color variants */
.progress-bar.bg-success {
    background-color: #28a745 !important;
}

.progress-bar.bg-warning {
    background-color: #ffc107 !important;
}

.progress-bar.bg-danger {
    background-color: #dc3545 !important;
}

.progress-bar.bg-info {
    background-color: #17a2b8 !important;
}

/* Pagination styles */
.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.dataTables_info {
    font-size: 0.875rem;
    color: #6c757d;
    padding-top: 0.5rem;
}

/* Connection status badges */
.badge.badge-success {
    background-color: #28a745;
}

.badge.badge-danger {
    background-color: #dc3545;
}

.badge.badge-warning {
    background-color: #ffc107;
}

.badge.badge-secondary {
    background-color: #6c757d;
}

.badge.badge-info {
    background-color: #17a2b8;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let activeRouters = 0;
    let totalPppSessions = 0;
    let totalCpuLoad = 0;
    let routerCount = 0;

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Search functionality
    $('#searchTable, #searchBtn').on('keyup click', function() {
        let searchTerm = $('#searchTable').val().toLowerCase();
        
        $('.router-row').each(function() {
            let row = $(this);
            let name = row.find('strong').text().toLowerCase();
            let ip = row.find('small').text().toLowerCase();
            
            if (name.includes(searchTerm) || ip.includes(searchTerm)) {
                row.show();
            } else {
                row.hide();
            }
        });
    });

    // Load router status for each router (slower interval)
    @if($routers->count() > 0)
        @foreach($routers as $router)
            @if($router->status === 'active')
                loadRouterStatus('{{ $router->id }}');
            @else
                updateRouterStatus('{{ $router->id }}', {
                    connected: false,
                    cpu_load: 'N/A',
                    memory_usage: 'N/A',
                    memory_usage_percent: 0,
                    active_ppp_sessions: 'N/A',
                    ping_8888: 'N/A',
                    uptime: 'N/A',
                    error: 'Router is inactive'
                });
            @endif
        @endforeach

        // Start ping monitoring (faster interval - every 3 seconds)
        // startPingMonitoring();
        // setInterval(startPingMonitoring, 3000);

        // Start status monitoring (slower interval - every 30 seconds)
        setInterval(function() {
            @foreach($routers as $router)
                @if($router->status === 'active')
                    loadRouterStatus('{{ $router->id }}');
                @endif
            @endforeach
        }, 30000);
    @endif
});

function loadRouterStatus(routerId) {
    $.ajax({
        url: `/routers/${routerId}/status`,
        method: 'GET',
        timeout: 30000,
        success: function(data) {
            updateRouterStatus(routerId, data);
            updateStatistics(data);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load router status for router ' + routerId + ':', error);
            updateRouterStatus(routerId, {
                connected: false,
                cpu_load: 'N/A',
                memory_usage: 'N/A',
                memory_usage_percent: 0,
                active_ppp_sessions: 'N/A',
                ping_8888: 'N/A',
                uptime: 'N/A',
                error: 'Connection timeout'
            });
        }
    });
}

function startPingMonitoring() {
    $.ajax({
        url: '/routers/ping-data',
        method: 'GET',
        timeout: 10000,
        success: function(response) {
            if (response.success) {
                Object.keys(response.data).forEach(function(routerId) {
                    const pingData = response.data[routerId];
                    updatePingStatus(routerId, pingData);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load ping data:', error);
        }
    });
}

function updatePingStatus(routerId, pingData) {
    const pingStatus = $(`.ping-status[data-router-id="${routerId}"]`);
    
    if (pingData.status === 'success' && pingData.ping_time !== null) {
        const pingValue = parseFloat(pingData.ping_time);
        let pingClass = 'text-success';
        let pingIcon = 'fas fa-signal';
        
        if (pingValue > 200) {
            pingClass = 'text-danger';
            pingIcon = 'fas fa-exclamation-triangle';
        } else if (pingValue > 100) {
            pingClass = 'text-warning';
            pingIcon = 'fas fa-exclamation-circle';
        }
        
        pingStatus.removeClass('text-muted text-success text-warning text-danger')
                  .addClass(pingClass)
                  .html(`<i class="${pingIcon} mr-1"></i><small><strong>${pingData.display}</strong></small>`);
    } else {
        pingStatus.removeClass('text-success text-warning text-danger')
                  .addClass('text-muted')
                  .html('<i class="fas fa-times mr-1"></i><small>N/A</small>');
    }
}

function updateRouterStatus(routerId, data) {
    // Update connection status
    const connectionStatus = $(`.connection-status[data-router-id="${routerId}"]`);
    if (data.connected) {
        connectionStatus.removeClass('badge-secondary badge-danger badge-warning')
                      .addClass('badge-success')
                      .html('<i class="fas fa-check"></i> Online');
    } else {
        connectionStatus.removeClass('badge-secondary badge-success badge-warning')
                      .addClass('badge-danger')
                      .html('<i class="fas fa-times"></i> Offline');
    }

    // Update CPU load
    const cpuLoad = $(`.cpu-load[data-router-id="${routerId}"]`);
    const cpuProgress = $(`.cpu-progress[data-router-id="${routerId}"]`);
    
    if (data.connected && data.cpu_load !== 'N/A') {
        const cpuPercent = parseInt(data.cpu_load);
        let cpuClass = 'bg-success';
        if (cpuPercent > 70) cpuClass = 'bg-danger';
        else if (cpuPercent > 50) cpuClass = 'bg-warning';
        
        cpuLoad.html(`${data.cpu_load}`);
        cpuProgress.removeClass('bg-success bg-warning bg-danger')
                   .addClass(cpuClass)
                   .css('width', cpuPercent + '%')
                   .attr('aria-valuenow', cpuPercent);
    } else {
        cpuLoad.html('<span class="text-muted">N/A</span>');
        cpuProgress.css('width', '0%').attr('aria-valuenow', 0);
    }

    // Update memory usage
    const memoryUsage = $(`.memory-usage[data-router-id="${routerId}"]`);
    const memoryProgress = $(`.memory-progress[data-router-id="${routerId}"]`);
    
    if (data.connected && data.memory_usage !== 'N/A') {
        const memoryPercent = data.memory_usage_percent || 0;
        let memoryClass = 'bg-info';
        if (memoryPercent > 80) memoryClass = 'bg-danger';
        else if (memoryPercent > 60) memoryClass = 'bg-warning';
        
        memoryUsage.html(`${Math.round(memoryPercent)}%`);
        memoryProgress.removeClass('bg-info bg-warning bg-danger')
                     .addClass(memoryClass)
                     .css('width', memoryPercent + '%')
                     .attr('aria-valuenow', memoryPercent);
    } else {
        memoryUsage.html('<span class="text-muted">N/A</span>');
        memoryProgress.css('width', '0%').attr('aria-valuenow', 0);
    }

    // Update active PPP sessions
    const activePpp = $(`.active-ppp[data-router-id="${routerId}"]`);
    if (data.connected && data.active_ppp_sessions !== 'N/A') {
        activePpp.removeClass('badge-secondary')
                 .addClass('badge-info')
                 .html(`${data.active_ppp_sessions}`);
    } else {
        activePpp.removeClass('badge-info')
                 .addClass('badge-secondary')
                 .html('0');
    }

    // Update ping status
    const pingStatus = $(`.ping-status[data-router-id="${routerId}"]`);
    if (data.connected && data.ping_8888 !== 'N/A') {
        const pingValue = parseFloat(data.ping_8888.replace('ms', ''));
        let pingClass = 'text-success';
        let pingIcon = 'fas fa-signal';
        
        if (pingValue > 200) {
            pingClass = 'text-danger';
            pingIcon = 'fas fa-exclamation-triangle';
        } else if (pingValue > 100) {
            pingClass = 'text-warning';
            pingIcon = 'fas fa-exclamation-circle';
        }
        
        pingStatus.removeClass('text-muted text-success text-warning text-danger')
                  .addClass(pingClass)
                  .html(`<i class="${pingIcon} mr-1"></i><small><strong>${data.ping_8888}</strong></small>`);
    } else {
        pingStatus.removeClass('text-success text-warning text-danger')
                  .addClass('text-muted')
                  .html('<i class="fas fa-times mr-1"></i><small>N/A</small>');
    }

    // Update uptime
    const uptime = $(`.uptime[data-router-id="${routerId}"]`);
    if (data.connected && data.uptime !== 'N/A') {
        uptime.removeClass('text-muted')
              .addClass('text-success')
              .html(`<i class="fas fa-clock mr-1"></i>${formatUptime(data.uptime)}`);
    } else {
        uptime.removeClass('text-success')
              .addClass('text-muted')
              .html('<i class="fas fa-times mr-1"></i>N/A');
    }
}

function updateStatistics(data) {
    if (data.connected) {
        let currentActive = parseInt($('#active-routers').text()) || 0;
        $('#active-routers').text(currentActive + 1);
        
        if (data.active_ppp_sessions !== 'N/A') {
            let currentPpp = parseInt($('#total-ppp').text()) || 0;
            let newPpp = parseInt(data.active_ppp_sessions) || 0;
            $('#total-ppp').text(currentPpp + newPpp);
        }
        
        if (data.cpu_load !== 'N/A') {
            let currentCpu = $('#avg-cpu').text().replace('%', '');
            let routerCpu = parseInt(data.cpu_load) || 0;
            let activeCount = parseInt($('#active-routers').text()) || 1;
            let avgCpu = Math.round((parseInt(currentCpu) * (activeCount - 1) + routerCpu) / activeCount);
            $('#avg-cpu').text(avgCpu + '%');
        }
    }
}

function formatUptime(uptime) {
    if (typeof uptime === 'string') {
        let formatted = uptime.replace(/(\d+)w/g, '$1w ')
                             .replace(/(\d+)d/g, '$1d ')
                             .replace(/(\d+)h/g, '$1h ')
                             .replace(/(\d+)m/g, '$1m ')
                             .replace(/(\d+)s/g, '$1s');
        return formatted.trim();
    }
    return uptime;
}

function deleteRouter(routerId, routerName) {
    if (confirm(`Are you sure you want to delete router "${routerName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = $('<form>', {
            method: 'POST',
            action: `/routers/${routerId}`
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_method',
            value: 'DELETE'
        }));
        
        $('body').append(form);
        form.submit();
    }
}
</script>
@stop
