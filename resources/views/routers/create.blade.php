@extends('adminlte::page')

@section('title', 'Add Router')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New Router</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('routers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Routers
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Router Information</h3>
        </div>
        <form action="{{ route('routers.store') }}" method="POST" id="routerForm">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Router Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                   id="ip_address" name="ip_address" value="{{ old('ip_address', '192.168.88.1') }}" 
                                   placeholder="192.168.88.1" required>
                            <small class="form-text text-muted">Default MikroTik IP is 192.168.88.1</small>
                            @error('ip_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                   id="username" name="username" value="{{ old('username', 'admin') }}" required>
                            <small class="form-text text-muted">Default is 'admin' or create new API user</small>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            <small class="form-text text-muted">Leave empty if no password is set</small>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="port">API Port</label>
                            <input type="number" class="form-control @error('port') is-invalid @enderror" 
                                   id="port" name="port" value="{{ old('port', 8728) }}" 
                                   min="1" max="65535">
                            <small class="form-text text-muted">Default MikroTik API port is 8728</small>
                            @error('port')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description') }}" 
                                   placeholder="Optional description">
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                @error('connection')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                    </div>
                @enderror

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> MikroTik API Setup Instructions:</h6>
                    <ol class="mb-0 pl-3">
                        <li>Enable API service: <code>/ip service enable api</code></li>
                        <li>Create API user: <code>/user add name=web password=webweb group=full</code></li>
                        <li>Default API port is <strong>8728</strong></li>
                        <li>Make sure firewall allows port 8728</li>
                    </ol>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-info" id="testConnectionBtn">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                        <span id="connectionStatus" class="ml-2"></span>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('routers.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Router
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
// Setup CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    $('#testConnectionBtn').click(function() {
        const btn = $(this);
        const status = $('#connectionStatus');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        status.html('');
        
        const data = {
            ip_address: $('#ip_address').val(),
            username: $('#username').val(),
            password: $('#password').val() || '',
            port: $('#port').val() || 8728,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        console.log('Sending data:', data);
        
        $.post('{{ route("routers.test-connection") }}', data)
            .done(function(response) {
                console.log('Response:', response);
                if (response.success) {
                    status.html('<span class="text-success"><i class="fas fa-check-circle"></i> Connection successful!</span>');
                } else {
                    status.html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + response.message + '</span>');
                }
            })
            .fail(function(xhr) {
                console.error('Request failed:', xhr);
                let errorMsg = 'Connection test failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMsg = 'CSRF token mismatch. Please refresh the page.';
                } else if (xhr.status === 422) {
                    errorMsg = 'Validation error. Please check your inputs.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Please check logs.';
                }
                status.html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + errorMsg + '</span>');
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="fas fa-plug"></i> Test Connection');
            });
    });
});
</script>
@stop
