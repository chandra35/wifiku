@extends('adminlte::page')

@section('title', 'System Monitor - ' . $router->name)

@section('adminlte_css_pre')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-desktop"></i> System Monitor
            </h1>
            <p class="text-muted mb-0">{{ $router->name }} ({{ $router->ip_address }})</p>
        </div>
        <div>
            <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Routers
            </a>
            <button type="button" class="btn btn-primary" id="refreshBtn">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- System Information Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> System Information
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success" id="status-badge">Loading...</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Router Name:</strong></td>
                                    <td id="router-identity">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>RouterOS Version:</strong></td>
                                    <td id="router-version">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>Board:</strong></td>
                                    <td id="router-board">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>CPU:</strong></td>
                                    <td id="router-cpu">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>Uptime:</strong></td>
                                    <td id="router-uptime">Loading...</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Resource Info -->
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>CPU Load:</strong></td>
                                    <td>
                                        <span id="cpu-load">Loading...</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 id="cpu-progress" style="width: 0%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Usage:</strong></td>
                                    <td>
                                        <span id="memory-usage">Loading...</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 id="memory-progress" style="width: 0%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Memory:</strong></td>
                                    <td id="total-memory">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>Free Memory:</strong></td>
                                    <td id="free-memory">Loading...</td>
                                </tr>
                                <tr>
                                    <td><strong>Date & Time:</strong></td>
                                    <td><span id="router-date">Loading...</span> <span id="router-time"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Connection Status Alert -->
    <div class="alert alert-info" id="loading-alert">
        <i class="fas fa-spinner fa-spin"></i> Loading system information...
    </div>
    
    <div class="alert alert-danger" id="error-alert" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i> <span id="error-message"></span>
    </div>
@stop

@section('adminlte_js')
<script>
$(document).ready(function() {
    let routerId = {{ $router->id }};
    let refreshInterval;
    
    // Load system info on page load
    loadSystemInfo();
    
    // Auto refresh every 30 seconds
    refreshInterval = setInterval(loadSystemInfo, 30000);
    
    // Manual refresh button
    $('#refreshBtn').click(function() {
        $(this).find('i').addClass('fa-spin');
        loadSystemInfo();
    });
    
    function loadSystemInfo() {
        $.ajax({
            url: '{{ route("routers.basic-system-info", $router) }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    updateSystemInfo(response.data);
                    $('#loading-alert').hide();
                    $('#error-alert').hide();
                    $('#status-badge').removeClass('badge-danger').addClass('badge-success').text('Connected');
                } else {
                    showError(response.message || 'Failed to load system information');
                }
            },
            error: function(xhr, status, error) {
                showError('Connection error: ' + error);
            },
            complete: function() {
                $('#refreshBtn').find('i').removeClass('fa-spin');
            }
        });
    }
    
    function updateSystemInfo(data) {
        $('#router-identity').text(data.identity || 'Unknown');
        $('#router-version').text(data.version || 'Unknown');
        $('#router-board').text(data.board || 'Unknown');
        $('#router-cpu').text(data.cpu || 'Unknown');
        $('#router-uptime').text(data.uptime || 'Unknown');
        $('#cpu-load').text(data.cpu_load + '%');
        $('#memory-usage').text(data.memory_usage + '%');
        $('#total-memory').text(data.total_memory || 'Unknown');
        $('#free-memory').text(data.free_memory || 'Unknown');
        $('#router-date').text(data.date || 'Unknown');
        $('#router-time').text(data.time || '');
        
        // Update progress bars
        let cpuLoad = parseFloat(data.cpu_load) || 0;
        let memUsage = parseFloat(data.memory_usage) || 0;
        
        $('#cpu-progress').css('width', cpuLoad + '%');
        $('#memory-progress').css('width', memUsage + '%');
        
        // Change progress bar colors based on usage
        let cpuProgressBar = $('#cpu-progress');
        let memProgressBar = $('#memory-progress');
        
        // CPU Load colors
        cpuProgressBar.removeClass('bg-success bg-warning bg-danger');
        if (cpuLoad < 50) {
            cpuProgressBar.addClass('bg-success');
        } else if (cpuLoad < 80) {
            cpuProgressBar.addClass('bg-warning');
        } else {
            cpuProgressBar.addClass('bg-danger');
        }
        
        // Memory Usage colors
        memProgressBar.removeClass('bg-success bg-warning bg-danger');
        if (memUsage < 60) {
            memProgressBar.addClass('bg-success');
        } else if (memUsage < 85) {
            memProgressBar.addClass('bg-warning');
        } else {
            memProgressBar.addClass('bg-danger');
        }
    }
    
    function showError(message) {
        $('#loading-alert').hide();
        $('#error-message').text(message);
        $('#error-alert').show();
        $('#status-badge').removeClass('badge-success').addClass('badge-danger').text('Disconnected');
        
        // Clear data on error
        $('.table td:not(:first-child)').text('N/A');
        $('.progress-bar').css('width', '0%');
    }
    
    // Clear interval when leaving page
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});
</script>
@stop
