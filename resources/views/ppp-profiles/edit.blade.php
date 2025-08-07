@extends('adminlte::page')

@section('title', 'Edit PPP Profile')

@section('adminlte_css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

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
            <!-- Hidden fields for required data -->
            <input type="hidden" id="router_id" name="router_id" value="{{ $pppProfile->router_id }}">
            <input type="hidden" name="name" value="{{ $pppProfile->name }}">
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
                            @error('local_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remote_address_type">Remote Address Type</label>
                            <select class="form-control" id="remote_address_type" name="remote_address_type">
                                @php
                                    // Determine the current type based on remote_address value
                                    $currentType = 'manual'; // default
                                    $remoteAddress = old('remote_address', $pppProfile->remote_address);
                                    
                                    if ($remoteAddress) {
                                        // More specific checks for manual IP formats
                                        $isIpAddress = preg_match('/^\d+\.\d+\.\d+\.\d+/', $remoteAddress); // starts with IP
                                        $hasCidr = strpos($remoteAddress, '/') !== false; // contains CIDR slash
                                        $isIpRange = preg_match('/\d+\.\d+\.\d+\.\d+\s*-\s*\d+\.\d+\.\d+\.\d+/', $remoteAddress); // IP-IP range
                                        
                                        $isManual = $isIpAddress || $hasCidr || $isIpRange;
                                        
                                        // If it doesn't look like manual IP format, assume it's a pool name
                                        if (!$isManual) {
                                            $currentType = 'pool';
                                        }
                                    }
                                    
                                    $selectedType = old('remote_address_type', $currentType);
                                @endphp
                                <option value="manual" {{ $selectedType == 'manual' ? 'selected' : '' }}>Manual IP/Range</option>
                                <option value="pool" {{ $selectedType == 'pool' ? 'selected' : '' }}>IP Pool</option>
                            </select>
                        </div>
                        
                        <!-- Manual IP Input -->
                        <div class="form-group" id="remote_address_manual">
                            <label for="remote_address">Remote IP Address</label>
                            <input type="text" class="form-control @error('remote_address') is-invalid @enderror" 
                                   id="remote_address" name="remote_address" 
                                   value="{{ old('remote_address', $pppProfile->remote_address) }}" 
                                   placeholder="192.168.1.0/24 or pool name">
                            <small class="form-text text-muted">IP range for clients or IP Pool name</small>
                            @error('remote_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <!-- IP Pool Selection -->
                        <div class="form-group" id="ip_pool_section" style="display: none;">
                            <label for="remote_address_pool">Select IP Pool</label>
                            <div class="input-group">
                                <select class="form-control" id="remote_address_pool" name="remote_address_pool" disabled>
                                    <option value="">Loading pools...</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-refresh-pools" 
                                            title="Refresh Pools" style="display: none;">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="btn-add-pool">
                                        <i class="fas fa-plus"></i> Add Pool
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Select existing IP pool or create new one</small>
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
                                   id="session_timeout" name="session_timeout" value="{{ old('session_timeout', $pppProfile->session_timeout) }}" 
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

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Important Notes:</h6>
                    <ul class="mb-0 pl-3">
                        <li>This will update a PPP profile directly on the selected MikroTik router</li>
                        <li>Profile name cannot be changed after creation</li>
                        <li>Rate limit format: upload/download (e.g., 1M/2M for 1Mbps upload, 2Mbps download)</li>
                        <li>IP addresses can be ranges (192.168.1.0/24) or specific IPs</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        @if(!$pppProfile->mikrotik_id)
                            <button type="button" class="btn btn-info" id="btn-sync">
                                <i class="fas fa-sync"></i> Sync to Router
                            </button>
                        @else
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Profile sudah tersinkronisasi dengan router
                            </span>
                        @endif
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update PPP Profile
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this PPP Profile? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('ppp-profiles.destroy', $pppProfile) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- IP Pool Management Modal -->
    <div class="modal fade" id="addPoolModal" tabindex="-1" role="dialog" aria-labelledby="addPoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPoolModalLabel">
                        <i class="fas fa-network-wired"></i> IP Pool Management
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="poolTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="existing-pools-tab" data-toggle="tab" href="#existing-pools" role="tab">
                                <i class="fas fa-list"></i> Existing IP Pools
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="create-pool-tab" data-toggle="tab" href="#create-pool" role="tab">
                                <i class="fas fa-plus"></i> Create New Pool
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="poolTabsContent">
                        <!-- Existing Pools Tab -->
                        <div class="tab-pane fade show active" id="existing-pools" role="tabpanel">
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-server"></i> IP Pools from MikroTik Router
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="refresh-pools-list">
                                        <i class="fas fa-sync"></i> Refresh
                                    </button>
                                </div>
                                
                                <!-- Loading State -->
                                <div id="pools-loading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading IP pools from MikroTik...</p>
                                </div>
                                
                                <!-- Pools Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="existing-pools-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="20%">Pool Name</th>
                                                <th width="50%">IP Ranges</th>
                                                <th width="15%">Used IPs</th>
                                                <th width="15%" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="existing-pools-body">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="fas fa-info-circle"></i> Select a router first to load IP pools
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Create Pool Tab -->
                        <div class="tab-pane fade" id="create-pool" role="tabpanel">
                            <div class="mt-3">
                                <form id="addPoolForm">
                                    @csrf
                                    <input type="hidden" id="pool_router_id" name="router_id">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pool_name">Pool Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pool_name" name="name" required
                                                       placeholder="e.g., pool-users">
                                                <small class="form-text text-muted">Must be unique on the router</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pool_comment">Comment</label>
                                                <input type="text" class="form-control" id="pool_comment" name="comment"
                                                       placeholder="Optional description">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pool_ranges">IP Ranges <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="pool_ranges" name="ranges" rows="4" required
                                                  placeholder="192.168.1.100-192.168.1.200&#10;192.168.1.210-192.168.1.250"></textarea>
                                        <small class="form-text text-muted">
                                            <strong>Format:</strong> start_ip-end_ip (one range per line)<br>
                                            <strong>Example:</strong> 192.168.1.100-192.168.1.200
                                        </small>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Tips:</h6>
                                        <ul class="mb-0 pl-3">
                                            <li>Use consecutive IP ranges for better management</li>
                                            <li>Avoid overlapping with existing network assignments</li>
                                            <li>Consider DHCP ranges when defining pools</li>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" id="save-pool-btn" style="display: none;">
                        <i class="fas fa-save"></i> Create Pool
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Pool Confirmation Modal -->
    <div class="modal fade" id="deletePoolModal" tabindex="-1" role="dialog" aria-labelledby="deletePoolModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deletePoolModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete IP Pool
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Delete IP Pool</h4>
                        <p>Are you sure you want to delete IP pool <strong id="delete-pool-name"></strong>?</p>
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-warning"></i> 
                                This will permanently delete the IP pool from MikroTik router. Any PPP profiles using this pool may be affected.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-pool">
                        <i class="fas fa-trash"></i> Delete Pool
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    
    <style>
        /* Custom styles for IP Pool modal */
        #addPoolModal .modal-xl {
            max-width: 1200px;
        }
        
        #addPoolModal .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        
        #addPoolModal .table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            border-top: none;
        }
        
        #existing-pools-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .pool-select-btn, .pool-delete-btn {
            transition: all 0.2s;
        }
        
        .pool-select-btn:hover {
            transform: scale(1.05);
        }
        
        .pool-delete-btn:hover {
            transform: scale(1.05);
        }
        
        /* Tab content padding */
        .tab-content {
            min-height: 400px;
        }
        
        /* Loading spinner */
        #pools-loading {
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        /* Enhanced table styling */
        #existing-pools-table {
            border: 1px solid #dee2e6;
        }
        
        #existing-pools-table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
        }
        
        #existing-pools-table td {
            vertical-align: middle;
            border-color: #dee2e6;
        }
        
        /* Action buttons */
        .btn-group-sm .btn {
            border-radius: 4px;
            margin: 0 1px;
        }
        
        /* Badge styles for pool usage */
        .text-warning {
            color: #856404 !important;
        }
        
        /* Modal enhancements */
        .modal-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-bottom: none;
        }
        
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        
        .modal-header .close:hover {
            opacity: 1;
        }
        
        /* Tab styling */
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-radius: 0.25rem 0.25rem 0 0;
            color: #495057;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-color: #007bff;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
            background-color: #f8f9fa;
        }
        
        .nav-tabs .nav-link.active:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            color: white;
        }
        
        /* Form enhancements */
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Alert styling */
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-color: #bee5eb;
        }
        
        /* Delete modal styling */
        #deletePoolModal .modal-header {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
        }
    </style>
