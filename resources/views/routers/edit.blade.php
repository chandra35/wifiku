@extends('adminlte::page')

@section('title', 'Edit Router')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Router</h1>
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
            <h3 class="card-title">Edit Router Information</h3>
        </div>
        <form action="{{ route('routers.update', $router) }}" method="POST" id="routerForm">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Router Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $router->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ip_address">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                   id="ip_address" name="ip_address" value="{{ old('ip_address', $router->ip_address) }}" 
                                   placeholder="192.168.1.1" required>
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
                                   id="username" name="username" value="{{ old('username', $router->username) }}" required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Leave blank to keep current password">
                            <small class="form-text text-muted">Only fill this if you want to change the password</small>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="port">API Port</label>
                            <input type="number" class="form-control @error('port') is-invalid @enderror" 
                                   id="port" name="port" value="{{ old('port', $router->port) }}" 
                                   min="1" max="65535">
                            <small class="form-text text-muted">Default MikroTik API port is 8728</small>
                            @error('port')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $router->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $router->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description', $router->description) }}" 
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
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('routers.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Router
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
