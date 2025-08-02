@extends('adminlte::page')

@section('title', 'PPPoE Secret Details')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>PPPoE Secret Details</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPPoE Secrets
                </a>
                <a href="{{ route('pppoe.edit', $pppoe->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('pppoe.destroy', $pppoe->id) }}" method="POST" style="display: inline;" 
                      onsubmit="return confirm('Are you sure you want to delete this PPPoE secret? This action cannot be undone and will remove it from the MikroTik router.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Basic Information Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield"></i> PPPoE Secret: {{ $pppoe->username }}
            </h3>
            <div class="card-tools">
                @if($pppoe->disabled)
                    <span class="badge badge-danger badge-lg">
                        <i class="fas fa-times-circle"></i> Disabled
                    </span>
                @else
                    <span class="badge badge-success badge-lg">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                @endif
                
                <span class="badge badge-lg ml-1" id="syncStatusBadge">
                    @if($pppoe->mikrotik_id)
                        <span class="badge badge-info badge-lg">
                            <i class="fas fa-link"></i> Synced to MikroTik
                        </span>
                    @else
                        <span class="badge badge-warning badge-lg">
                            <i class="fas fa-unlink"></i> Not Synced
                        </span>
                    @endif
                </span>
                
                <button type="button" class="btn btn-sm btn-primary ml-2" id="checkSyncStatusBtn">
                    <i class="fas fa-sync"></i> Check Real-time Status
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Username:</th>
                            <td><strong class="text-primary">{{ $pppoe->username }}</strong></td>
                        </tr>
                        <tr>
                            <th>Router:</th>
                            <td>
                                <a href="{{ route('routers.show', $pppoe->router->id) }}" class="text-decoration-none">
                                    {{ $pppoe->router->name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $pppoe->router->ip_address }}:{{ $pppoe->router->port }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Service:</th>
                            <td>
                                <span class="badge badge-primary">{{ strtoupper($pppoe->service) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Profile:</th>
                            <td>
                                @if($pppoe->profile)
                                    <span class="badge badge-info">{{ $pppoe->profile }}</span>
                                @else
                                    <span class="text-muted">No profile assigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($pppoe->disabled)
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Disabled</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Local IP:</th>
                            <td>
                                @if($pppoe->local_address)
                                    <code>{{ $pppoe->local_address }}</code>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Remote IP:</th>
                            <td>
                                @if($pppoe->remote_address)
                                    <code>{{ $pppoe->remote_address }}</code>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>MikroTik ID:</th>
                            <td>
                                @if($pppoe->mikrotik_id)
                                    <code>{{ $pppoe->mikrotik_id }}</code>
                                @else
                                    <span class="text-muted">Not synced</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>
                                {{ $pppoe->created_at->format('d M Y H:i') }}
                                @if($pppoe->createdBy)
                                    <br><small class="text-muted">by {{ $pppoe->createdBy->name }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Updated:</th>
                            <td>{{ $pppoe->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($pppoe->comment)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Comment:</h6>
                        <div class="card card-light">
                            <div class="card-body">
                                {{ $pppoe->comment }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Sync Status Details Card -->
    <div class="card" id="syncStatusCard" style="display: none;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Real-time Sync Status
            </h3>
        </div>
        <div class="card-body" id="syncStatusDetails">
            <!-- Content will be populated by JavaScript -->
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
                    @if($pppoe->mikrotik_id)
                        <button type="button" class="btn btn-warning btn-block" id="syncToMikrotikBtn">
                            <i class="fas fa-sync"></i> Sync to MikroTik
                        </button>
                    @else
                        <button type="button" class="btn btn-success btn-block" id="createOnMikrotikBtn">
                            <i class="fas fa-plus"></i> Create on MikroTik
                        </button>
                    @endif
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary btn-block" id="showPasswordBtn">
                        <i class="fas fa-eye"></i> Show Password
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-secondary btn-block" id="copyCredentialsBtn">
                        <i class="fas fa-copy"></i> Copy Credentials
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <div id="actionStatus"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Statistics Card (if available) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Connection Statistics
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" id="refreshStatsBtn">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="statsContainer">
            <div class="text-center text-muted">
                <i class="fas fa-info-circle"></i> Click refresh to load connection statistics from MikroTik
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
                    <span class="bg-green">{{ $pppoe->created_at->format('d M Y') }}</span>
                </div>
                <div>
                    <i class="fas fa-plus bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="fas fa-clock"></i> {{ $pppoe->created_at->format('H:i') }}
                        </span>
                        <h3 class="timeline-header">PPPoE Secret Created</h3>
                        <div class="timeline-body">
                            PPPoE secret was created 
                            @if($pppoe->createdBy)
                                by <strong>{{ $pppoe->createdBy->name }}</strong>
                            @endif
                        </div>
                    </div>
                </div>

                @if($pppoe->updated_at != $pppoe->created_at)
                    <div class="time-label">
                        <span class="bg-yellow">{{ $pppoe->updated_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-edit bg-yellow"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $pppoe->updated_at->format('H:i') }}
                            </span>
                            <h3 class="timeline-header">PPPoE Secret Updated</h3>
                            <div class="timeline-body">
                                PPPoE secret was last modified
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
        // Check Real-time Sync Status
        $('#checkSyncStatusBtn').click(function() {
            const btn = $(this);
            const statusCard = $('#syncStatusCard');
            const statusDetails = $('#syncStatusDetails');
            const badge = $('#syncStatusBadge');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');
            statusCard.show();
            statusDetails.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Checking sync status with MikroTik...</div>');
            
            $.post('{{ route("pppoe.check-sync-status", $pppoe->id) }}', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    let statusHtml = '';
                    let badgeHtml = '';
                    
                    // Update badge based on sync status
                    switch(response.sync_status) {
                        case 'synced':
                            badgeHtml = '<span class="badge badge-success badge-lg"><i class="fas fa-check-circle"></i> Real-time Synced</span>';
                            statusHtml = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.message + '</div>';
                            break;
                        case 'out_of_sync':
                            badgeHtml = '<span class="badge badge-warning badge-lg"><i class="fas fa-exclamation-triangle"></i> Out of Sync</span>';
                            statusHtml = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ' + response.message + '</div>';
                            
                            if (response.differences && response.differences.length > 0) {
                                statusHtml += '<div class="mt-3"><h6>Differences found:</h6><ul>';
                                response.differences.forEach(function(diff) {
                                    statusHtml += '<li><code>' + diff.replace('_', ' ') + '</code></li>';
                                });
                                statusHtml += '</ul></div>';
                            }
                            break;
                        case 'not_synced':
                            badgeHtml = '<span class="badge badge-danger badge-lg"><i class="fas fa-times-circle"></i> Not Found in MikroTik</span>';
                            statusHtml = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + response.message + '</div>';
                            break;
                        case 'connection_failed':
                            badgeHtml = '<span class="badge badge-secondary badge-lg"><i class="fas fa-unlink"></i> Connection Failed</span>';
                            statusHtml = '<div class="alert alert-danger"><i class="fas fa-unlink"></i> ' + response.message + '</div>';
                            break;
                    }
                    
                    // Show MikroTik data if available
                    if (response.mikrotik_data) {
                        statusHtml += '<div class="mt-3"><h6>MikroTik Data:</h6>';
                        statusHtml += '<div class="row">';
                        statusHtml += '<div class="col-md-6">';
                        statusHtml += '<table class="table table-sm table-bordered">';
                        statusHtml += '<tr><th>ID:</th><td>' + (response.mikrotik_data.id || '-') + '</td></tr>';
                        statusHtml += '<tr><th>Name:</th><td>' + (response.mikrotik_data.name || '-') + '</td></tr>';
                        statusHtml += '<tr><th>Profile:</th><td>' + (response.mikrotik_data.profile || '-') + '</td></tr>';
                        statusHtml += '<tr><th>Service:</th><td>' + (response.mikrotik_data.service || '-') + '</td></tr>';
                        statusHtml += '</table>';
                        statusHtml += '</div>';
                        statusHtml += '<div class="col-md-6">';
                        statusHtml += '<table class="table table-sm table-bordered">';
                        statusHtml += '<tr><th>Local Address:</th><td>' + (response.mikrotik_data.local_address || '-') + '</td></tr>';
                        statusHtml += '<tr><th>Remote Address:</th><td>' + (response.mikrotik_data.remote_address || '-') + '</td></tr>';
                        statusHtml += '<tr><th>Disabled:</th><td>' + (response.mikrotik_data.disabled ? 'Yes' : 'No') + '</td></tr>';
                        statusHtml += '<tr><th>Comment:</th><td>' + (response.mikrotik_data.comment || '-') + '</td></tr>';
                        statusHtml += '</table>';
                        statusHtml += '</div>';
                        statusHtml += '</div>';
                        statusHtml += '</div>';
                    }
                    
                    badge.html(badgeHtml);
                    statusDetails.html(statusHtml);
                } else {
                    statusDetails.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Failed to check sync status';
                statusDetails.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + message + '</div>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Check Real-time Status');
            });
        });

        // Sync to MikroTik
        $('#syncToMikrotikBtn, #createOnMikrotikBtn').click(function() {
            const btn = $(this);
            const status = $('#actionStatus');
            const isCreate = btn.attr('id') === 'createOnMikrotikBtn';
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + (isCreate ? 'Creating...' : 'Syncing...'));
            status.html('');
            
            $.post('{{ route("pppoe.sync-to-mikrotik", $pppoe->id) }}', {
                _token: '{{ csrf_token() }}',
                force_create: isCreate
            })
            .done(function(response) {
                if (response.success) {
                    status.html('<div class="alert alert-success"><i class="fas fa-check"></i> ' + response.message + '</div>');
                    if (isCreate) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Sync failed';
                status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + message + '</div>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-' + (isCreate ? 'plus' : 'sync') + '"></i> ' + (isCreate ? 'Create on MikroTik' : 'Sync to MikroTik'));
            });
        });

        // Show password
        $('#showPasswordBtn').click(function() {
            const btn = $(this);
            const status = $('#actionStatus');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            $.post('{{ route("pppoe.show-password", $pppoe->id) }}', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    status.html('<div class="alert alert-info"><strong>Password:</strong> <code>' + response.password + '</code> <small class="text-muted">(This will be hidden in 30 seconds)</small></div>');
                    setTimeout(function() {
                        status.html('');
                    }, 30000);
                } else {
                    status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> Failed to retrieve password</div>');
                }
            })
            .fail(function() {
                status.html('<div class="alert alert-danger"><i class="fas fa-times"></i> Failed to retrieve password</div>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-eye"></i> Show Password');
            });
        });

        // Copy credentials
        $('#copyCredentialsBtn').click(function() {
            const credentials = 'Username: {{ $pppoe->username }}\nRouter: {{ $pppoe->router->ip_address }}\nService: {{ strtoupper($pppoe->service) }}';
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(credentials).then(function() {
                    $('#actionStatus').html('<div class="alert alert-success"><i class="fas fa-check"></i> Credentials copied to clipboard!</div>');
                    setTimeout(function() {
                        $('#actionStatus').html('');
                    }, 3000);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = credentials;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                $('#actionStatus').html('<div class="alert alert-success"><i class="fas fa-check"></i> Credentials copied to clipboard!</div>');
                setTimeout(function() {
                    $('#actionStatus').html('');
                }, 3000);
            }
        });

        // Refresh statistics
        $('#refreshStatsBtn').click(function() {
            const btn = $(this);
            const container = $('#statsContainer');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            $.post('{{ route("pppoe.get-stats", $pppoe->id) }}', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success && response.data) {
                    const stats = response.data;
                    let html = '<div class="row">';
                    
                    if (stats.status) {
                        html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-signal"></i></span><div class="info-box-content"><span class="info-box-text">Status</span><span class="info-box-number">' + stats.status + '</span></div></div></div>';
                    }
                    
                    if (stats.uptime) {
                        html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Uptime</span><span class="info-box-number">' + stats.uptime + '</span></div></div></div>';
                    }
                    
                    if (stats.bytes_in) {
                        html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-download"></i></span><div class="info-box-content"><span class="info-box-text">Bytes In</span><span class="info-box-number">' + stats.bytes_in + '</span></div></div></div>';
                    }
                    
                    if (stats.bytes_out) {
                        html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-upload"></i></span><div class="info-box-content"><span class="info-box-text">Bytes Out</span><span class="info-box-number">' + stats.bytes_out + '</span></div></div></div>';
                    }
                    
                    html += '</div>';
                    
                    if (html === '<div class="row"></div>') {
                        html = '<div class="text-center text-muted"><i class="fas fa-info-circle"></i> No active connection found</div>';
                    }
                    
                    container.html(html);
                } else {
                    container.html('<div class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i> Failed to load statistics</div>');
                }
            })
            .fail(function() {
                container.html('<div class="text-center text-muted"><i class="fas fa-exclamation-triangle"></i> Failed to load statistics</div>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Refresh');
            });
        });
    });
    </script>
@stop
