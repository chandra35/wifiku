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

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-cog"></i> Configure Router
                    </a>
                    
                    @if($router->status === 'active')
                    <button type="button" class="btn btn-info btn-block mb-2" id="testConnection">
                        <i class="fas fa-wifi"></i> Test Connection
                    </button>
                    @endif
                    
                    <hr class="bg-danger">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </small>
                    
                    <form action="{{ route('routers.destroy', $router) }}" method="POST" 
                          onsubmit="return confirm('⚠️ WARNING: You are about to permanently delete this router.\n\nThis action will:\n• Remove all router configuration\n• Delete all associated data\n• Cannot be undone\n\nAre you absolutely sure?')"
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Router
                        </button>
                    </form>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users-cog mr-2"></i>Assigned Users
                    </h3>
                </div>
                <div class="card-body">
                    @if($router->users->count() > 0)
                        <div class="user-list">
                            @foreach($router->users as $user)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div class="user-info">
                                        <span class="network-value">
                                            <i class="fas fa-user-shield mr-1 text-success"></i>{{ $user->name }}
                                        </span>
                                    </div>
                                    <span class="badge badge-primary">{{ $user->role->display_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p class="mb-0"><small>No users assigned to this router</small></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    const routerId = '{{ $router->id }}';
    let refreshInterval;
    
    // Load initial data
    loadRouterStatus();
    @if($router->status === 'active')
    loadSystemIdentity();
    
    // Start auto-refresh for active routers
    startAutoRefresh();
    @endif
    
    // Manual refresh buttons
    $('#refreshStatus').click(function() {
        $(this).find('i').addClass('fa-spin');
        loadRouterStatus().always(function() {
            $('#refreshStatus i').removeClass('fa-spin');
        });
    });
    
    // Test connection
    $('#testConnection').click(function() {
        const button = $(this);
        const originalText = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        $.ajax({
            url: '/routers/' + routerId + '/test-connection',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    if(typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Connection test successful!');
                    } else {
                        alert(response.message || 'Connection test successful!');
                    }
                    // Refresh status after successful test
                    loadRouterStatus();
                } else {
                    if(typeof toastr !== 'undefined') {
                        toastr.error(response.message || 'Connection test failed!');
                    } else {
                        alert(response.message || 'Connection test failed!');
                    }
                }
            },
            error: function(xhr) {
                let message = 'Connection test failed!';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if(typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            },
            complete: function() {
                button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            loadRouterStatus();
            loadSystemIdentity();
        }, 30000); // 30 seconds
    }
    
    function stopAutoRefresh() {
        if(refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
    
    function loadRouterStatus() {
        console.log('Loading router status for ID:', routerId);
        
        // Show loading state immediately
        updateConnectionStatus(null, 'Checking...');
        
        // Use same logic as Router Management
        return $.ajax({
            url: '/routers/' + routerId + '/status',
            method: 'GET',
            timeout: 30000, // Same timeout as Router Management
            success: function(data) {
                console.log('Router status response:', data);
                updateRouterStatus(data);
            },
            error: function(xhr, status, error) {
                console.error('Failed to load router status for router ' + routerId + ':', error);
                updateRouterStatus({
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
    
    function updateRouterStatus(data) {
        // Update connection status with enhanced table styling
        const connectionStatus = $('.connection-status');
        if (data.connected) {
            connectionStatus.html(
                '<span class="badge badge-success status-badge pulse-success">' +
                '<i class="fas fa-wifi"></i> Connected</span>'
            );
        } else {
            connectionStatus.html(
                '<span class="badge badge-danger status-badge pulse-danger">' +
                '<i class="fas fa-exclamation-triangle"></i> Disconnected</span>'
            );
        }
        
        console.log('Router status updated:', data);
    }
    
    function updateConnectionStatus(isLoading, message) {
        // Helper function for loading state with enhanced styling
        const connectionStatus = $('.connection-status');
        
        if(isLoading === null) {
            // Loading state with pulse animation
            connectionStatus.html(
                '<span class="badge badge-secondary loading-badge pulse-loading">' +
                '<i class="fas fa-spinner fa-spin"></i> ' + (message || 'Checking...') +
                '</span>'
            );
        }
    }
    
    function loadSystemIdentity() {
        console.log('Loading system identity for ID:', routerId);
        
        $.ajax({
            url: `/routers/${routerId}/monitor/system-identity`,
            type: 'GET',
            timeout: 30000, // 30 seconds timeout
            success: function(data) {
                console.log('System identity response received:', data);
                if (data && data.success !== false) {
                    updateSystemIdentity(data);
                } else {
                    console.error('Failed to load system identity:', data);
                    updateSystemIdentity(null);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading system identity:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                updateSystemIdentity(null);
            }
        });
    }
    
    function updateSystemIdentity(data) {
        const identityText = $('#system-identity-text');
        const versionText = $('#routeros-version-text');
        const boardText = $('#board-name-text');
        
        if (data && data.data) {
            const systemData = data.data;
            
            // Update system identity with enhanced styling
            if (systemData.identity) {
                identityText.html('<span class="badge badge-light network-badge identity-badge">' + systemData.identity + '</span>');
            } else {
                identityText.html('<span class="badge badge-secondary">N/A</span>');
            }
            
            // Update RouterOS version with enhanced styling
            if (systemData.version) {
                let versionHtml = '<code class="version-badge bg-success text-white">' + systemData.version + '</code>';
                if (systemData.architecture) {
                    versionHtml += ' <small class="text-muted ml-1 arch-info">(' + systemData.architecture + ')</small>';
                }
                versionText.html(versionHtml);
            } else {
                versionText.html('<span class="badge badge-secondary">N/A</span>');
            }
            
            // Update board name with enhanced styling
            if (systemData.board_name && boardText.length > 0) {
                boardText.html('<span class="board-name hardware-info">' + systemData.board_name + '</span>');
            }
        } else {
            // Error state with enhanced styling
            identityText.html('<span class="badge badge-danger error-badge">Error</span>');
            versionText.html('<span class="badge badge-danger error-badge">Error</span>');
            if (boardText.length > 0) {
                boardText.html('<span class="badge badge-danger error-badge">Error</span>');
            }
        }
        
        console.log('System identity updated:', data);
    }
    
    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
});
</script>

<style>
/* Enhanced Table Styling for Router Information */

/* Card enhancements */
.card-primary.card-outline {
    border-top: 3px solid #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

/* Table base styling */
.network-info-table, 
.system-info-table {
    font-size: 0.9rem;
    border-collapse: separate;
    border-spacing: 0;
}

/* Section headers */
.table-section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border-bottom: 2px solid #dee2e6;
    padding: 15px !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.section-title {
    font-size: 0.85rem;
    color: #495057;
}

/* Table rows */
.network-row,
.system-row {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.network-row:hover {
    background-color: rgba(0,123,255,0.05) !important;
    border-left-color: #007bff;
    transform: translateX(2px);
}

.system-row:hover {
    background-color: rgba(40,167,69,0.05) !important;
    border-left-color: #28a745;
    transform: translateX(2px);
}

/* Table cells */
.table-label {
    width: 25%;
    padding: 12px 15px;
    font-weight: 500;
    vertical-align: middle;
    border-right: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.table-label i {
    width: 20px;
    text-align: center;
}

.table-value {
    padding: 12px 15px;
    vertical-align: middle;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Enhanced badges and codes */
.network-badge {
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    padding: 6px 12px;
    border-radius: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    border: 1px solid #dee2e6;
}

.status-badge {
    font-size: 0.8rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.loading-badge {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    animation: pulse-loading 2s infinite;
}

.identity-badge {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
    border: none;
}

.version-badge {
    font-family: 'Courier New', monospace !important;
    font-size: 0.8rem !important;
    padding: 4px 8px !important;
    border-radius: 15px !important;
    font-weight: bold !important;
}

.error-badge {
    animation: pulse-danger 1.5s infinite;
}

/* Code styling */
.ip-address {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    color: #1976d2 !important;
    padding: 4px 8px;
    border-radius: 15px;
    font-weight: bold;
    border: 1px solid #2196f3;
}

.port-number {
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
    color: #f57f17 !important;
    padding: 4px 8px;
    border-radius: 15px;
    font-weight: bold;
    border: 1px solid #ff9800;
}

/* Text styling */
.username-text {
    font-family: 'Courier New', monospace;
    color: #495057;
    font-weight: 500;
}

.hardware-info {
    color: #28a745;
    font-weight: 500;
}

.arch-info {
    font-style: italic;
}

/* Animations */
@keyframes pulse-loading {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.7;
        transform: scale(1.02);
    }
}

@keyframes pulse-success {
    0%, 100% { 
        box-shadow: 0 0 0 rgba(40,167,69,0.4);
    }
    50% { 
        box-shadow: 0 0 10px rgba(40,167,69,0.6);
    }
}

@keyframes pulse-danger {
    0%, 100% { 
        box-shadow: 0 0 0 rgba(220,53,69,0.4);
    }
    50% { 
        box-shadow: 0 0 10px rgba(220,53,69,0.6);
    }
}

.pulse-success {
    animation: pulse-success 2s infinite;
}

.pulse-danger {
    animation: pulse-danger 1.5s infinite;
}

/* Refresh button enhancement */
.refresh-btn {
    border: 2px solid rgba(255,255,255,0.3) !important;
    background: rgba(255,255,255,0.1) !important;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.refresh-btn:hover {
    background: rgba(255,255,255,0.2) !important;
    border-color: rgba(255,255,255,0.5) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.refresh-btn:hover i {
    animation: fa-spin 1s linear infinite;
}

/* Description box */
.description-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #17a2b8;
}

/* Table responsive enhancements */
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-label,
    .table-value {
        padding: 8px 10px;
        font-size: 0.85rem;
    }
    
    .network-badge,
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    .table-label {
        width: 35%;
    }
}

/* Smooth transitions for all interactive elements */
.badge,
.table tr,
code,
.btn {
    transition: all 0.3s ease;
}

/* Striped table enhancement */
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
