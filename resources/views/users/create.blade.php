@extends('adminlte::page')

@section('title', 'Add User')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New User</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <form action="{{ route('users.store') }}" method="POST" id="userForm">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <small class="form-text text-muted">Minimum 8 characters</small>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role_id">Role <span class="text-danger">*</span></label>
                            <select class="form-control @error('role_id') is-invalid @enderror" 
                                    id="role_id" name="role_id" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                            {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="routers">Assign Routers</label>
                            <select class="form-control select2" 
                                    id="routers" name="routers[]" multiple>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}"
                                            {{ in_array($router->id, old('routers', [])) ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select routers this user can manage</small>
                            @error('routers')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> User Role Information:</h6>
                    <ul class="mb-0 pl-3">
                        <li><strong>Super Admin:</strong> Full access to all features including user and router management</li>
                        <li><strong>Admin:</strong> Can manage PPPoE secrets for assigned routers only</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
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
            placeholder: 'Select routers...',
            allowClear: true
        });

        // Password confirmation validation
        $('#password_confirmation').on('keyup', function() {
            var password = $('#password').val();
            var confirmPassword = $(this).val();
            
            if (password !== confirmPassword) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });

        // Role change handler
        $('#role_id').on('change', function() {
            var selectedRole = $(this).find('option:selected').text();
            var routersGroup = $('#routers').closest('.form-group');
            
            if (selectedRole === 'Super Admin') {
                routersGroup.find('small').text('Super Admin has access to all routers');
                $('#routers').prop('disabled', true);
            } else {
                routersGroup.find('small').text('Select routers this user can manage');
                $('#routers').prop('disabled', false);
            }
        });

        // Trigger role change on page load
        $('#role_id').trigger('change');
    });
    </script>
@stop
