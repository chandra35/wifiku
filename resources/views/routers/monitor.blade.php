@extends('adminlte::page')

@section('title', 'Router Monitor - ' . $router->name)

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-white">
                <i class="fas fa-desktop"></i> Router Monitor
            </h1>
            <p class="text-white-50 mb-0">{{ $router->name }} ({{ $router->ip_address }})</p>
        </div>
        <div>
            <a href="{{ route('routers.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to Routers
            </a>
            <button type="button" class="btn btn-warning" id="refreshAll">
                <i class="fas fa-sync"></i> Refresh All
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="cpu-load">-</h3>
                    <p>CPU Load</p>
                </div>
                <div class="icon">
                    <i class="fas fa-microchip"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="memory-usage">-</h3>
                    <p>Memory Usage</p>
                </div>
                <div class="icon">
                    <i class="fas fa-memory"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="active-sessions">-</h3>
                    <p>Active PPP Sessions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="uptime">-</h3>
                    <p>Uptime</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Monitoring Panel -->
    <div class="row">
        <div class="col-12">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-terminal"></i> Router Dashboard
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-tool tab-btn active" data-tab="system">
                                <i class="fas fa-info-circle"></i> System
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="interfaces">
                                <i class="fas fa-network-wired"></i> Interfaces
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="ppp">
                                <i class="fas fa-users"></i> PPP
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="ip">
                                <i class="fas fa-globe"></i> IP
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="dhcp">
                                <i class="fas fa-server"></i> DHCP
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="firewall">
                                <i class="fas fa-shield-alt"></i> Firewall
                            </button>
                            <button type="button" class="btn btn-tool tab-btn" data-tab="logs">
                                <i class="fas fa-list"></i> Logs
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-dark text-white" style="min-height: 600px;">
                    <!-- System Info Tab -->
                    <div id="tab-system" class="tab-content active">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-secondary">
                                    <div class="card-header">
                                        <h4><i class="fas fa-server"></i> System Resource</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="system-resource">
                                            <div class="text-center p-4">
                                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                                <p class="mt-2">Loading system information...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-secondary">
                                    <div class="card-header">
                                        <h4><i class="fas fa-microchip"></i> RouterBoard</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="routerboard-info">
                                            <div class="text-center p-4">
                                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                                <p class="mt-2">Loading RouterBoard information...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interfaces Tab -->
                    <div id="tab-interfaces" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-network-wired"></i> Network Interfaces</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadInterfaces()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="interfaces-list">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading interfaces...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PPP Tab -->
                    <div id="tab-ppp" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-users"></i> PPP Sessions</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadPppSessions()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="ppp-sessions">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading PPP sessions...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- IP Tab -->
                    <div id="tab-ip" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-globe"></i> IP Configuration</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadIpAddresses()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="ip-addresses">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading IP configuration...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DHCP Tab -->
                    <div id="tab-dhcp" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-server"></i> DHCP Leases</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadDhcpLeases()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="dhcp-leases">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading DHCP leases...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Firewall Tab -->
                    <div id="tab-firewall" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-shield-alt"></i> Firewall Rules</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadFirewallRules()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="firewall-rules">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading firewall rules...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Tab -->
                    <div id="tab-logs" class="tab-content">
                        <div class="card bg-secondary">
                            <div class="card-header d-flex justify-content-between">
                                <h4><i class="fas fa-list"></i> System Logs</h4>
                                <button class="btn btn-sm btn-primary" onclick="loadSystemLogs()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="system-logs">
                                    <div class="text-center p-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading system logs...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .content-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        margin-bottom: 20px;
        padding: 20px;
    }
    
    .card-dark {
        background: #1a1a1a;
        border: 1px solid #333;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    
    .card-dark .card-header {
        background: #2d2d2d;
        border-bottom: 1px solid #444;
        color: #fff;
    }
    
    .tab-btn {
        color: #bbb !important;
        transition: all 0.3s ease;
        margin: 0 2px;
        padding: 8px 12px;
        border-radius: 4px;
    }
    
    .tab-btn.active {
        color: #fff !important;
        background: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,123,255,0.3);
    }
    
    .tab-btn:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.1);
        transform: translateY(-1px);
    }
    
    .tab-content {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }
    
    .tab-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .status-table {
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        width: 100%;
    }
    
    .status-table th {
        background: #333;
        color: #fff;
        border: 1px solid #555;
        padding: 8px;
        font-weight: bold;
    }
    
    .status-table td {
        background: #2d2d2d;
        color: #fff;
        border: 1px solid #555;
        padding: 8px;
        word-break: break-word;
    }
    
    .status-online {
        color: #28a745;
        font-weight: bold;
    }
    
    .status-offline {
        color: #dc3545;
        font-weight: bold;
    }
    
    .status-disabled {
        color: #6c757d;
        font-weight: bold;
    }
    
    .terminal-output {
        background: #000;
        color: #00ff00;
        font-family: 'Courier New', monospace;
        font-size: 0.85em;
        padding: 15px;
        border-radius: 5px;
        height: 400px;
        overflow-y: auto;
        border: 1px solid #333;
        scrollbar-width: thin;
        scrollbar-color: #444 #000;
    }
    
    .terminal-output::-webkit-scrollbar {
        width: 8px;
    }
    
    .terminal-output::-webkit-scrollbar-track {
        background: #000;
    }
    
    .terminal-output::-webkit-scrollbar-thumb {
        background: #444;
        border-radius: 4px;
    }
    
    .log-entry {
        margin-bottom: 5px;
        padding: 2px 5px;
        border-left: 3px solid #444;
        transition: all 0.2s ease;
    }
    
    .log-entry:hover {
        background: rgba(255,255,255,0.05);
    }
    
    .log-entry.info {
        border-left-color: #17a2b8;
    }
    
    .log-entry.warning {
        border-left-color: #ffc107;
        color: #ffc107;
    }
    
    .log-entry.error {
        border-left-color: #dc3545;
        color: #dc3545;
    }
    
    .small-box {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .card.bg-secondary {
        background: #343a40 !important;
        border: 1px solid #495057;
    }
    
    .card.bg-secondary .card-header {
        background: #495057 !important;
        border-bottom: 1px solid #6c757d;
    }
    
    .btn-group .btn {
        margin-right: 5px;
    }
    
    .text-center.p-4 {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .content-header h1 {
            font-size: 1.5rem;
        }
        
        .card-tools .btn-group {
            flex-wrap: wrap;
        }
        
        .tab-btn {
            font-size: 0.8rem;
            padding: 6px 8px;
        }
        
        .terminal-output {
            height: 300px;
            font-size: 0.75em;
        }
        
        .status-table {
            font-size: 0.8em;
        }
        
        .small-box .inner h3 {
            font-size: 1.5rem;
        }
    }
</style>
@stop

@section('js')
<script>
const routerId = '{{ $router->id }}';
let refreshInterval;

// Test jQuery
console.log('jQuery loaded:', typeof $ !== 'undefined');
console.log('Router ID:', routerId);

$(document).ready(function() {
    console.log('Document ready fired');
    
    // Test AJAX call
    console.log('Testing AJAX...');
    $.get('/test-ajax/{{ $router->id }}')
        .done(function(response) {
            console.log('Test AJAX successful:', response);
        })
        .fail(function(xhr) {
            console.error('Test AJAX failed:', xhr);
        });
    
    // Test system-info directly
    console.log('Testing system-info...');
    $.get('{{ route("routers.monitor.system-info", $router) }}')
        .done(function(response) {
            console.log('System-info AJAX successful:', response);
        })
        .fail(function(xhr) {
            console.error('System-info AJAX failed:', xhr);
        });
    
    // Initialize monitoring
    loadSystemInfo();
    loadRouterStatus();
    
    // Tab switching
    $('.tab-btn').click(function() {
        const tab = $(this).data('tab');
        switchTab(tab);
    });
    
    // Auto refresh every 30 seconds
    refreshInterval = setInterval(function() {
        loadRouterStatus();
        
        // Refresh active tab data
        const activeTab = $('.tab-btn.active').data('tab');
        switch(activeTab) {
            case 'interfaces':
                loadInterfaces();
                break;
            case 'ppp':
                loadPppSessions();
                break;
            case 'system':
                loadSystemInfo();
                break;
        }
    }, 30000);
    
    // Refresh all button
    $('#refreshAll').click(function() {
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        loadRouterStatus();
        loadSystemInfo();
        
        setTimeout(() => {
            $(this).html('<i class="fas fa-sync"></i> Refresh All');
        }, 2000);
    });
});

function switchTab(tab) {
    // Update buttons
    $('.tab-btn').removeClass('active');
    $('[data-tab="' + tab + '"]').addClass('active');
    
    // Update content
    $('.tab-content').removeClass('active');
    $('#tab-' + tab).addClass('active');
    
    // Load tab data
    switch(tab) {
        case 'interfaces':
            loadInterfaces();
            break;
        case 'ppp':
            loadPppSessions();
            break;
        case 'ip':
            loadIpAddresses();
            break;
        case 'dhcp':
            loadDhcpLeases();
            break;
        case 'firewall':
            loadFirewallRules();
            break;
        case 'logs':
            loadSystemLogs();
            break;
        case 'system':
            loadSystemInfo();
            break;
    }
}

function loadRouterStatus() {
    console.log('loadRouterStatus called with routerId:', routerId);
    $.get('/routers/' + routerId + '/status')
        .done(function(data) {
            console.log('Router status response:', data);
            $('#cpu-load').text(data.cpu_load);
            $('#memory-usage').text(data.memory_usage);
            $('#active-sessions').text(data.active_ppp_sessions);
            $('#uptime').text(data.uptime);
        })
        .fail(function(xhr) {
            console.error('Router status failed:', xhr);
            $('#cpu-load, #memory-usage, #active-sessions, #uptime').text('Error');
        });
}

function loadSystemInfo() {
    console.log('loadSystemInfo called');
    $('#system-resource, #routerboard-info').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    const url = '{{ route("routers.monitor.system-info", $router) }}';
    console.log('Requesting URL:', url);
    
    $.get(url)
        .done(function(response) {
            console.log('Response received:', response);
            if (response.success) {
                // System Resource
                let resourceHtml = '<table class="table table-sm status-table">';
                if (response.resource && response.resource.length > 0) {
                    const res = response.resource[0];
                    resourceHtml += '<tr><th>Version</th><td>' + (res.version || '-') + '</td></tr>';
                    resourceHtml += '<tr><th>Build Time</th><td>' + (res['build-time'] || '-') + '</td></tr>';
                    resourceHtml += '<tr><th>CPU</th><td>' + (res.cpu || '-') + '</td></tr>';
                    resourceHtml += '<tr><th>CPU Count</th><td>' + (res['cpu-count'] || '-') + '</td></tr>';
                    resourceHtml += '<tr><th>CPU Frequency</th><td>' + (res['cpu-frequency'] || '-') + ' MHz</td></tr>';
                    resourceHtml += '<tr><th>CPU Load</th><td>' + (res['cpu-load'] || '-') + '%</td></tr>';
                    resourceHtml += '<tr><th>Memory</th><td>' + formatBytes(res['total-memory'] || 0) + '</td></tr>';
                    resourceHtml += '<tr><th>Free Memory</th><td>' + formatBytes(res['free-memory'] || 0) + '</td></tr>';
                }
                resourceHtml += '</table>';
                $('#system-resource').html(resourceHtml);
                
                // RouterBoard Info
                let routerboardHtml = '<table class="table table-sm status-table">';
                if (response.routerboard && response.routerboard.length > 0) {
                    const rb = response.routerboard[0];
                    routerboardHtml += '<tr><th>Model</th><td>' + (rb.model || '-') + '</td></tr>';
                    routerboardHtml += '<tr><th>Serial Number</th><td>' + (rb['serial-number'] || '-') + '</td></tr>';
                    routerboardHtml += '<tr><th>Firmware</th><td>' + (rb['current-firmware'] || '-') + '</td></tr>';
                    routerboardHtml += '<tr><th>Upgrade Firmware</th><td>' + (rb['upgrade-firmware'] || '-') + '</td></tr>';
                }
                routerboardHtml += '</table>';
                $('#routerboard-info').html(routerboardHtml);
            } else {
                console.error('API error:', response.message);
                $('#system-resource, #routerboard-info').html('<div class="text-danger text-center p-3">Error: ' + response.message + '</div>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Request failed:', xhr, status, error);
            console.error('Response text:', xhr.responseText);
            $('#system-resource, #routerboard-info').html('<div class="text-danger text-center p-3">Failed to load data: ' + error + '</div>');
        });
}';
                    routerboardHtml += '<tr><th>Firmware</th><td>' + (rb['current-firmware'] || '-') + '</td></tr>';
                    routerboardHtml += '<tr><th>Upgrade Firmware</th><td>' + (rb['upgrade-firmware'] || '-') + '</td></tr>';
                }
                routerboardHtml += '</table>';
                $('#routerboard-info').html(routerboardHtml);
            }
        })
        .fail(function() {
            $('#system-resource, #routerboard-info').html('<div class="text-danger text-center p-3">Failed to load data</div>');
        });
}

