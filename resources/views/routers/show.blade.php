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
    <!-- Traffic Network Card -->
    <div class="row">
        <!-- Router Information Card -->
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server mr-2"></i>Router Information
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-tool" id="refreshStatus">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-sm">Hostname:</dt>
                                <dd class="col-sm-8 text-sm mb-1">{{ $router->name }}</dd>
                                
                                <dt class="col-sm-4 text-sm">IP Address:</dt>
                                <dd class="col-sm-8 text-sm mb-1">
                                    <code class="text-sm">{{ $router->ip_address }}</code>
                                </dd>
                                
                                <dt class="col-sm-4 text-sm">Username:</dt>
                                <dd class="col-sm-8 text-sm mb-1">{{ $router->username }}</dd>
                                
                                <dt class="col-sm-4 text-sm">API Port:</dt>
                                <dd class="col-sm-8 text-sm mb-1">
                                    <code class="text-sm">{{ $router->port }}</code>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-sm">Status:</dt>
                                <dd class="col-sm-8 text-sm mb-1">
                                    @if($router->status === 'active')
                                        <span class="badge badge-success badge-sm">
                                            <i class="fas fa-check"></i> Active
                                        </span>
                                    @else
                                        <span class="badge badge-danger badge-sm">
                                            <i class="fas fa-times"></i> Inactive
                                        </span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4 text-sm">Connection:</dt>
                                <dd class="col-sm-8 text-sm mb-1">
                                    <span class="connection-status" data-router-id="{{ $router->id }}">
                                        <span class="badge badge-secondary badge-sm">
                                            <i class="fas fa-spinner fa-spin"></i> Checking...
                                        </span>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4 text-sm">Created:</dt>
                                <dd class="col-sm-8 text-xs text-muted mb-1">{{ $router->created_at->format('d/m/Y H:i') }}</dd>
                                
                                <dt class="col-sm-4 text-sm">Updated:</dt>
                                <dd class="col-sm-8 text-xs text-muted mb-1">{{ $router->updated_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($router->status === 'active')
                    <hr>
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-microchip mr-2"></i>System Information
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-sm">System Identity:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="system-identity-text" class="system-identity" data-router-id="{{ $router->id }}">
                                        <small class="text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </small>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-5 text-sm">RouterOS Version:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="routeros-version-text">
                                        @if($router->routeros_version)
                                            <span class="badge badge-info badge-sm">{{ $router->routeros_version }}</span>
                                            @if($router->architecture)
                                                <br><small class="text-muted text-xs">{{ $router->architecture }}</small>
                                            @endif
                                        @else
                                            <small class="text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Loading...
                                            </small>
                                        @endif
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            @if($router->board_name)
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-sm">Hardware Board:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="board-name-text">{{ $router->board_name }}</span>
                                </dd>
                                
                                <dt class="col-sm-5 text-sm">Last System Check:</dt>
                                <dd class="col-sm-7 text-xs text-muted mb-1">{{ $router->updated_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Gateway Information -->
                    <hr>
                    <h5 class="text-success mb-3">
                        <i class="fas fa-route mr-2"></i>Gateway Information
                        <button type="button" class="btn btn-xs btn-outline-success ml-2" id="refreshGatewayInfo">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-sm">Gateway Interface:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="gatewayInterface" class="text-primary font-weight-bold">
                                        <i class="fas fa-spinner fa-spin"></i> Detecting...
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-5 text-sm">Gateway Address:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="gatewayAddress" class="text-dark">
                                        <small class="text-muted">Detecting...</small>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-sm">Interface Status:</dt>
                                <dd class="col-sm-7 text-sm mb-1">
                                    <span id="gatewayStatus" class="badge badge-secondary badge-sm">Unknown</span>
                                </dd>
                                
                                <dt class="col-sm-5 text-sm">Last Updated:</dt>
                                <dd class="col-sm-7 text-xs text-muted mb-1" id="gatewayLastUpdate">Never</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                    
                    <!-- ISP Information -->
                    <hr>
                    <h5 class="text-info mb-3">
                        <i class="fas fa-globe mr-2"></i>ISP Information
                        <button type="button" class="btn btn-xs btn-outline-info ml-2" id="refreshIspInfo">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </h5>
                    <div id="ispInfoContainer">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5 text-sm">ISP Name:</dt>
                                    <dd class="col-sm-7 text-sm mb-1">
                                        <span id="ispName" class="text-primary font-weight-bold">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-5 text-sm">ASN:</dt>
                                    <dd class="col-sm-7 text-sm mb-1">
                                        <span id="ispAsn" class="text-dark">
                                            <small class="text-muted">Loading...</small>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-5 text-sm">Country:</dt>
                                    <dd class="col-sm-7 text-sm mb-1">
                                        <span id="ispCountry" class="text-secondary">
                                            <small class="text-muted">Loading...</small>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5 text-sm">IP Public:</dt>
                                    <dd class="col-sm-7 text-sm mb-1">
                                        <span id="ispPublicIp" class="text-monospace">
                                            <small class="text-muted">Detecting...</small>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-5 text-sm">Data Source:</dt>
                                    <dd class="col-sm-7 text-sm mb-1">
                                        <span id="ispDataSource" class="badge badge-info badge-sm">BGP.tools</span>
                                    </dd>
                                    
                                    <dt class="col-sm-5 text-sm">Last Updated:</dt>
                                    <dd class="col-sm-7 text-xs text-muted mb-1" id="ispLastUpdate">Never</dd>
                                </dl>
                            </div>
                        </div>
                        
                        <!-- Upstream Providers -->
                        <div class="mt-3">
                            <h6 class="text-secondary text-sm mb-2">
                                <i class="fas fa-sitemap mr-2"></i>Upstream Providers
                            </h6>
                            <div id="ispUpstreams" class="text-sm">
                                <span class="text-muted">Loading upstream information...</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($router->description)
                    <hr>
                    <div>
                        <h6 class="text-secondary text-sm mb-2">
                            <i class="fas fa-file-alt mr-2"></i>Description
                        </h6>
                        <p class="text-muted text-sm mb-0">{{ $router->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-md-4">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title text-sm">
                        <i class="fas fa-tools mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical btn-block">
                        <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-sm mb-2">
                            <i class="fas fa-edit mr-1"></i> Edit Router
                        </a>
                        
                        @if($router->status === 'active')
                            <button class="btn btn-info btn-sm mb-2" id="testConnection">
                                <i class="fas fa-network-wired mr-1"></i> Test Connection
                            </button>
                            
                            <button class="btn btn-success btn-sm mb-2" id="getSystemInfo">
                                <i class="fas fa-info-circle mr-1"></i> Get System Info
                            </button>
                        @endif
                        
                        <form action="{{ route('routers.destroy', $router) }}" method="POST" 
                              class="delete-router-form" data-router-name="{{ $router->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm btn-block delete-router-btn">
                                <i class="fas fa-trash mr-1"></i> Delete Router
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interface Traffic Details - Moved to bottom -->
    <div class="row">
        <div class="col-12">
            <div class="card card-secondary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title text-sm">
                        <i class="fas fa-ethernet mr-2"></i>Interface Traffic Details
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-xs btn-tool" id="refreshInterfaceTraffic">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-sm">Interface</th>
                                    <th class="text-sm">Type</th>
                                    <th class="text-sm">Status</th>
                                    <th class="text-sm">TX Bytes</th>
                                    <th class="text-sm">RX Bytes</th>
                                    <th class="text-sm">TX Packets</th>
                                    <th class="text-sm">RX Packets</th>
                                </tr>
                            </thead>
                            <tbody id="interface-traffic-table">
                                <tr>
                                    <td colspan="7" class="text-center text-muted text-sm py-3">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading interface data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
        console.log('Updating system identity for router ID:', routerId);
        
        if (routerId) {
            $.ajax({
                url: '/routers/' + routerId + '/monitor/system-identity',
                method: 'GET',
                success: function(response) {
                    console.log('System identity response:', response);
                    if (response.success) {
                        $('#system-identity-text').html(
                            '<span class="badge badge-primary">' + response.data.identity + '</span>'
                        );
                        
                        if (response.data.version) {
                            $('#routeros-version-text').html(
                                '<span class="badge badge-info">' + response.data.version + '</span>' +
                                (response.data.architecture ? '<br><small class="text-muted">' + response.data.architecture + '</small>' : '')
                            );
                        }
                        
                        if (response.data.board_name) {
                            $('#board-name-text').text(response.data.board_name);
                        }
                    } else {
                        console.error('System identity error:', response.message);
                        $('#system-identity-text').html(
                            '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + response.message + '</small>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
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
                        statusHtml = '<span class="badge badge-success"><i class="fas fa-check"></i> Connected</span>';
                    } else {
                        statusHtml = '<span class="badge badge-danger"><i class="fas fa-times"></i> Disconnected</span>';
                    }
                    $('.connection-status').html(statusHtml);
                },
                error: function() {
                    $('.connection-status').html(
                        '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>'
                    );
                }
            });
        }
    }

    // Update network traffic
    function updateNetworkTraffic() {
        var routerId = $('.system-identity').data('router-id');
        console.log('Updating network traffic for router ID:', routerId);
        
        if (routerId) {
            $.ajax({
                url: '/routers/' + routerId + '/monitor/network-traffic',
                method: 'GET',
                success: function(response) {
                    console.log('Network traffic response:', response);
                    if (response.success && response.data) {
                        var data = response.data;
                        
                        // Update traffic statistics
                        $('#total-traffic').html(data.total_traffic_formatted || '0 B');
                        $('#upload-traffic').html(data.total_tx_formatted || '0 B');
                        $('#download-traffic').html(data.total_rx_formatted || '0 B');
                        $('#active-connections').html(data.active_connections || '0');
                        
                        // Update interface table
                        var tableHtml = '';
                        if (data.interfaces && data.interfaces.length > 0) {
                            data.interfaces.forEach(function(iface) {
                                var statusBadge = iface.running ? 
                                    '<span class="badge badge-success">Running</span>' : 
                                    '<span class="badge badge-secondary">Down</span>';
                                    
                                tableHtml += '<tr>' +
                                    '<td><strong>' + iface.name + '</strong></td>' +
                                    '<td>' + iface.type + '</td>' +
                                    '<td>' + statusBadge + '</td>' +
                                    '<td>' + iface.tx_bytes_formatted + '</td>' +
                                    '<td>' + iface.rx_bytes_formatted + '</td>' +
                                    '<td>' + iface.tx_packets.toLocaleString() + '</td>' +
                                    '<td>' + iface.rx_packets.toLocaleString() + '</td>' +
                                '</tr>';
                            });
                        } else {
                            tableHtml = '<tr><td colspan="7" class="text-center text-muted">No interface data available</td></tr>';
                        }
                        $('#interface-traffic-table').html(tableHtml);
                        
                    } else {
                        console.error('Network traffic error:', response.message);
                        $('#total-traffic').html('<small class="text-danger">Error</small>');
                        $('#upload-traffic').html('<small class="text-danger">Error</small>');
                        $('#download-traffic').html('<small class="text-danger">Error</small>');
                        $('#active-connections').html('<small class="text-danger">Error</small>');
                        $('#interface-traffic-table').html(
                            '<tr><td colspan="7" class="text-center text-danger">' +
                            '<i class="fas fa-exclamation-triangle"></i> ' + (response.message || 'Error loading traffic data') +
                            '</td></tr>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Network traffic AJAX error:', {xhr, status, error});
                    $('#total-traffic').html('<small class="text-danger">Error</small>');
                    $('#upload-traffic').html('<small class="text-danger">Error</small>');
                    $('#download-traffic').html('<small class="text-danger">Error</small>');
                    $('#active-connections').html('<small class="text-danger">Error</small>');
                    $('#interface-traffic-table').html(
                        '<tr><td colspan="7" class="text-center text-danger">' +
                        '<i class="fas fa-times"></i> Failed to load traffic data' +
                        '</td></tr>'
                    );
                }
            });
        } else {
            console.warn('No router ID found for network traffic update');
        }
    }

    // Update gateway traffic function
    // Update gateway information only (simplified)
    function updateGatewayTraffic() {
        var routerId = $('.system-identity').data('router-id');
        
        if (routerId) {
            $.ajax({
                url: '/routers/' + routerId + '/monitor/gateway-traffic',
                type: 'GET',
                timeout: 10000,
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        
                        // Update gateway info in Router Information section
                        $('#gatewayInterface').text(data.interface_name || 'Unknown');
                        $('#gatewayAddress').text(data.gateway_address || 'Unknown');
                        
                        // Update status with proper badge styling
                        const status = data.running ? 'Running' : 'Down';
                        const badgeClass = data.running ? 'badge-success' : 'badge-danger';
                        $('#gatewayStatus').removeClass().addClass('badge ' + badgeClass).text(status);
                        
                        // Update last update time
                        $('#gatewayLastUpdate').text(new Date().toLocaleTimeString());
                        
                    } else {
                        console.error('Gateway info error:', response.message);
                        $('#gatewayInterface').text('Error');
                        $('#gatewayAddress').text('Error');
                        $('#gatewayStatus').removeClass().addClass('badge badge-danger').text('Error');
                        $('#gatewayLastUpdate').text('Error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Gateway info AJAX error:', {xhr, status, error});
                    $('#gatewayInterface').text('Connection Error');
                    $('#gatewayAddress').text('Connection Error');
                    $('#gatewayStatus').removeClass().addClass('badge badge-danger').text('Connection Error');
                    $('#gatewayLastUpdate').text('Connection Error');
                }
            });
        } else {
            console.warn('No router ID found for gateway info update');
        }
    }

    // Update ISP information
    function updateIspInfo() {
        const routerId = "{{ $router->id }}";
        if (routerId) {
            console.log('Fetching ISP info for router:', routerId);
            
            // Show loading state for individual elements
            $('#ispName').html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            $('#ispAsn').html('<small class="text-muted">Loading...</small>');
            $('#ispCountry').html('<small class="text-muted">Loading...</small>');
            $('#ispPublicIp').html('<small class="text-muted">Detecting...</small>');
            $('#ispUpstreams').html('<span class="text-muted">Loading upstream information...</span>');
            $('#ispLastUpdate').text('Loading...');
            
            $.ajax({
                url: `/routers/${routerId}/monitor/isp-info`,
                method: 'GET',
                success: function(data) {
                    console.log('ISP info response:', data);
                    
                    if (data.success && data.data) {
                        // Update ISP Name
                        $('#ispName').html(data.data.isp_name || 'Unknown');
                        
                        // Update ASN
                        if (data.data.asn) {
                            $('#ispAsn').html(`<span class="badge badge-primary">AS${data.data.asn}</span>`);
                        } else {
                            $('#ispAsn').html('<small class="text-muted">Unknown</small>');
                        }
                        
                        // Update Country
                        if (data.data.country) {
                            $('#ispCountry').html(`<span class="badge badge-secondary">${data.data.country}</span>`);
                        } else {
                            $('#ispCountry').html('<small class="text-muted">Unknown</small>');
                        }
                        
                        // Update Public IP
                        if (data.data.public_ip) {
                            $('#ispPublicIp').html(`<code>${data.data.public_ip}</code>`);
                        } else {
                            $('#ispPublicIp').html('<small class="text-muted">Not detected</small>');
                        }
                        
                        // Update Prefix if available
                        if (data.data.prefix) {
                            $('#ispGatewayIp').append(`<br><small class="text-muted">Prefix: ${data.data.prefix}</small>`);
                        }
                        
                        // Update Upstream Providers
                        if (data.data.upstreams && data.data.upstreams.length > 0) {
                            let upstreamHtml = '';
                            data.data.upstreams.forEach(function(upstream) {
                                upstreamHtml += `<span class="badge badge-info mr-1 mb-1">AS${upstream.asn} - ${upstream.name}</span>`;
                            });
                            $('#ispUpstreams').html(upstreamHtml);
                        } else {
                            $('#ispUpstreams').html('<small class="text-muted">No upstream providers found</small>');
                        }
                        
                        // Update Data Source
                        if (data.data.data_source) {
                            $('#ispDataSource').text(data.data.data_source);
                            $('#ispDataSource').removeClass('badge-info badge-warning').addClass('badge-success');
                        }
                        
                        // Update Last Updated
                        if (data.data.last_updated) {
                            $('#ispLastUpdate').text(new Date(data.data.last_updated).toLocaleString());
                        }
                    } else {
                        // Handle error case
                        $('#ispName').html('<span class="text-danger">Error loading</span>');
                        $('#ispAsn').html('<small class="text-danger">Error</small>');
                        $('#ispCountry').html('<small class="text-danger">Error</small>');
                        $('#ispGatewayIp').html('<small class="text-danger">Error</small>');
                        $('#ispUpstreams').html(`<div class="alert alert-warning alert-sm">${data.message || 'Unable to retrieve ISP information'}</div>`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching ISP info:', error);
                    $('#ispName').html('<span class="text-danger">Connection Error</span>');
                    $('#ispAsn').html('<small class="text-danger">Error</small>');
                    $('#ispCountry').html('<small class="text-danger">Error</small>');
                    $('#ispGatewayIp').html('<small class="text-danger">Error</small>');
                    $('#ispUpstreams').html(`<div class="alert alert-danger alert-sm">Error loading ISP information: ${error}</div>`);
                }
            });
        } else {
            console.warn('No router ID found for ISP info update');
        }
    }

    // Initial load
    updateSystemIdentity();
    updateConnectionStatus();
    updateNetworkTraffic();
    updateGatewayTraffic();
    updateIspInfo();

    // Auto-refresh every 30 seconds (except ISP info which refreshes every 5 minutes)
    setInterval(function() {
        updateSystemIdentity();
        updateConnectionStatus();
        updateNetworkTraffic();
        updateGatewayTraffic();
    }, 30000);

    // Auto-refresh ISP info every 5 minutes (less frequent due to caching)
    setInterval(function() {
        updateIspInfo();
    }, 300000);

    // Manual refresh button for system info
    $('#refreshStatus').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateSystemIdentity();
        updateConnectionStatus();
        setTimeout(function() {
            $('#refreshStatus i').removeClass('fa-spin');
        }, 2000);
    });

    // Manual refresh button for traffic
    $('#refreshTraffic').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateNetworkTraffic();
        setTimeout(function() {
            $('#refreshTraffic i').removeClass('fa-spin');
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

    // Manual refresh button for network traffic
    $('#refreshTraffic').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateNetworkTraffic();
        setTimeout(function() {
            $('#refreshTraffic i').removeClass('fa-spin');
        }, 2000);
    });

    // Manual refresh button for gateway info
    $('#refreshGatewayInfo').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateGatewayTraffic();
        setTimeout(function() {
            $('#refreshGatewayInfo i').removeClass('fa-spin');
        }, 2000);
    });

    // Manual refresh button for ISP info
    $('#refreshIspInfo').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateIspInfo();
        setTimeout(function() {
            $('#refreshIspInfo i').removeClass('fa-spin');
        }, 2000);
    });

    // Manual refresh button for interface traffic
    $('#refreshInterfaceTraffic').click(function() {
        $(this).find('i').addClass('fa-spin');
        updateNetworkTraffic();
        setTimeout(function() {
            $('#refreshInterfaceTraffic i').removeClass('fa-spin');
        }, 2000);
    });
});
</script>

