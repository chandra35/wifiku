@extends('adminlte::page')

@section('title', 'Add PPP Profile')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Add New PPP Profile</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to PPP Profiles
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PPP Profile Information</h3>
        </div>
        <form action="{{ route('ppp-profiles.store') }}" method="POST" id="profileForm">
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
                            <label for="name">Profile Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="e.g., 1Mbps, Premium, Basic">
                            <small class="form-text text-muted">Must be unique per router</small>
                            @error('name')
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
                                   placeholder="192.168.1.0/24 or specific IP">
                            <small class="form-text text-muted">IP range for clients</small>
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
                                   id="dns_server" name="dns_server" value="{{ old('dns_server') }}" 
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
                                   id="rate_limit" name="rate_limit" value="{{ old('rate_limit') }}" 
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
                                   id="session_timeout" name="session_timeout" value="{{ old('session_timeout') }}" 
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
                                   id="idle_timeout" name="idle_timeout" value="{{ old('idle_timeout') }}" 
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
                                       id="only_one" name="only_one" value="1" {{ old('only_one') ? 'checked' : '' }}>
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
                                      placeholder="Optional description or notes">{{ old('comment') }}</textarea>
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

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Important Notes:</h6>
                    <ul class="mb-0 pl-3">
                        <li>This will create a PPP profile directly on the selected MikroTik router</li>
                        <li>Profile name must be unique per router</li>
                        <li>Rate limit format: upload/download (e.g., 1M/2M for 1Mbps upload, 2Mbps download)</li>
                        <li>IP addresses can be ranges (192.168.1.0/24) or specific IPs</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create PPP Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
