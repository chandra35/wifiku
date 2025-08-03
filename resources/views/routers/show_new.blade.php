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
        <!-- Router Information Card -->
        <div class="col-md-8">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server mr-2"></i>Router Information
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-light" id="refreshStatus">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body bg-dark text-light p-3">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td class="table-label">Hostname</td>
                                    <td class="table-value">{{ $router->name }}</td>
                                    <td class="table-label">Status</td>
                                    <td class="table-value">
                                        @if($router->status === 'active')
                                            <span class="badge badge-success badge-sm">
                                                <i class="fas fa-check"></i> Active
                                            </span>
                                        @else
                                            <span class="badge badge-danger badge-sm">
                                                <i class="fas fa-times"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label">IP Address</td>
                                    <td class="table-value">
                                        <code class="text-primary">{{ $router->ip_address }}</code>
                                    </td>
                                    <td class="table-label">Connection</td>
                                    <td class="table-value">
                                        <span class="connection-status" data-router-id="{{ $router->id }}">
                                            <span class="badge badge-secondary badge-sm">
                                                <i class="fas fa-spinner fa-spin"></i> Checking...
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label">Username</td>
                                    <td class="table-value">{{ $router->username }}</td>
                                    <td class="table-label">API Port</td>
                                    <td class="table-value">
                                        <code class="text-warning">{{ $router->port }}</code>
                                    </td>
                                </tr>
                                @if($router->status === 'active')
                                <tr>
                                    <td class="table-label">System Identity</td>
                                    <td class="table-value">
                                        <span id="system-identity-text" class="system-identity" data-router-id="{{ $router->id }}">
                                            <small class="text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Loading...
                                            </small>
                                        </span>
                                    </td>
                                    <td class="table-label">RouterOS Version</td>
                                    <td class="table-value">
                                        <span id="routeros-version-text">
                                            @if($router->routeros_version)
                                                <code class="text-success">{{ $router->routeros_version }}</code>
                                                @if($router->architecture)
                                                    <small class="text-muted ml-1">({{ $router->architecture }})</small>
                                                @endif
                                            @else
                                                <small class="text-muted">
                                                    <i class="fas fa-spinner fa-spin"></i> Loading...
                                                </small>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @if($router->board_name)
                                <tr>
                                    <td class="table-label">Hardware Board</td>
                                    <td class="table-value">
                                        <span id="board-name-text">{{ $router->board_name }}</span>
                                    </td>
                                    <td class="table-label">Last Updated</td>
                                    <td class="table-value">
                                        <small class="text-muted">{{ $router->updated_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                </tr>
                                @endif
                                @endif
                                <tr>
                                    <td class="table-label">Created</td>
                                    <td class="table-value">
                                        <small class="text-muted">{{ $router->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="table-label">Updated</td>
                                    <td class="table-value">
                                        <small class="text-muted">{{ $router->updated_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    @if($router->description)
                    <div class="mt-3 pt-3 border-top border-secondary">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-file-alt mr-1"></i>Description
                        </small>
                        <small class="text-light">{{ $router->description }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-md-4">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> Edit Router
                        </a>
                        
                        @if($router->status === 'active')
                            <button class="btn btn-info btn-block" id="testConnection">
                                <i class="fas fa-network-wired"></i> Test Connection
                            </button>
                            
                            <button class="btn btn-success btn-block" id="getSystemInfo">
                                <i class="fas fa-info-circle"></i> System Info
                            </button>
                        @endif
                        
                        <form action="{{ route('routers.destroy', $router) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this router?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash"></i> Delete Router
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-refresh system identity and connection status
    function updateSystemIdentity() {
        var routerId = $('.system-identity').data('router-id');
        
        if (routerId) {
            $.ajax({
                url: '/routers/' + routerId + '/system-identity',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#system-identity-text').html(
                            '<span class="badge badge-info badge-sm">' + response.data.identity + '</span>'
                        );
                        
                        if (response.data.version) {
                            $('#routeros-version-text').html(
                                '<code class="text-success">' + response.data.version + '</code>' +
                                (response.data.architecture ? ' <small class="text-muted ml-1">(' + response.data.architecture + ')</small>' : '')
                            );
                        }
                        
                        if (response.data.board) {
                            $('#board-name-text').text(response.data.board);
                        }
                    } else {
                        $('#system-identity-text').html(
                            '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + response.message + '</small>'
                        );
                    }
                },
                error: function() {
                    $('#system-identity-text').html(
                        '<small class="text-danger"><i class="fas fa-times"></i> Error loading</small>'
                    );
                }
            });
        }
    }

    // Update connection status
    function updateConnectionStatus() {
        var routerId = $('.connection-status').data('router-id');
        
        if (routerId) {
            $.ajax({
                url: '/routers/' + routerId + '/status',
                method: 'GET',
                success: function(response) {
                    var statusHtml = '';
                    if (response.connected) {
                        statusHtml = '<span class="badge badge-success badge-sm"><i class="fas fa-check"></i> Connected</span>';
                    } else {
                        statusHtml = '<span class="badge badge-danger badge-sm"><i class="fas fa-times"></i> Disconnected</span>';
                    }
                    $('.connection-status').html(statusHtml);
                },
                error: function() {
                    $('.connection-status').html(
                        '<span class="badge badge-danger badge-sm"><i class="fas fa-exclamation-triangle"></i> Error</span>'
                    );
                }
            });
        }
    }

    // Initial load
    updateSystemIdentity();
    updateConnectionStatus();

    // Auto-refresh every 30 seconds
    setInterval(function() {
        updateSystemIdentity();
        updateConnectionStatus();
    }, 30000);

    // Manual refresh button
    $('#refreshStatus').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateSystemIdentity();
        updateConnectionStatus();
        setTimeout(function() {
            $('#refreshStatus i').removeClass('fa-spin');
        }, 2000);
    });

    // Test connection button
    $('#testConnection').click(function() {
        var btn = $(this);
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        setTimeout(function() {
            updateConnectionStatus();
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-network-wired"></i> Test Connection');
        }, 2000);
    });

    // Get system info button
    $('#getSystemInfo').click(function() {
        var btn = $(this);
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        setTimeout(function() {
            updateSystemIdentity();
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-info-circle"></i> System Info');
        }, 2000);
    });
});
</script>