function loadInterfaces() {
    $('#interfaces-list').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.interfaces", $router) }}')
        .done(function(response) {
            if (response.success && response.interfaces) {
                let html = '<table class="table table-sm status-table"><thead><tr>';
                html += '<th>Name</th><th>Type</th><th>Status</th><th>MAC Address</th><th>MTU</th><th>RX/TX</th>';
                html += '</tr></thead><tbody>';
                
                response.interfaces.forEach(function(iface) {
                    const status = iface.disabled === 'true' ? 'disabled' : (iface.running === 'true' ? 'running' : 'stopped');
                    const statusClass = status === 'running' ? 'status-online' : (status === 'disabled' ? 'status-disabled' : 'status-offline');
                    
                    html += '<tr>';
                    html += '<td><strong>' + (iface.name || '-') + '</strong></td>';
                    html += '<td>' + (iface.type || '-') + '</td>';
                    html += '<td><span class="' + statusClass + '">' + status + '</span></td>';
                    html += '<td>' + (iface['mac-address'] || '-') + '</td>';
                    html += '<td>' + (iface.mtu || '-') + '</td>';
                    html += '<td>' + (iface['rx-byte'] ? formatBytes(iface['rx-byte']) : '0') + ' / ' + (iface['tx-byte'] ? formatBytes(iface['tx-byte']) : '0') + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#interfaces-list').html(html);
            } else {
                $('#interfaces-list').html('<div class="text-warning text-center p-3">No interfaces found</div>');
            }
        })
        .fail(function() {
            $('#interfaces-list').html('<div class="text-danger text-center p-3">Failed to load interfaces</div>');
        });
}

function loadPppSessions() {
    $('#ppp-sessions').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.ppp", $router) }}')
        .done(function(response) {
            if (response.success) {
                let html = '<div class="row"><div class="col-md-6">';
                html += '<h5><i class="fas fa-users text-success"></i> Active Sessions (' + (response.active_sessions ? response.active_sessions.length : 0) + ')</h5>';
                html += '<div class="terminal-output" style="height: 300px;">';
                
                if (response.active_sessions && response.active_sessions.length > 0) {
                    response.active_sessions.forEach(function(session) {
                        html += '<div class="log-entry">';
                        html += '<strong>' + (session.name || 'Unknown') + '</strong> - ';
                        html += 'IP: ' + (session.address || 'N/A') + ' | ';
                        html += 'Uptime: ' + (session.uptime || 'N/A') + ' | ';
                        html += 'Service: ' + (session.service || 'N/A');
                        html += '</div>';
                    });
                } else {
                    html += '<div class="text-center text-muted">No active sessions</div>';
                }
                
                html += '</div></div><div class="col-md-6">';
                html += '<h5><i class="fas fa-key text-info"></i> PPP Secrets (' + (response.secrets ? response.secrets.length : 0) + ')</h5>';
                html += '<div class="terminal-output" style="height: 300px;">';
                
                if (response.secrets && response.secrets.length > 0) {
                    response.secrets.forEach(function(secret) {
                        html += '<div class="log-entry">';
                        html += '<strong>' + (secret.name || 'Unknown') + '</strong>';
                        if (secret.profile) html += ' [' + secret.profile + ']';
                        if (secret['local-address']) html += ' - Local: ' + secret['local-address'];
                        if (secret['remote-address']) html += ' - Remote: ' + secret['remote-address'];
                        html += '</div>';
                    });
                } else {
                    html += '<div class="text-center text-muted">No secrets configured</div>';
                }
                
                html += '</div></div></div>';
                $('#ppp-sessions').html(html);
            }
        })
        .fail(function() {
            $('#ppp-sessions').html('<div class="text-danger text-center p-3">Failed to load PPP data</div>');
        });
}

function loadIpAddresses() {
    $('#ip-addresses').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.ip-addresses", $router) }}')
        .done(function(response) {
            if (response.success) {
                let html = '<div class="row"><div class="col-md-6">';
                html += '<h5><i class="fas fa-network-wired text-primary"></i> IP Addresses</h5>';
                html += '<table class="table table-sm status-table"><thead><tr>';
                html += '<th>Address</th><th>Interface</th><th>Network</th>';
                html += '</tr></thead><tbody>';
                
                if (response.addresses && response.addresses.length > 0) {
                    response.addresses.forEach(function(addr) {
                        html += '<tr>';
                        html += '<td>' + (addr.address || '-') + '</td>';
                        html += '<td>' + (addr.interface || '-') + '</td>';
                        html += '<td>' + (addr.network || '-') + '</td>';
                        html += '</tr>';
                    });
                }
                
                html += '</tbody></table></div><div class="col-md-6">';
                html += '<h5><i class="fas fa-route text-warning"></i> Routes</h5>';
                html += '<div class="terminal-output" style="height: 300px;">';
                
                if (response.routes && response.routes.length > 0) {
                    response.routes.forEach(function(route) {
                        html += '<div class="log-entry">';
                        html += (route['dst-address'] || 'default') + ' via ' + (route.gateway || 'direct');
                        if (route.interface) html += ' dev ' + route.interface;
                        if (route.distance) html += ' metric ' + route.distance;
                        html += '</div>';
                    });
                } else {
                    html += '<div class="text-center text-muted">No routes found</div>';
                }
                
                html += '</div></div></div>';
                $('#ip-addresses').html(html);
            }
        })
        .fail(function() {
            $('#ip-addresses').html('<div class="text-danger text-center p-3">Failed to load IP data</div>');
        });
}

function loadDhcpLeases() {
    $('#dhcp-leases').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.dhcp-leases", $router) }}')
        .done(function(response) {
            if (response.success && response.leases) {
                let html = '<table class="table table-sm status-table"><thead><tr>';
                html += '<th>IP Address</th><th>MAC Address</th><th>Host Name</th><th>Status</th><th>Expires</th>';
                html += '</tr></thead><tbody>';
                
                response.leases.forEach(function(lease) {
                    html += '<tr>';
                    html += '<td>' + (lease.address || '-') + '</td>';
                    html += '<td>' + (lease['mac-address'] || '-') + '</td>';
                    html += '<td>' + (lease['host-name'] || '-') + '</td>';
                    html += '<td>' + (lease.status || '-') + '</td>';
                    html += '<td>' + (lease['expires-after'] || '-') + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#dhcp-leases').html(html);
            } else {
                $('#dhcp-leases').html('<div class="text-warning text-center p-3">No DHCP leases found</div>');
            }
        })
        .fail(function() {
            $('#dhcp-leases').html('<div class="text-danger text-center p-3">Failed to load DHCP data</div>');
        });
}

function loadFirewallRules() {
    $('#firewall-rules').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.firewall", $router) }}')
        .done(function(response) {
            if (response.success) {
                let html = '<div class="row"><div class="col-md-6">';
                html += '<h5><i class="fas fa-filter text-danger"></i> Filter Rules</h5>';
                html += '<div class="terminal-output" style="height: 300px;">';
                
                if (response.filter_rules && response.filter_rules.length > 0) {
                    response.filter_rules.forEach(function(rule, index) {
                        html += '<div class="log-entry">';
                        html += '[' + index + '] ' + (rule.action || 'accept');
                        if (rule.chain) html += ' chain=' + rule.chain;
                        if (rule.protocol) html += ' protocol=' + rule.protocol;
                        if (rule['src-address']) html += ' src=' + rule['src-address'];
                        if (rule['dst-address']) html += ' dst=' + rule['dst-address'];
                        if (rule['dst-port']) html += ' dport=' + rule['dst-port'];
                        html += '</div>';
                    });
                } else {
                    html += '<div class="text-center text-muted">No filter rules</div>';
                }
                
                html += '</div></div><div class="col-md-6">';
                html += '<h5><i class="fas fa-exchange-alt text-success"></i> NAT Rules</h5>';
                html += '<div class="terminal-output" style="height: 300px;">';
                
                if (response.nat_rules && response.nat_rules.length > 0) {
                    response.nat_rules.forEach(function(rule, index) {
                        html += '<div class="log-entry">';
                        html += '[' + index + '] ' + (rule.action || 'accept');
                        if (rule.chain) html += ' chain=' + rule.chain;
                        if (rule['src-address']) html += ' src=' + rule['src-address'];
                        if (rule['dst-address']) html += ' dst=' + rule['dst-address'];
                        if (rule['to-addresses']) html += ' to=' + rule['to-addresses'];
                        html += '</div>';
                    });
                } else {
                    html += '<div class="text-center text-muted">No NAT rules</div>';
                }
                
                html += '</div></div></div>';
                $('#firewall-rules').html(html);
            }
        })
        .fail(function() {
            $('#firewall-rules').html('<div class="text-danger text-center p-3">Failed to load firewall data</div>');
        });
}

function loadSystemLogs() {
    $('#system-logs').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.get('{{ route("routers.monitor.logs", $router) }}')
        .done(function(response) {
            if (response.success && response.logs) {
                let html = '<div class="terminal-output">';
                
                response.logs.forEach(function(log) {
                    let logClass = 'log-entry';
                    if (log.topics && log.topics.includes('error')) logClass += ' error';
                    else if (log.topics && log.topics.includes('warning')) logClass += ' warning';
                    else logClass += ' info';
                    
                    html += '<div class="' + logClass + '">';
                    html += '[' + (log.time || 'Unknown') + '] ';
                    html += (log.message || 'No message');
                    html += '</div>';
                });
                
                html += '</div>';
                $('#system-logs').html(html);
                
                // Auto scroll to bottom
                const logsDiv = $('#system-logs .terminal-output');
                logsDiv.scrollTop(logsDiv[0].scrollHeight);
            } else {
                $('#system-logs').html('<div class="text-warning text-center p-3">No logs available</div>');
            }
        })
        .fail(function() {
            $('#system-logs').html('<div class="text-danger text-center p-3">Failed to load logs</div>');
        });
}

function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
@stop
