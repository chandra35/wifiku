@extends('adminlte::page')

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
    <!-- Basic Information Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> PPP Profile: {{ $pppProfile->name }}
            </h3>
            <div class="card-tools">
                @if($pppProfile->only_one)
                    <span class="badge badge-warning badge-lg">
                        <i class="fas fa-user"></i> Only One Session
                    </span>
                @endif
                
                @if($pppProfile->mikrotik_id)
                    <span class="badge badge-success badge-lg ml-1">
                        <i class="fas fa-link"></i> Synced to MikroTik
                    </span>
                @else
                    <span class="badge badge-warning badge-lg ml-1">
                        <i class="fas fa-unlink"></i> Not Synced
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Profile Name:</th>
                            <td><strong class="text-primary">{{ $pppProfile->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Router:</th>
                            <td>
                                <a href="{{ route('routers.show', $pppProfile->router->id) }}" class="text-decoration-none">
                                    {{ $pppProfile->router->name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $pppProfile->router->ip_address }}:{{ $pppProfile->router->port }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Local Address:</th>
                            <td>
                                @if($pppProfile->local_address)
                                    <code>{{ $pppProfile->local_address }}</code>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Remote Address:</th>
                            <td>
                                @if($pppProfile->remote_address)
                                    <code>{{ $pppProfile->remote_address }}</code>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>DNS Server:</th>
                            <td>
                                @if($pppProfile->dns_server)
                                    <code>{{ $pppProfile->dns_server }}</code>
                                @else
                                    <span class="text-muted">Default</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">Rate Limit:</th>
                            <td>
                                @if($pppProfile->rate_limit)
                                    <span class="badge badge-info">{{ $pppProfile->rate_limit }}</span>
                                @else
                                    <span class="text-muted">Unlimited</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Session Timeout:</th>
                            <td>
                                @if($pppProfile->session_timeout)
                                    {{ gmdate('H:i:s', $pppProfile->session_timeout) }}
                                    <small class="text-muted">({{ $pppProfile->session_timeout }} seconds)</small>
                                @else
                                    <span class="text-muted">Unlimited</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Idle Timeout:</th>
                            <td>
                                @if($pppProfile->idle_timeout)
                                    {{ gmdate('H:i:s', $pppProfile->idle_timeout) }}
                                    <small class="text-muted">({{ $pppProfile->idle_timeout }} seconds)</small>
                                @else
                                    <span class="text-muted">No limit</span>
                                @endif
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
                                {{ $pppProfile->created_at->format('d M Y H:i') }}
                                @if($pppProfile->createdBy)
                                    <br><small class="text-muted">by {{ $pppProfile->createdBy->name }}</small>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($pppProfile->comment)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Comment:</h6>
                        <div class="card card-light">
                            <div class="card-body">
                                {{ $pppProfile->comment }}
                            </div>
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
                    @if($pppProfile->mikrotik_id)
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
                    <button type="button" class="btn btn-secondary btn-block" id="copyProfileBtn">
                        <i class="fas fa-copy"></i> Copy Profile Info
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary btn-block" id="viewUsageBtn">
                        <i class="fas fa-users"></i> View Users
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

    <!-- Profile Configuration Summary -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list-alt"></i> Configuration Summary
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Network Configuration</h6>
                    <ul class="list-unstyled">
                        <li><strong>Local Address:</strong> {{ $pppProfile->local_address ?: 'Auto-assigned' }}</li>
                        <li><strong>Remote Address:</strong> {{ $pppProfile->remote_address ?: 'Auto-assigned' }}</li>
                        <li><strong>DNS Servers:</strong> {{ $pppProfile->dns_server ?: 'Router default' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Connection Limits</h6>
                    <ul class="list-unstyled">
                        <li><strong>Bandwidth:</strong> {{ $pppProfile->rate_limit ?: 'Unlimited' }}</li>
                        <li><strong>Session Duration:</strong> 
                            @if($pppProfile->session_timeout)
                                {{ gmdate('H:i:s', $pppProfile->session_timeout) }}
                            @else
                                Unlimited
                            @endif
                        </li>
                        <li><strong>Idle Timeout:</strong> 
                            @if($pppProfile->idle_timeout)
                                {{ gmdate('H:i:s', $pppProfile->idle_timeout) }}
                            @else
                                No limit
                            @endif
                        </li>
                        <li><strong>Multiple Sessions:</strong> {{ $pppProfile->only_one ? 'Disabled' : 'Allowed' }}</li>
                    </ul>
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
                <button type="button" class="btn btn-sm btn-primary" id="refreshStatsBtn">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="statsContainer">
            <div class="text-center text-muted">
                <i class="fas fa-info-circle"></i> Click refresh to load usage statistics
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
        // Sync to MikroTik
        $('#syncToMikrotikBtn, #createOnMikrotikBtn').click(function() {
            const btn = $(this);
            const status = $('#actionStatus');
            const isCreate = btn.attr('id') === 'createOnMikrotikBtn';
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + (isCreate ? 'Creating...' : 'Syncing...'));
            status.html('');
            
            $.post('{{ route("ppp-profiles.sync-to-mikrotik", $pppProfile->id) }}', {
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
            
            // Simulate getting profile usage stats
            setTimeout(function() {
                let html = '<div class="row">';
                html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Active Users</span><span class="info-box-number">0</span></div></div></div>';
                html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span><div class="info-box-content"><span class="info-box-text">Total Connections</span><span class="info-box-number">0</span></div></div></div>';
                html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-download"></i></span><div class="info-box-content"><span class="info-box-text">Data Download</span><span class="info-box-number">0 MB</span></div></div></div>';
                html += '<div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-upload"></i></span><div class="info-box-content"><span class="info-box-text">Data Upload</span><span class="info-box-number">0 MB</span></div></div></div>';
                html += '</div>';
                html += '<div class="text-center text-muted"><small>Statistics require MikroTik API integration</small></div>';
                
                container.html(html);
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Refresh');
            }, 1000);
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
@stop
