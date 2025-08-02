@extends('adminlte::page')

@section('title', 'Add PPPoE Secret')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New PPPoE Secret</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPPoE Secrets
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PPPoE Secret Information</h3>
        </div>
        <form action="{{ route('pppoe.store') }}" method="POST" id="pppoeForm">
            @csrf
            <div class="card-body">
                <!-- Router Selection -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="router_id">Select Router <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('router_id') is-invalid @enderror" 
                                    id="router_id" name="router_id" required>
                                <option value="">Select Router</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" 
                                            {{ old('router_id') == $router->id ? 'selected' : '' }}>
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
                            </select>
                            <small class="form-text text-muted">Select router first to load profiles</small>
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
                                   id="username" name="username" value="{{ old('username') }}" required>
                            <small class="form-text text-muted">Must be unique per router</small>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="generatePassword">
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Minimum 6 characters</small>
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
                                <option value="pppoe" {{ old('service', 'pppoe') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                <option value="pptp" {{ old('service') == 'pptp' ? 'selected' : '' }}>PPTP</option>
                                <option value="l2tp" {{ old('service') == 'l2tp' ? 'selected' : '' }}>L2TP</option>
                                <option value="ovpn" {{ old('service') == 'ovpn' ? 'selected' : '' }}>OpenVPN</option>
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
                                <option value="0" {{ old('disabled', '0') == '0' ? 'selected' : '' }}>Active</option>
                                <option value="1" {{ old('disabled') == '1' ? 'selected' : '' }}>Disabled</option>
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
                                   id="local_address" name="local_address" value="{{ old('local_address') }}" 
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
                                   id="remote_address" name="remote_address" value="{{ old('remote_address') }}" 
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
                                      placeholder="Optional description or notes">{{ old('comment') }}</textarea>
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

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Important Notes:</h6>
                    <ul class="mb-0 pl-3">
                        <li>This will create a PPPoE secret directly on the selected MikroTik router</li>
                        <li>Username must be unique per router</li>
                        <li>Make sure the router is accessible and API is enabled</li>
                        <li>Profile settings will override local/remote IP addresses if specified</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create PPPoE Secret
                        </button>
                    </div>
                </div>
            </div>
        </form>
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

        // Load profiles when router is selected
        $('#router_id').change(function() {
            const routerId = $(this).val();
            const profileSelect = $('#profile');
            
            if (routerId) {
                // Show loading state
                profileSelect.empty().append('<option value="">Loading profiles...</option>').prop('disabled', true);
                
                // Load profiles
                $.post('{{ route("pppoe.get-profiles") }}', {
                    router_id: routerId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    console.log('Profile response:', response);
                    profileSelect.empty().append('<option value="">Select Profile (Optional)</option>');
                    
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(profile) {
                            profileSelect.append('<option value="' + profile.name + '">' + profile.name + '</option>');
                        });
                        console.log('Loaded ' + response.data.length + ' profiles');
                    } else {
                        profileSelect.append('<option value="" disabled>No profiles found</option>');
                        console.log('No profiles found in response');
                    }
                })
                .fail(function(xhr) {
                    console.error('Failed to load profiles:', xhr);
                    console.error('Response text:', xhr.responseText);
                    profileSelect.empty().append('<option value="" disabled>Failed to load profiles</option>');
                    
                    // Show error message
                    let errorMessage = 'Failed to load profiles from router';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    // You can add a toast notification here if needed
                    console.error(errorMessage);
                })
                .always(function() {
                    profileSelect.prop('disabled', false);
                });
            } else {
                profileSelect.empty().append('<option value="">Select Profile (Optional)</option>').prop('disabled', false);
            }
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
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters');
                e.preventDefault();
                return false;
            }
        });
    });
    </script>
@stop