@stop

@section('js')
    <!-- Select2 JS -->
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Check if required libraries are loaded
        console.log('jQuery available:', typeof $ !== 'undefined');
        console.log('SweetAlert available:', typeof Swal !== 'undefined');
        
        $(document).ready(function() {
            let currentPoolToDelete = null;

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Remote Address Type Change Handler
            $('#remote_address_type').change(function() {
                const selectedType = $(this).val();
                console.log('Remote address type changed to:', selectedType);
                
                if (selectedType === 'pool') {
                    console.log('Switching to pool mode');
                    
                    // Debug element existence
                    console.log('Elements found:');
                    console.log('- remote_address_manual:', $('#remote_address_manual').length);
                    console.log('- ip_pool_section:', $('#ip_pool_section').length);
                    console.log('- btn-refresh-pools:', $('#btn-refresh-pools').length);
                    
                    $('#remote_address_manual').hide();
                    $('#ip_pool_section').show().css('display', 'block');
                    $('#btn-refresh-pools').show().css('display', 'inline-block');
                    
                    // Force show with vanilla JS if jQuery fails
                    document.getElementById('ip_pool_section').style.display = 'block';
                    document.getElementById('btn-refresh-pools').style.display = 'inline-block';
                    
                    // Verify visibility
                    console.log('After show/hide:');
                    console.log('- ip_pool_section visible:', $('#ip_pool_section').is(':visible'));
                    console.log('- btn-refresh-pools visible:', $('#btn-refresh-pools').is(':visible'));
                    
                    const routerId = $('#router_id').val();
                    if (routerId) {
                        console.log('Loading pools for router:', routerId);
                        loadIPPools(routerId);
                    } else {
                        console.log('No router selected');
                    }
                } else {
                    console.log('Switching to manual mode');
                    $('#remote_address_manual').show();
                    $('#ip_pool_section').hide();
                    $('#btn-refresh-pools').hide();
                    
                    // Clear the remote_address field when switching to manual if it was a pool name
                    const currentValue = $('#remote_address').val();
                    if (currentValue && !currentValue.includes('/') && !currentValue.includes('-') && 
                        !currentValue.match(/^\d+\.\d+\.\d+\.\d+/)) {
                        // Looks like a pool name, clear it
                        $('#remote_address').val('');
                    }
                }
            });

            // Initialize form state based on current remote_address value and load pools
            const currentRemoteAddress = $('#remote_address').val();
            const initialRouterId = $('#router_id').val();
            const currentType = $('#remote_address_type').val(); // PHP already determined this
            
            console.log('Edit page loaded - Initial state:', {
                remote_address: currentRemoteAddress,
                detected_type: currentType,
                router_id: initialRouterId
            });
            
            // Trigger initial change to setup form UI based on PHP-detected type
            console.log('Triggering initial change event for type:', currentType);
            $('#remote_address_type').trigger('change');

            // Router Selection Change
            $('#router_id').change(function() {
                const routerId = $(this).val();
                const addressType = $('#remote_address_type').val();
                
                if (routerId && addressType === 'pool') {
                    loadIPPools(routerId);
                }
            });

            // Refresh IP Pools
            $('#btn-refresh-pools').click(function() {
                const routerId = $('#router_id').val();
                if (routerId) {
                    loadIPPools(routerId);
                }
            });

            // Add New Pool - Open Modal
            $('#btn-add-pool').click(function() {
                const routerId = $('#router_id').val();
                if (!routerId) {
                    alert('Please select a router first');
                    return;
                }
                
                $('#pool_router_id').val(routerId);
                
                // Reset modal to existing pools tab
                $('#existing-pools-tab').tab('show');
                $('#addPoolModal').modal('show');
                
                // Load existing pools
                loadExistingPoolsFromMikrotik(routerId);
            });

            // Tab switching
            $('#existing-pools-tab').on('shown.bs.tab', function() {
                $('#save-pool-btn').hide();
            });

            $('#create-pool-tab').on('shown.bs.tab', function() {
                $('#save-pool-btn').show();
            });

            // Refresh pools list
            $('#refresh-pools-list').click(function() {
                const routerId = $('#pool_router_id').val();
                if (routerId) {
                    loadExistingPoolsFromMikrotik(routerId);
                }
            });

            // Save Pool
            $('#save-pool-btn').click(function() {
                const formData = $('#addPoolForm').serialize();
                const btn = $(this);
                const originalHtml = btn.html();
                const routerId = $('#pool_router_id').val();
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
                
                $.ajax({
                    url: `{{ route('ppp-profiles.create-ip-pool') }}`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#addPoolForm')[0].reset();
                            
                            // Switch back to existing pools tab and refresh
                            $('#existing-pools-tab').tab('show');
                            loadExistingPoolsFromMikrotik(routerId);
                            
                            // Refresh the dropdown in main form
                            loadIPPools($('#router_id').val());
                            
                            showToast('Pool Created', 'IP Pool created successfully!', 'success');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error creating IP Pool';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert(message);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Select Pool from existing pools
            $(document).on('click', '.pool-select-btn', function() {
                const poolName = $(this).data('pool-name');
                $('#remote_address_pool').val(poolName);
                $('#remote_address').val(poolName);
                
                // Close modal
                $('#addPoolModal').modal('hide');
                
                // Show success message
                showToast('Pool Selected', `IP Pool "${poolName}" has been selected`, 'success');
            });

            // Delete Pool
            $(document).on('click', '.pool-delete-btn', function() {
                const poolName = $(this).data('pool-name');
                const poolId = $(this).data('pool-id');
                
                currentPoolToDelete = {
                    name: poolName,
                    id: poolId
                };
                
                $('#delete-pool-name').text(poolName);
                $('#deletePoolModal').modal('show');
            });

            // Confirm Delete Pool
            $('#confirm-delete-pool').click(function() {
                if (!currentPoolToDelete) return;
                
                const btn = $(this);
                const originalHtml = btn.html();
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: `{{ route('ppp-profiles.delete-ip-pool') }}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        router_id: $('#pool_router_id').val(),
                        pool_name: currentPoolToDelete.name
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deletePoolModal').modal('hide');
                            
                            // Refresh pools list
                            loadExistingPoolsFromMikrotik($('#pool_router_id').val());
                            
                            // Refresh dropdown in main form
                            loadIPPools($('#router_id').val());
                            
                            showToast('Pool Deleted', `IP Pool "${currentPoolToDelete.name}" has been deleted`, 'success');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = 'Error deleting IP Pool';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert(message);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                        currentPoolToDelete = null;
                    }
                });
            });

            // Pool selection change
            $('#remote_address_pool').change(function() {
                const selectedPool = $(this).val();
                $('#remote_address').val(selectedPool);
            });

            // Sync to Router
            $('#btn-sync').click(function() {
                const routerId = $('#router_id').val();
                const profileName = $('#name').val();
                
                if (!routerId || !profileName) {
                    alert('Router and profile name are required');
                    return;
                }
                
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
                
                $.ajax({
                    url: `/ppp-profiles/{{ $pppProfile->id }}/sync`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Sync failed: ' + response.message);
                            $('#btn-sync').prop('disabled', false).html('<i class="fas fa-sync"></i> Sync to Router');
                        }
                    },
                    error: function() {
                        alert('Sync failed');
                        $('#btn-sync').prop('disabled', false).html('<i class="fas fa-sync"></i> Sync to Router');
                    }
                });
            });
        });

        function loadIPPools(routerId) {
            if (!routerId) return;
            
            console.log('loadIPPools called with routerId:', routerId);
            
            $.ajax({
                url: `{{ route('ppp-profiles.get-ip-pools') }}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    router_id: routerId
                },
                beforeSend: function() {
                    console.log('Loading IP pools from server...');
                    $('#remote_address_pool').html('<option value="">Loading pools...</option>').prop('disabled', true);
                },
                success: function(response) {
                    console.log('IP pools response:', response);
                    const select = $('#remote_address_pool');
                    const currentValue = $('#remote_address').val();
                    
                    console.log('Current remote_address value:', currentValue);
                    
                    select.empty().append('<option value="">Select IP Pool...</option>');
                    
                    if (response.success && response.data.length > 0) {
                        select.prop('disabled', false);
                        response.data.forEach(function(pool) {
                            // Create option text with pool name and ranges
                            let optionText = pool.name;
                            if (pool.ranges_string && pool.ranges_string.length > 0) {
                                optionText += ` (${pool.ranges_string})`;
                            }
                            
                            const isSelected = pool.name === currentValue ? 'selected' : '';
                            console.log(`Pool: ${pool.name}, Current: ${currentValue}, Selected: ${isSelected}`);
                            select.append(`<option value="${pool.name}" ${isSelected}>${optionText}</option>`);
                        });
                        console.log('IP pools loaded successfully, dropdown enabled');
                    } else {
                        select.prop('disabled', true);
                        console.log('No IP pools found or response failed');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading IP pools:', xhr);
                    $('#remote_address_pool').html('<option value="">Error loading pools</option>').prop('disabled', true);
                }
            });
        }

        function loadExistingPoolsFromMikrotik(routerId) {
            const tbody = $('#existing-pools-body');
            const loadingDiv = $('#pools-loading');
            
            // Show loading
            tbody.html('');
            loadingDiv.show();
            
            $.ajax({
                url: `{{ route('ppp-profiles.get-ip-pools') }}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    router_id: routerId
                },
                success: function(response) {
                    loadingDiv.hide();
                    tbody.empty();
                    
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(pool) {
                            const rangesText = pool.ranges && pool.ranges.length > 0 
                                ? pool.ranges.join('<br>') 
                                : (pool.ranges_string || 'No ranges defined');
                            
                            const usedIPs = pool.used_ips || 0;
                            const totalIPs = pool.total_ips || 0;
                            const usageText = totalIPs > 0 ? `${usedIPs}/${totalIPs}` : '-';
                            
                            const usageClass = usedIPs > 0 ? 'text-warning' : 'text-muted';
                            
                            tbody.append(`
                                <tr>
                                    <td>
                                        <strong>${pool.name}</strong>
                                        ${pool.comment ? `<br><small class="text-muted">${pool.comment}</small>` : ''}
                                    </td>
                                    <td>
                                        <small>${rangesText}</small>
                                    </td>
                                    <td class="${usageClass}">
                                        <small>${usageText}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-primary btn-sm pool-select-btn" 
                                                    data-pool-name="${pool.name}"
                                                    title="Select this pool">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm pool-delete-btn" 
                                                    data-pool-name="${pool.name}"
                                                    data-pool-id="${pool.id || pool.name}"
                                                    title="Delete this pool">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> No IP pools found on this router
                                    <br><small>Click "Create New Pool" tab to add one</small>
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr) {
                    loadingDiv.hide();
                    let message = 'Error loading IP pools';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    tbody.html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-triangle"></i> ${message}
                                <br><button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="loadExistingPoolsFromMikrotik(${routerId})">
                                    <i class="fas fa-redo"></i> Retry
                                </button>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function showToast(title, message, type) {
            // Simple toast notification
            const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
            
            const toast = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fas ${iconClass}"></i> <strong>${title}:</strong> ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('body').append(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.alert('close');
            }, 3000);
        }

        // Form submission handler
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            console.log('Form submission started');
            
            const formData = new FormData(this);
            const remoteAddressType = $('#remote_address_type').val();
            
            console.log('Remote address type:', remoteAddressType);
            console.log('Form action URL:', $(this).attr('action'));
            
            // Handle remote address based on type
            if (remoteAddressType === 'pool') {
                const selectedPool = $('#remote_address_pool').val();
                console.log('Selected pool:', selectedPool);
                if (selectedPool) {
                    formData.set('remote_address', selectedPool);
                    console.log('Set remote_address to pool name:', selectedPool);
                } else {
                    console.log('No pool selected, checking if remote_address has value');
                    const currentRemoteAddress = $('#remote_address').val();
                    if (currentRemoteAddress) {
                        console.log('Using current remote_address as pool:', currentRemoteAddress);
                        formData.set('remote_address', currentRemoteAddress);
                    } else {
                        alert('Please select an IP Pool');
                        return false;
                    }
                }
            } else {
                const manualAddress = $('#remote_address').val();
                console.log('Manual address:', manualAddress);
                formData.set('remote_address', manualAddress);
            }
            
            // Add remote address type to form data
            formData.set('remote_address_type', remoteAddressType);
            
            // Log all form data
            console.log('Form data being sent:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Show loading state
            const submitBtn = $('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            // Submit form
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function(xhr) {
                    console.log('AJAX request starting...');
                },
                success: function(response) {
                    console.log('Update successful:', response);
                    if (response.success) {
                        // Show success message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'PPP Profile updated successfully and synced to MikroTik',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Redirect back to index
                                window.location.href = '{{ route("ppp-profiles.index") }}';
                            });
                        } else {
                            alert('Success: ' + (response.message || 'PPP Profile updated successfully'));
                            window.location.href = '{{ route("ppp-profiles.index") }}';
                        }
                    } else {
                        throw new Error(response.message || 'Update failed');
                    }
                },
                error: function(xhr) {
                    console.error('Update error:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Status:', xhr.status);
                    console.error('Status text:', xhr.statusText);
                    
                    let errorMessage = 'An error occurred while updating the profile.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('\n');
                        }
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage
                        });
                    } else {
                        alert('Error: ' + errorMessage);
                    }
                },
                complete: function() {
                    console.log('AJAX request completed');
                    // Restore button state
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
            
            return false;
        });
    </script>
@stop
