@extends('adminlte::page')

@section('css')
<style>
    .form-section {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    
    .form-section-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 0.75rem 1rem;
        margin: 0;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .form-section-body {
        padding: 1.5rem;
        background: #f8f9fa;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .required-field::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }
    
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    .card-modern {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header-modern {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }
    
    .btn-submit {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 25px;
    }
    
    .alert-info-custom {
        background: linear-gradient(135deg, #d1ecf1 0%, #b8daff 100%);
        border-color: #b8daff;
        color: #0c5460;
    }
</style>
@stop

@section('title', 'Add PPPoE Secret')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="text-primary">
                <i class="fas fa-plus-circle"></i> Add New PPPoE Secret
            </h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('pppoe.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Secrets
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fas fa-check"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card card-modern">
        <div class="card-header card-header-modern">
            <h3 class="card-title mb-0">
                <i class="fas fa-user-secret"></i> PPPoE Secret Configuration
            </h3>
        </div>
        <form action="{{ route('pppoe.store') }}" method="POST" id="pppoeForm">
            @csrf
            <div class="card-body p-0">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h6 class="form-section-header">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="router_id" class="required-field">Router</label>
                                    <select class="form-control form-control-lg @error('router_id') is-invalid @enderror" 
                                            id="router_id" name="router_id" required>
                                        <option value="">-- Select Router --</option>
                                        @foreach($routers as $router)
                                            <option value="{{ $router->id }}" 
                                                    {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                                {{ $router->name }} ({{ $router->ip_address }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="help-text">Choose the MikroTik router where this secret will be created</div>
                                    @error('router_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service">Service Type</label>
                                    <select class="form-control form-control-lg @error('service') is-invalid @enderror" 
                                            id="service" name="service">
                                        <option value="pppoe" {{ old('service', 'pppoe') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                        <option value="pptp" {{ old('service') == 'pptp' ? 'selected' : '' }}>PPTP</option>
                                        <option value="l2tp" {{ old('service') == 'l2tp' ? 'selected' : '' }}>L2TP</option>
                                        <option value="ovpn" {{ old('service') == 'ovpn' ? 'selected' : '' }}>OpenVPN</option>
                                    </select>
                                    <div class="help-text">Select the VPN service type</div>
                                    @error('service')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Authentication Section -->
                <div class="form-section">
                    <h6 class="form-section-header">
                        <i class="fas fa-key"></i> Authentication Credentials
                    </h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="required-field">Username</label>
                                    <input type="text" class="form-control form-control-lg @error('username') is-invalid @enderror" 
                                           id="username" name="username" value="{{ old('username') }}" required
                                           placeholder="Enter unique username">
                                    <div class="help-text">Username must be unique per router</div>
                                    @error('username')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="required-field">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                               id="password" name="password" value="{{ old('password') }}" required
                                               placeholder="Enter password">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="help-text">Strong password recommended</div>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Network Configuration Section -->
                <div class="form-section">
                    <h6 class="form-section-header">
                        <i class="fas fa-network-wired"></i> Network Configuration
                    </h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile">PPP Profile</label>
                                    <select class="form-control form-control-lg @error('profile') is-invalid @enderror" 
                                            id="profile" name="profile">
                                        <option value="">-- Select Profile (Optional) --</option>
                                    </select>
                                    <div class="help-text">Select router first to load available profiles</div>
                                    @error('profile')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="local_address">Local IP Address</label>
                                    <input type="text" class="form-control form-control-lg @error('local_address') is-invalid @enderror" 
                                           id="local_address" name="local_address" value="{{ old('local_address') }}"
                                           placeholder="e.g., 192.168.1.1">
                                    <div class="help-text">Router's IP for this connection (optional)</div>
                                    @error('local_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="remote_address">Remote IP Address</label>
                                    <input type="text" class="form-control form-control-lg @error('remote_address') is-invalid @enderror" 
                                           id="remote_address" name="remote_address" value="{{ old('remote_address') }}"
                                           placeholder="e.g., 192.168.1.100">
                                    <div class="help-text">Client's IP address (optional)</div>
                                    @error('remote_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comment">Comment</label>
                                    <textarea class="form-control @error('comment') is-invalid @enderror" 
                                              id="comment" name="comment" rows="3"
                                              placeholder="Optional description or notes">{{ old('comment') }}</textarea>
                                    <div class="help-text">Optional description for this secret</div>
                                    @error('comment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-section">
                    <h6 class="form-section-header">
                        <i class="fas fa-toggle-on"></i> Status & Options
                    </h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-lg">
                                        <input type="checkbox" class="custom-control-input" id="disabled" name="disabled" value="1"
                                               {{ old('disabled') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="disabled">
                                            Disabled
                                        </label>
                                    </div>
                                    <div class="help-text">Check to create the secret in disabled state</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-lg">
                                        <input type="checkbox" class="custom-control-input" id="sync_to_mikrotik" 
                                               name="sync_to_mikrotik" value="1" checked>
                                        <label class="custom-control-label" for="sync_to_mikrotik">
                                            Sync to MikroTik
                                        </label>
                                    </div>
                                    <div class="help-text">Automatically create this secret on the MikroTik router</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Form Actions -->
            <div class="card-footer bg-light text-center py-4">
                <button type="submit" class="btn btn-primary btn-submit btn-lg mr-2">
                    <i class="fas fa-save"></i> Create PPPoE Secret
                </button>
                <a href="{{ route('pppoe.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Load profiles when router is selected
    $('#router_id').change(function() {
        const routerId = $(this).val();
        const profileSelect = $('#profile');
        
        if (routerId) {
            // Show loading state
            profileSelect.empty()
                .append('<option value="">Loading profiles...</option>')
                .prop('disabled', true);
            
            // Load profiles from router
            $.ajax({
                url: '{{ route("pppoe.get-profiles") }}',
                type: 'POST',
                data: {
                    router_id: routerId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    profileSelect.empty()
                        .append('<option value="">-- Select Profile (Optional) --</option>')
                        .prop('disabled', false);
                    
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(profile) {
                            profileSelect.append(`<option value="${profile.name}">${profile.name}</option>`);
                        });
                    } else {
                        profileSelect.append('<option value="" disabled>No profiles available</option>');
                    }
                },
                error: function(xhr) {
                    profileSelect.empty()
                        .append('<option value="" disabled>Failed to load profiles</option>')
                        .prop('disabled', false);
                    
                    const message = xhr.responseJSON?.message || 'Failed to load profiles';
                    console.error('Profile loading error:', message);
                }
            });
        } else {
            profileSelect.empty()
                .append('<option value="">-- Select Profile (Optional) --</option>')
                .prop('disabled', false);
        }
    });

    // Form validation and submission
    $('#pppoeForm').submit(function(e) {
        e.preventDefault();
        
        const username = $('#username').val().trim();
        const password = $('#password').val();
        const routerId = $('#router_id').val();
        
        // Basic validation
        if (!routerId) {
            Swal.fire('Validation Error', 'Please select a router', 'error');
            return false;
        }
        
        if (username.length < 3) {
            Swal.fire('Validation Error', 'Username must be at least 3 characters', 'error');
            return false;
        }
        
        if (password.length < 4) {
            Swal.fire('Validation Error', 'Password must be at least 4 characters', 'error');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Secret...');
        
        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'PPPoE Secret created successfully',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("pppoe.index") }}';
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to create secret', 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'Failed to create PPPoE Secret';
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        const errorList = Object.values(errors).flat();
                        message = errorList.join('<br>');
                    }
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    html: message,
                    icon: 'error'
                });
                
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Generate random password
    function generatePassword(length = 8) {
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let password = '';
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return password;
    }
    
    // Auto-generate username based on certain patterns if needed
    $('#username').on('blur', function() {
        const username = $(this).val().trim();
        if (username && !$('#password').val()) {
            // Optionally auto-generate password when username is entered
            // Uncomment the line below if you want this feature
            // $('#password').val(generatePassword());
        }
    });
});
</script>
@stop
