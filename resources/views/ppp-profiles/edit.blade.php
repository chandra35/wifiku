@extends('adminlte::page')

@section('title', 'Edit PPP Profile')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit PPP Profile</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPP Profiles
                </a>
                <a href="{{ route('ppp-profiles.show', $pppProfile->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit PPP Profile: {{ $pppProfile->name }}</h3>
            <div class="card-tools">
                @if($pppProfile->mikrotik_id)
                    <span class="badge badge-success">
                        <i class="fas fa-link"></i> Synced to MikroTik
                    </span>
                @else
                    <span class="badge badge-warning">
                        <i class="fas fa-unlink"></i> Not Synced
                    </span>
                @endif
            </div>
        </div>
        <form action="{{ route('ppp-profiles.update', $pppProfile->id) }}" method="POST" id="profileForm">
            @csrf
            @method('PUT')
            <div class="card-body">
                <!-- Profile Info (Read-only) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Profile Name</label>
                            <input type="text" class="form-control" value="{{ $pppProfile->name }}" readonly>
                            <small class="form-text text-muted">Profile name cannot be changed</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Router</label>
                            <input type="text" class="form-control" 
                                   value="{{ $pppProfile->router->name }} ({{ $pppProfile->router->ip_address }})" readonly>
                            <small class="form-text text-muted">Router assignment cannot be changed</small>
                        </div>
                    </div>
                </div>

                <!-- IP Addresses -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="local_address">Local IP Address</label>
                            <input type="text" class="form-control @error('local_address') is-invalid @enderror" 
                                   id="local_address" name="local_address" 
                                   value="{{ old('local_address', $pppProfile->local_address) }}" 
                                   placeholder="192.168.1.1">
                            <small class="form-text text-muted">IP address of the router/server</small>
                            @error('local_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remote_address">Remote IP Address</label>
                            <input type="text" class="form-control @error('remote_address') is-invalid @enderror" 
                                   id="remote_address" name="remote_address" 
                                   value="{{ old('remote_address', $pppProfile->remote_address) }}" 
                                   placeholder="192.168.1.0/24 or specific IP">
                            <small class="form-text text-muted">IP range or specific IP for clients</small>
                            @error('remote_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- DNS and Rate Limit -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dns_server">DNS Server</label>
                            <input type="text" class="form-control @error('dns_server') is-invalid @enderror" 
                                   id="dns_server" name="dns_server" 
                                   value="{{ old('dns_server', $pppProfile->dns_server) }}" 
                                   placeholder="8.8.8.8,8.8.4.4">
                            <small class="form-text text-muted">Comma-separated DNS servers</small>
                            @error('dns_server')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rate_limit">Rate Limit</label>
                            <input type="text" class="form-control @error('rate_limit') is-invalid @enderror" 
                                   id="rate_limit" name="rate_limit" 
                                   value="{{ old('rate_limit', $pppProfile->rate_limit) }}" 
                                   placeholder="1M/1M">
                            <small class="form-text text-muted">Format: upload/download (e.g., 1M/1M)</small>
                            @error('rate_limit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Timeouts -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (seconds)</label>
                            <input type="number" class="form-control @error('session_timeout') is-invalid @enderror" 
                                   id="session_timeout" name="session_timeout" 
                                   value="{{ old('session_timeout', $pppProfile->session_timeout) }}" 
                                   min="0" placeholder="0 for unlimited">
                            <small class="form-text text-muted">Maximum session duration (0 = unlimited)</small>
                            @error('session_timeout')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="idle_timeout">Idle Timeout (seconds)</label>
                            <input type="number" class="form-control @error('idle_timeout') is-invalid @enderror" 
                                   id="idle_timeout" name="idle_timeout" 
                                   value="{{ old('idle_timeout', $pppProfile->idle_timeout) }}" 
                                   min="0" placeholder="0 for unlimited">
                            <small class="form-text text-muted">Idle time before disconnect (0 = unlimited)</small>
                            @error('idle_timeout')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input @error('only_one') is-invalid @enderror" 
                                       id="only_one" name="only_one" value="1" 
                                       {{ old('only_one', $pppProfile->only_one) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="only_one">Only One Session</label>
                            </div>
                            <small class="form-text text-muted">Allow only one active session per user</small>
                            @error('only_one')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Comment -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="3" 
                                      placeholder="Optional description or notes">{{ old('comment', $pppProfile->comment) }}</textarea>
                            @error('comment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Quick Templates -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Quick Templates</label>
                            <div class="btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-info btn-sm">
                                    <input type="radio" name="template" value="basic"> Basic (512k/512k)
                                </label>
                                <label class="btn btn-outline-primary btn-sm">
                                    <input type="radio" name="template" value="standard"> Standard (1M/1M)
                                </label>
                                <label class="btn btn-outline-success btn-sm">
                                    <input type="radio" name="template" value="premium"> Premium (2M/2M)
                                </label>
                                <label class="btn btn-outline-warning btn-sm">
                                    <input type="radio" name="template" value="unlimited"> Unlimited
                                </label>
                            </div>
                            <small class="form-text text-muted">Click a template to auto-fill common configurations</small>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Update Information -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Update Information:</h6>
                    <ul class="mb-0 pl-3">
                        <li>Changes will be synchronized to the MikroTik router</li>
                        <li>Profile name and router assignment cannot be changed</li>
                        <li>Rate limit format: upload/download (e.g., 1M/2M for 1Mbps upload, 2Mbps download)</li>
                        <li>IP addresses can be ranges (192.168.1.0/24) or specific IPs</li>
                    </ul>
                </div>

                <!-- History Information -->
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Created:</strong> {{ $pppProfile->created_at->format('d M Y H:i') }}
                            @if($pppProfile->createdBy)
                                by {{ $pppProfile->createdBy->name }}
                            @endif
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Last Updated:</strong> {{ $pppProfile->updated_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        @if($pppProfile->mikrotik_id)
                            <button type="button" class="btn btn-warning" id="syncToMikrotikBtn">
                                <i class="fas fa-sync"></i> Force Sync to MikroTik
                            </button>
                        @else
                            <button type="button" class="btn btn-success" id="createOnMikrotikBtn">
                                <i class="fas fa-plus"></i> Create on MikroTik
                            </button>
                        @endif
                        <span id="syncStatus" class="ml-2"></span>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update PPP Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Danger Zone</h3>
        </div>
        <div class="card-body">
            <p>Once you delete this PPP profile, there is no going back. This will also remove it from the MikroTik router and may affect users using this profile.</p>
            <form action="{{ route('ppp-profiles.destroy', $pppProfile->id) }}" method="POST" style="display: inline;" 
                  class="delete-profile-form" data-profile-name="{{ $pppProfile->name }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger delete-profile-btn">
                    <i class="fas fa-trash"></i> Delete PPP Profile
                </button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
    $(document).ready(function() {
        // Sync to MikroTik
        $('#syncToMikrotikBtn, #createOnMikrotikBtn').click(function() {
            const btn = $(this);
            const status = $('#syncStatus');
            const isCreate = btn.attr('id') === 'createOnMikrotikBtn';
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + (isCreate ? 'Creating...' : 'Syncing...'));
            status.html('');
            
            $.post('{{ route("ppp-profiles.sync-to-mikrotik", $pppProfile->id) }}', {
                _token: '{{ csrf_token() }}',
                force_create: isCreate
            })
            .done(function(response) {
                if (response.success) {
                    status.html('<span class="text-success"><i class="fas fa-check"></i> ' + response.message + '</span>');
                    if (isCreate) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    status.html('<span class="text-danger"><i class="fas fa-times"></i> ' + response.message + '</span>');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Failed to sync';
                status.html('<span class="text-danger"><i class="fas fa-times"></i> ' + message + '</span>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-' + (isCreate ? 'plus' : 'sync') + '"></i> ' + (isCreate ? 'Create on MikroTik' : 'Force Sync to MikroTik'));
            });
        });

        // Quick templates
        $('input[name="template"]').change(function() {
            const template = $(this).val();
            
            switch(template) {
                case 'basic':
                    $('#rate_limit').val('512k/512k');
                    $('#session_timeout').val('');
                    $('#idle_timeout').val('300');
                    break;
                case 'standard':
                    $('#rate_limit').val('1M/1M');
                    $('#session_timeout').val('');
                    $('#idle_timeout').val('600');
                    break;
                case 'premium':
                    $('#rate_limit').val('2M/2M');
                    $('#session_timeout').val('');
                    $('#idle_timeout').val('900');
                    break;
                case 'unlimited':
                    $('#rate_limit').val('');
                    $('#session_timeout').val('0');
                    $('#idle_timeout').val('0');
                    break;
            }
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
