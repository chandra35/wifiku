@extends('adminlte::page')

@section('title', 'Edit PPPoE Secret')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit PPPoE Secret</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPPoE Secrets
                </a>
                <a href="{{ route('pppoe.show', $pppoe->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit PPPoE Secret: {{ $pppoe->username }}</h3>
            <div class="card-tools">
                @if($pppoe->mikrotik_id)
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
        <form action="{{ route('pppoe.update', $pppoe->id) }}" method="POST" id="pppoeForm">
            @csrf
            @method('PUT')
            <div class="card-body">
                <!-- Router Selection -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="router_id">Router <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('router_id') is-invalid @enderror" 
                                    id="router_id" name="router_id" required>
                                <option value="">Select Router</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" 
                                            {{ old('router_id', $pppoe->router_id) == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            @error('router_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="profile">PPP Profile</label>
                            <select class="form-control @error('profile') is-invalid @enderror" 
                                    id="profile" name="profile">
                                <option value="">Select Profile (Optional)</option>
                                @if($pppoe->profile)
                                    <option value="{{ $pppoe->profile }}" selected>{{ $pppoe->profile }}</option>
                                @endif
                            </select>
                            <small class="form-text text-muted">Change router to reload profiles</small>
                            @error('profile')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                   id="username" name="username" value="{{ old('username', $pppoe->username) }}" required>
                            <small class="form-text text-muted">Must be unique per router</small>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Leave empty to keep current password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="generatePassword">
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Leave empty to keep current password. Minimum 6 characters if changing</small>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Service and Status -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service">Service Type</label>
                            <select class="form-control @error('service') is-invalid @enderror" 
                                    id="service" name="service">
                                <option value="pppoe" {{ old('service', $pppoe->service) == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                <option value="pptp" {{ old('service', $pppoe->service) == 'pptp' ? 'selected' : '' }}>PPTP</option>
                                <option value="l2tp" {{ old('service', $pppoe->service) == 'l2tp' ? 'selected' : '' }}>L2TP</option>
                                <option value="ovpn" {{ old('service', $pppoe->service) == 'ovpn' ? 'selected' : '' }}>OpenVPN</option>
                            </select>
                            @error('service')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="disabled">Status</label>
                            <select class="form-control @error('disabled') is-invalid @enderror" 
                                    id="disabled" name="disabled">
                                <option value="0" {{ old('disabled', $pppoe->disabled) == '0' ? 'selected' : '' }}>Active</option>
                                <option value="1" {{ old('disabled', $pppoe->disabled) == '1' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error('disabled')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
                                   value="{{ old('local_address', $pppoe->local_address) }}" 
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
                                   value="{{ old('remote_address', $pppoe->remote_address) }}" 
                                   placeholder="192.168.1.100">
                            <small class="form-text text-muted">IP address assigned to the client</small>
                            @error('remote_address')
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
                                      placeholder="Optional description or notes">{{ old('comment', $pppoe->comment) }}</textarea>
                            @error('comment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
                        <li>Username changes require unique value per router</li>
                        <li>Leave password empty to keep current password</li>
                        <li>Profile changes will override IP address settings</li>
                    </ul>
                </div>

                <!-- History Information -->
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Created:</strong> {{ $pppoe->created_at->format('d M Y H:i') }}
                            @if($pppoe->createdBy)
                                by {{ $pppoe->createdBy->name }}
                            @endif
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Last Updated:</strong> {{ $pppoe->updated_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        @if($pppoe->mikrotik_id)
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
                        <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update PPPoE Secret
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
            <p>Once you delete this PPPoE secret, there is no going back. This will also remove it from the MikroTik router.</p>
            <form action="{{ route('pppoe.destroy', $pppoe->id) }}" method="POST" style="display: inline;" 
                  onsubmit="return confirm('Are you sure you want to delete this PPPoE secret? This action cannot be undone and will remove it from the MikroTik router.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete PPPoE Secret
                </button>
            </form>
        </div>
    </div>
@stop

@section('css')
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
    <!-- Select2 JS -->
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Select router...',
            allowClear: true
        });

        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        // Generate random password
        $('#generatePassword').click(function() {
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let password = '';
            for (let i = 0; i < 8; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            $('#password').val(password);
        });

        // Load profiles when router is changed
        $('#router_id').change(function() {
            const routerId = $(this).val();
            const currentRouterId = '{{ $pppoe->router_id }}';
            const profileSelect = $('#profile');
            
            if (routerId && routerId !== currentRouterId) {
                // Load profiles for new router
                $.post('{{ route("pppoe.get-profiles") }}', {
                    router_id: routerId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    profileSelect.empty().append('<option value="">Select Profile (Optional)</option>');
                    
                    if (response.success && response.data) {
                        response.data.forEach(function(profile) {
                            profileSelect.append('<option value="' + profile.name + '">' + profile.name + '</option>');
                        });
                    }
                })
                .fail(function() {
                    profileSelect.empty().append('<option value="">Failed to load profiles</option>');
                });
            }
        });

        // Sync to MikroTik
        $('#syncToMikrotikBtn, #createOnMikrotikBtn').click(function() {
            const btn = $(this);
            const status = $('#syncStatus');
            const isCreate = btn.attr('id') === 'createOnMikrotikBtn';
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + (isCreate ? 'Creating...' : 'Syncing...'));
            status.html('');
            
            $.post('{{ route("pppoe.sync-to-mikrotik", $pppoe->id) }}', {
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

        // Form validation
        $('#pppoeForm').submit(function(e) {
            const username = $('#username').val();
            const password = $('#password').val();
            
            if (username.length < 3) {
                alert('Username must be at least 3 characters');
                e.preventDefault();
                return false;
            }
            
            if (password && password.length < 6) {
                alert('Password must be at least 6 characters if provided');
                e.preventDefault();
                return false;
            }
        });
    });
    </script>
@stop