<style>
/* Simple Dark Mode Table Styling */
.table-dark {
    font-size: 0.8rem;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.table-dark td {
    padding: 8px 12px;
    vertical-align: middle;
    border: none;
}

.table-label {
    font-weight: 500;
    color: #9ca3af;
    width: 20%;
    min-width: 100px;
}

.table-value {
    color: #f1f5f9;
    width: 30%;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 3px 8px;
}

.connection-status .badge {
    min-width: 80px;
}

code {
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    background-color: rgba(255, 255, 255, 0.1);
}

.text-primary {
    color: #60a5fa !important;
}

.text-warning {
    color: #fbbf24 !important;
}

.text-success {
    color: #34d399 !important;
}

.border-secondary {
    border-color: #4b5563 !important;
}

/* Card Header Styling */
.card-dark .card-header {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    border-bottom: 1px solid #4b5563;
}

.card-dark .card-body {
    background-color: #1f2937;
}

/* Button Styling */
.btn-outline-light {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-color: #6b7280;
}

.btn-outline-light:hover {
    background-color: #4b5563;
    border-color: #6b7280;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-dark {
        font-size: 0.7rem;
    }
    
    .table-label,
    .table-value {
        width: auto;
        display: block;
        padding: 4px 8px;
    }
    
    .table-label {
        font-weight: 600;
        padding-bottom: 2px;
    }
    
    .table-value {
        padding-top: 0;
        padding-bottom: 8px;
    }
}
</style>
@stop
