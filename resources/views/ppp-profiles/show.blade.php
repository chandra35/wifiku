@extends('adminlte::page')

@section('css')
<style>
    /* Modern Card Styling */
    .card {
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 25px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
        padding: 1rem 1.5rem;
    }
    
    .card-header .card-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin: 0;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Profile Info Table */
    .profile-info-table {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .profile-info-table .table {
        margin-bottom: 0;
        background: transparent;
    }
    
    .profile-info-table .table td {
        border: none;
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
    }
    
    .profile-info-table .table th {
        border: none;
        padding: 0.75rem 0.5rem;
        font-weight: 600;
        color: #495057;
        background: transparent;
        width: 35%;
    }
    
    /* Status Badges */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-badge.synced {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
    }
    
    .status-badge.not-synced {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
        box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3);
    }
    
    .status-badge.only-one {
        background: linear-gradient(135deg, #6f42c1, #e83e8c);
        color: white;
        box-shadow: 0 2px 10px rgba(111, 66, 193, 0.3);
    }
    
    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .info-card.network {
        border-left-color: #007bff;
    }
    
    .info-card.limits {
        border-left-color: #28a745;
    }
    
    .info-card h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-list li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f1f1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-list li:last-child {
        border-bottom: none;
    }
    
    .info-list .label {
        font-weight: 500;
        color: #6c757d;
    }
    
    .info-list .value {
        font-weight: 600;
        color: #495057;
    }
    
    /* Action Buttons */
    .action-btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .action-btn.create {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .action-btn.synced {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        cursor: not-allowed;
    }
    
    .action-btn.synced:disabled {
        opacity: 1;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .action-btn.copy {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
    }
    
    .action-btn.users {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }
    
    /* Alert Success for Synced */
    .synced-alert {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border: none;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        text-align: center;
        color: #155724;
        font-weight: 600;
    }
    
    /* Code Elements */
    code {
        background: #f8f9fa;
        color: #e83e8c;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.9rem;
        border: 1px solid #e9ecef;
    }
    
    /* Timeline Improvements */
    .timeline {
        margin: 0;
    }
    
    .timeline .timeline-item {
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    /* Statistics Cards */
    .stats-info-box {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
        transition: all 0.3s ease;
    }
    
    .stats-info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .profile-info-table {
            padding: 0.75rem;
        }
        
        .action-btn {
            margin-bottom: 0.5rem;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            margin-bottom: 0.5rem;
            display: block;
            text-align: center;
        }
    }
</style>
@stop

@section('title', 'PPP Profile Details')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>PPP Profile Details</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPP Profiles
                </a>
                <a href="{{ route('ppp-profiles.edit', $pppProfile->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('ppp-profiles.destroy', $pppProfile->id) }}" method="POST" style="display: inline;" 
                      class="delete-profile-form" data-profile-name="{{ $pppProfile->name }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger delete-profile-btn">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Profile Header Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> PPP Profile: {{ $pppProfile->name }}
            </h3>
            <div class="card-tools">
                @if($pppProfile->only_one)
                    <span class="status-badge only-one">
                        <i class="fas fa-user"></i> Only One Session
                    </span>
                @endif
                
                @if($pppProfile->mikrotik_id)
                    <span class="status-badge synced ml-1">
                        <i class="fas fa-link"></i> Synced to MikroTik
                    </span>
                @else
                    <span class="status-badge not-synced ml-1">
                        <i class="fas fa-unlink"></i> Not Synced
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column - Basic Info -->
                <div class="col-lg-6">
                    <div class="profile-info-table">
                        <h6 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Basic Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Profile Name:</th>
                                <td><strong class="text-primary">{{ $pppProfile->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Router:</th>
                                <td>
                                    <a href="{{ route('routers.show', $pppProfile->router->id) }}" class="text-decoration-none text-info">
                                        <strong>{{ $pppProfile->router->name }}</strong>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-server"></i> {{ $pppProfile->router->ip_address }}:{{ $pppProfile->router->port }}
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>MikroTik ID:</th>
                                <td>
                                    @if($pppProfile->mikrotik_id)
                                        <code>{{ $pppProfile->mikrotik_id }}</code>
                                    @else
                                        <span class="text-muted">Not synced</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>
                                    <strong>{{ $pppProfile->created_at->format('d M Y H:i') }}</strong>
                                    @if($pppProfile->createdBy)
                                        <br><small class="text-muted">by {{ $pppProfile->createdBy->name }}</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Right Column - Network Config -->
                <div class="col-lg-6">
                    <div class="profile-info-table">
                        <h6 class="mb-3"><i class="fas fa-network-wired text-success"></i> Network Configuration</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Local Address:</th>
                                <td>
                                    @if($pppProfile->local_address)
                                        <code>{{ $pppProfile->local_address }}</code>
                                    @else
                                        <span class="text-muted">Auto-assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Remote Address:</th>
                                <td>
                                    @if($pppProfile->remote_address)
                                        <code>{{ $pppProfile->remote_address }}</code>
                                    @else
                                        <span class="text-muted">Auto-assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>DNS Server:</th>
                                <td>
                                    @if($pppProfile->dns_server)
                                        <code>{{ $pppProfile->dns_server }}</code>
                                    @else
                                        <span class="text-muted">Router default</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Rate Limit:</th>
                                <td>
                                    @if($pppProfile->rate_limit)
                                        <span class="badge badge-info">{{ $pppProfile->rate_limit }}</span>
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            @if($pppProfile->comment)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="profile-info-table">
                            <h6 class="mb-3"><i class="fas fa-comment text-warning"></i> Comment</h6>
                            <p class="mb-0 text-muted">{{ $pppProfile->comment }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools"></i> Quick Actions
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if(!$pppProfile->mikrotik_id)
                        <button type="button" class="btn action-btn create btn-block" id="createOnMikrotikBtn">
                            <i class="fas fa-plus"></i> Create on MikroTik
                        </button>
                    @else
                        <button type="button" class="btn action-btn synced btn-block" disabled>
                            <i class="fas fa-check-circle"></i> Already Synced
                        </button>
                    @endif
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn action-btn copy btn-block" id="copyProfileBtn">
                        <i class="fas fa-copy"></i> Copy Profile Info
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn action-btn users btn-block" id="viewUsageBtn">
                        <i class="fas fa-users"></i> View Users
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div id="actionStatus"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Details -->
    <div class="row">
        <!-- Connection Limits -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt"></i> Connection Limits
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-card limits">
                        <h6><i class="fas fa-stopwatch"></i> Session Settings</h6>
                        <ul class="info-list">
                            <li>
                                <span class="label">Bandwidth Limit:</span>
                                <span class="value">
                                    @if($pppProfile->rate_limit)
                                        <span class="badge badge-info">{{ $pppProfile->rate_limit }}</span>
                                    @else
                                        <span class="text-success">Unlimited</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">Session Duration:</span>
                                <span class="value">
                                    @if($pppProfile->session_timeout)
                                        <span class="badge badge-warning">{{ gmdate('H:i:s', $pppProfile->session_timeout) }}</span>
                                        <small class="text-muted">({{ $pppProfile->session_timeout }}s)</small>
                                    @else
                                        <span class="text-success">Unlimited</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">Idle Timeout:</span>
                                <span class="value">
                                    @if($pppProfile->idle_timeout)
                                        <span class="badge badge-secondary">{{ gmdate('H:i:s', $pppProfile->idle_timeout) }}</span>
                                        <small class="text-muted">({{ $pppProfile->idle_timeout }}s)</small>
                                    @else
                                        <span class="text-success">No limit</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">Multiple Sessions:</span>
                                <span class="value">
                                    @if($pppProfile->only_one)
                                        <span class="badge badge-danger">Disabled</span>
                                    @else
                                        <span class="badge badge-success">Allowed</span>
                                    @endif
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Network Configuration -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-network-wired"></i> Network Configuration
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-card network">
                        <h6><i class="fas fa-globe"></i> IP Settings</h6>
                        <ul class="info-list">
                            <li>
                                <span class="label">Local Address:</span>
                                <span class="value">
                                    @if($pppProfile->local_address)
                                        <code>{{ $pppProfile->local_address }}</code>
                                    @else
                                        <span class="text-muted">Auto-assigned</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">Remote Address:</span>
                                <span class="value">
                                    @if($pppProfile->remote_address)
                                        <code>{{ $pppProfile->remote_address }}</code>
                                    @else
                                        <span class="text-muted">Auto-assigned</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">DNS Servers:</span>
                                <span class="value">
                                    @if($pppProfile->dns_server)
                                        <code>{{ $pppProfile->dns_server }}</code>
                                    @else
                                        <span class="text-muted">Router default</span>
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="label">Router:</span>
                                <span class="value">
                                    <strong>{{ $pppProfile->router->name }}</strong>
                                    <br><small class="text-muted">{{ $pppProfile->router->ip_address }}</small>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Statistics Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Usage Statistics
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light action-btn" id="refreshStatsBtn">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="statsContainer">
            <div class="text-center text-muted py-4">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h6>Click refresh to load usage statistics</h6>
                <p class="small">Real-time statistics require MikroTik API integration</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Recent Activity
            </h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="time-label">
                    <span class="bg-green">{{ $pppProfile->created_at->format('d M Y') }}</span>
                </div>
                <div>
                    <i class="fas fa-cogs bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="fas fa-clock"></i> {{ $pppProfile->created_at->format('H:i') }}
                        </span>
                        <h3 class="timeline-header">PPP Profile Created</h3>
                        <div class="timeline-body">
                            PPP profile was created 
                            @if($pppProfile->createdBy)
                                by <strong>{{ $pppProfile->createdBy->name }}</strong>
                            @endif
                        </div>
                    </div>
                </div>

                @if($pppProfile->updated_at != $pppProfile->created_at)
                    <div class="time-label">
                        <span class="bg-yellow">{{ $pppProfile->updated_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-edit bg-yellow"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $pppProfile->updated_at->format('H:i') }}
                            </span>
                            <h3 class="timeline-header">PPP Profile Updated</h3>
                            <div class="timeline-body">
                                PPP profile was last modified
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-lg {
            font-size: 0.9em;
            padding: 0.5em 0.75em;
        }
    </style>
@stop

@section('js')
    <script>
    $(document).ready(function() {
        // Create on MikroTik (only for profiles not yet synced)
        $('#createOnMikrotikBtn').click(function() {
            const btn = $(this);
            const status = $('#actionStatus');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            status.html('');
            
            $.post('{{ route("ppp-profiles.sync-to-mikrotik", $pppProfile->id) }}', {
                _token: '{{ csrf_token() }}',
                force_create: true
            })
            .done(function(response) {
                if (response.success) {
                    status.html('<div class="alert alert-success"><i class="fas fa-check"></i> ' + response.message + '</div>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Create failed';
                status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + message + '</div>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i> Create on MikroTik');
            });
        });

        // Copy profile info
        $('#copyProfileBtn').click(function() {
            const profileInfo = `PPP Profile: {{ $pppProfile->name }}
Router: {{ $pppProfile->router->name }} ({{ $pppProfile->router->ip_address }})
Rate Limit: {{ $pppProfile->rate_limit ?: 'Unlimited' }}
Local Address: {{ $pppProfile->local_address ?: 'Auto-assigned' }}
Remote Address: {{ $pppProfile->remote_address ?: 'Auto-assigned' }}
Session Timeout: {{ $pppProfile->session_timeout ? gmdate('H:i:s', $pppProfile->session_timeout) : 'Unlimited' }}
Only One Session: {{ $pppProfile->only_one ? 'Yes' : 'No' }}`;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(profileInfo).then(function() {
                    $('#actionStatus').html('<div class="alert alert-success"><i class="fas fa-check"></i> Profile information copied to clipboard!</div>');
                    setTimeout(function() {
                        $('#actionStatus').html('');
                    }, 3000);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = profileInfo;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                $('#actionStatus').html('<div class="alert alert-success"><i class="fas fa-check"></i> Profile information copied to clipboard!</div>');
                setTimeout(function() {
                    $('#actionStatus').html('');
                }, 3000);
            }
        });

        // View users using this profile
        $('#viewUsageBtn').click(function() {
            // Redirect to PPPoE secrets with filter for this profile
            window.location.href = '{{ route("pppoe.index") }}?profile={{ $pppProfile->name }}';
        });

        // Refresh statistics
        $('#refreshStatsBtn').click(function() {
            const btn = $(this);
            const container = $('#statsContainer');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            // Show loading state
            container.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Fetching usage statistics...</p>
                </div>
            `);
            
            // Simulate getting profile usage stats
            setTimeout(function() {
                let html = `
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stats-info-box info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Users</span>
                                    <span class="info-box-number">0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 0%"></div>
                                    </div>
                                    <span class="progress-description">Currently online</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-info-box info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-user-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Sessions</span>
                                    <span class="info-box-number">0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 0%"></div>
                                    </div>
                                    <span class="progress-description">All time connections</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-info-box info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-download"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Download</span>
                                    <span class="info-box-number">0 MB</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 0%"></div>
                                    </div>
                                    <span class="progress-description">Total downloaded</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-info-box info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-upload"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Upload</span>
                                    <span class="info-box-number">0 MB</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: 0%"></div>
                                    </div>
                                    <span class="progress-description">Total uploaded</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Real-time statistics require MikroTik API integration. 
                        These are placeholder values for demonstration.
                    </div>
                `;
                
                container.html(html);
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Refresh');
            }, 1500);
        });
        
        // SweetAlert2 Delete Profile
        $('.delete-profile-btn').click(function(e) {
            e.preventDefault();
            const form = $(this).closest('.delete-profile-form');
            const profileName = form.data('profile-name');
            
            Swal.fire({
                title: 'Konfirmasi Hapus PPP Profile',
                html: `Apakah Anda yakin ingin menghapus PPP profile <strong>"${profileName}"</strong>?<br><br>
                       <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Profile ini juga akan dihapus dari MikroTik router dan dapat mempengaruhi pengguna yang menggunakan profile ini.</small>`,
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
                        title: 'Menghapus PPP Profile...',
                        html: 'Sedang menghapus PPP profile dari database dan MikroTik.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                        // Submit via AJAX instead of form submit
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message || 'PPP Profile berhasil dihapus.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    // Redirect to index page after success
                                    window.location.href = '{{ route("ppp-profiles.index") }}';
                                });
                            },
                            error: function(xhr) {
                                let errorMessage = 'Gagal menghapus PPP Profile.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        });
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
@stop