<style>
/* AdminLTE Enhanced Styling */
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}

.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}

/* Definition list styling */
dt {
    font-weight: 600;
    color: #495057;
}

dd {
    margin-bottom: 0.5rem;
}

/* Badge responsiveness */
.badge {
    font-size: 0.875rem;
}

/* Code blocks */
code {
    font-size: 0.875rem;
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

/* Button group styling */
.btn-group-vertical .btn {
    margin-bottom: 0.5rem;
}

.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-sm-4, .col-sm-5 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 0.25rem;
    }
    
    .col-sm-7, .col-sm-8 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }
    
    dt {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    dd {
        margin-bottom: 1rem;
        padding-left: 1rem;
    }
}

/* Loading states */
.fa-spin {
    animation: fa-spin 1s infinite linear;
}

/* Card tools styling */
.card-tools .btn-tool {
    color: #6c757d;
}

.card-tools .btn-tool:hover {
    color: #495057;
}

/* Chart container styling */
.chart-container {
    background: white;
    border-radius: 8px;
    padding: 10px;
}

/* Info box enhancements */
.info-box {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-box-icon {
    border-radius: 8px 0 0 8px;
}

/* Form controls in card header */
.card-header .form-control {
    background-color: rgba(255,255,255,0.9);
    border: 1px solid #ced4da;
}

.card-header .btn-group {
    margin-right: 10px;
}
</style>

<script>
$(document).ready(function() {
    // Format bytes function
    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Load interface list
    
    // Auto refresh functions
    setInterval(function() {
        updateSystemIdentity();
        updateConnectionStatus();
        updateNetworkTraffic();
        updateGatewayTraffic();
    }, 10000); // Refresh every 10 seconds
    
    // Initial load
    updateSystemIdentity();
    updateConnectionStatus();
    updateNetworkTraffic();
    updateGatewayTraffic();
    
    // SweetAlert2 Delete Router
    $('.delete-router-btn').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('.delete-router-form');
        const routerName = form.data('router-name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus Router',
            html: `Apakah Anda yakin ingin menghapus router <strong>"${routerName}"</strong>?<br><br>
                   <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait router.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                popup: 'swal2-popup-delete',
                title: 'swal2-title-delete',
                content: 'swal2-content-delete'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Menghapus Router...',
                    html: 'Sedang menghapus router dari sistem.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                form.submit();
            }
        });
    });
});
</script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- Custom SweetAlert2 Styling -->
<style>
.swal2-popup-delete {
    border-radius: 15px !important;
    padding: 2rem !important;
}

.swal2-title-delete {
    color: #dc3545 !important;
    font-weight: 600 !important;
}

.swal2-content-delete {
    font-size: 1rem !important;
    line-height: 1.5 !important;
}

.swal2-confirm {
    background: #dc3545 !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
}

.swal2-cancel {
    background: #6c757d !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
}

.swal2-icon.swal2-warning {
    border-color: #ffc107 !important;
    color: #ffc107 !important;
}

.swal2-loading .swal2-styled.swal2-confirm {
    background: #dc3545 !important;
}
</style>
@endsection

@section('css')
<style>
    /* Custom font sizes and spacing */
    .text-xs { font-size: 0.75rem !important; }
    .text-sm { font-size: 0.875rem !important; }
    .badge-sm { font-size: 0.75rem !important; padding: 0.25rem 0.5rem !important; }
    .btn-xs { 
        padding: 0.125rem 0.25rem !important; 
        font-size: 0.75rem !important; 
        line-height: 1.2 !important; 
    }
    
    /* Improve spacing and alignment */
    dl.row { margin-bottom: 0 !important; }
    dt { font-weight: 500 !important; margin-bottom: 0.25rem !important; }
    dd { margin-bottom: 0.5rem !important; }
    
    /* Consistent code styling */
    code.text-sm { 
        font-size: 0.8rem !important; 
        padding: 0.125rem 0.25rem !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.25rem !important;
    }
    
    /* Better card spacing */
    .card-body { padding: 1rem !important; }
    .card-header h3 { font-size: 1.1rem !important; margin-bottom: 0 !important; }
    
    /* Table improvements */
    .table-sm td, .table-sm th { 
        padding: 0.3rem !important; 
        font-size: 0.875rem !important; 
        white-space: nowrap !important; 
    }
    
    /* Better badge spacing */
    .badge { 
        white-space: nowrap !important; 
        display: inline-block !important;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .col-sm-4, .col-sm-5 { 
            flex: 0 0 35% !important; 
            max-width: 35% !important; 
        }
        .col-sm-7, .col-sm-8 { 
            flex: 0 0 65% !important; 
            max-width: 65% !important; 
        }
    }
</style>
@endsection
