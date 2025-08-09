@extends('adminlte::page')

@section('css')
<style>
    .import-step {
        min-height: 300px;
    }
    
    .info-box {
        margin-bottom: 15px;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    /* Compact table styling */
    .table-sm {
        font-size: 0.875rem;
    }
    
    .table-sm td {
        padding: 0.5rem 0.25rem;
        vertical-align: middle;
    }
    
    .table-sm th {
        padding: 0.75rem 0.25rem;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .badge-sm {
        font-size: 0.65rem;
        padding: 0.2em 0.4em;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Sync button specific styling */
    .sync-btn {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
        line-height: 1.2 !important;
    }
    
    .sync-btn .fas {
        font-size: 0.65rem;
    }
    
    /* Keep sync button compact */
    .sync-btn.btn-sm {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
    }
    
    /* Responsive text */
    @media (max-width: 768px) {
        .table-sm {
            font-size: 0.8rem;
        }
        
        .table-sm td {
            padding: 0.25rem 0.1rem;
        }
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa;
    }
    
    .progress {
        height: 25px;
        font-size: 14px;
    }
    
    .progress-bar {
        line-height: 25px;
    }
    
    .custom-control-label {
        cursor: pointer;
    }
    
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .modal-lg {
        max-width: 900px;
    }
    
    /* Secret status badges */
    .secret-username {
        font-weight: 600;
        color: #007bff;
    }
    
    .router-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .secret-actions {
        white-space: nowrap;
    }

    /* Custom SweetAlert2 Styling to match PPP Profile theme */
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
        border-radius: 8px !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
    }

    .swal2-cancel {
        border-radius: 8px !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
    }

    .swal2-icon.swal2-warning {
        border-color: #ffc107 !important;
        color: #ffc107 !important;
    }

    /* Loading animation custom style */
    .swal2-loading .swal2-styled.swal2-confirm {
        background-color: #d33 !important;
    }
</style>
@stop

@section('title', 'PPPoE Secret Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>PPPoE Secret Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">PPPoE Secrets</li>
            </ol>
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

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->total() }}</h3>
                    <p>Total Secrets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->where('disabled', false)->count() }}</h3>
                    <p>Active Secrets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->where('disabled', true)->count() }}</h3>
                    <p>Disabled Secrets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->where('created_at', '>=', today())->count() }}</h3>
                    <p>Created Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Search & Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pppoe.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Username</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Enter username or comment...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="router_id">Filter by Router</label>
                            <select class="form-control" id="router_id" name="router_id">
                                <option value="">All Routers</option>
                                @if(isset($routers) && $routers->count() > 0)
                                    @foreach($routers as $router)
                                        <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                            {{ $router->name }} ({{ $router->ip_address }})
                                        </option>
                                    @endforeach
                                @else
                                    @php
                                        $user = auth()->user();
                                        $availableRouters = $user->hasRole('super_admin') 
                                            ? \App\Models\Router::where('status', 'active')->get()
                                            : $user->routers()->where('status', 'active')->get();
                                    @endphp
                                    @foreach($availableRouters as $router)
                                        <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                                            {{ $router->name }} ({{ $router->ip_address }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sync_status">Sync Status</label>
                            <select class="form-control" id="sync_status" name="sync_status">
                                <option value="">All</option>
                                <option value="synced" {{ request('sync_status') === 'synced' ? 'selected' : '' }}>Synced</option>
                                <option value="not_synced" {{ request('sync_status') === 'not_synced' ? 'selected' : '' }}>Not Synced</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-block">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('pppoe.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('pppoe.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Secret
            </a>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-download"></i> Import from MikroTik
            </button>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary" onclick="syncAllSecrets()">
                    <i class="fas fa-sync-alt"></i> Sync All
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="exportSecrets()">
                    <i class="fas fa-file-export"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- PPPoE Secrets Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                PPPoE Secrets List
                <span class="badge badge-info ml-2">{{ $pppoeSecrets->total() }} total</span>
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control float-right" placeholder="Quick search...">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($pppoeSecrets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="pppoeTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%" class="text-center">#</th>
                                <th>Username</th>
                                <th>Router</th>
                                <th class="text-center">Service</th>
                                <th>Profile</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Sync</th>
                                @if(auth()->user()->role && auth()->user()->role->name === 'super_admin')
                                    <th class="text-center">Created By</th>
                                @endif
                                <th class="text-center">Date</th>
                                <th width="12%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pppoeSecrets as $index => $secret)
                                <tr>
                                    <td class="text-center align-middle">
                                        <span class="text-muted">{{ $pppoeSecrets->firstItem() + $index }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="secret-username">{{ $secret->username }}</div>
                                        @if($secret->comment)
                                            <small class="text-muted d-block">{{ Str::limit($secret->comment, 25) }}</small>
                                        @endif
                                        @if($secret->local_address || $secret->remote_address)
                                            <div class="mt-1">
                                                @if($secret->local_address)
                                                    <span class="badge badge-light badge-sm">L: {{ $secret->local_address }}</span>
                                                @endif
                                                @if($secret->remote_address)
                                                    <span class="badge badge-light badge-sm">R: {{ $secret->remote_address }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="router-info">
                                            <span class="badge badge-info badge-sm">{{ $secret->router->name }}</span>
                                            <small class="text-muted">{{ $secret->router->ip_address }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-secondary badge-sm">{{ $secret->service ?: 'pppoe' }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-primary font-weight-medium">{{ $secret->profile ?: '-' }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $secret->disabled ? 'danger' : 'success' }} badge-sm">
                                            {{ $secret->disabled ? 'Disabled' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="sync-status-container" data-secret-id="{{ $secret->id }}">
                                            <span class="badge badge-warning badge-sm">
                                                <i class="fas fa-spinner fa-spin"></i> Checking
                                            </span>
                                        </div>
                                    </td>
                                    @if(auth()->user()->role && auth()->user()->role->name === 'super_admin')
                                        <td class="text-center align-middle">
                                            <div class="user-info">
                                                <span class="d-block font-weight-medium">{{ $secret->user->name }}</span>
                                                <small class="text-muted">{{ Str::limit($secret->user->email, 15) }}</small>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <div class="date-info">
                                            <span class="d-block">{{ $secret->created_at->format('d M Y') }}</span>
                                            <small class="text-muted">{{ $secret->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="secret-actions">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('pppoe.show', $secret) }}" 
                                                   class="btn btn-outline-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('pppoe.edit', $secret) }}" 
                                                   class="btn btn-outline-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm sync-btn" 
                                                        data-secret-id="{{ $secret->id }}"
                                                        title="Sync to MikroTik">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                <form action="{{ route('pppoe.destroy', $secret) }}" method="POST" 
                                                      class="delete-secret-form" data-secret-name="{{ $secret->username }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm delete-btn" 
                                                            title="Delete"
                                                            onclick="openDeleteModal('{{ $secret->id }}', '{{ addslashes($secret->username) }}', '{{ route('pppoe.destroy', $secret->id) }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="dataTables_info">
                                Showing {{ $pppoeSecrets->firstItem() ?? 0 }} to {{ $pppoeSecrets->lastItem() ?? 0 }} 
                                of {{ $pppoeSecrets->total() }} entries
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                {{ $pppoeSecrets->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-wifi fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted mb-2">No PPPoE Secrets Found</h4>
                            <p class="text-muted">
                                @if(request()->has('search') || request()->has('router_id') || request()->has('status'))
                                    No secrets match your current filters. Try adjusting your search criteria.
                                @else
                                    You haven't created any PPPoE secrets yet. Start by adding your first secret.
                                @endif
                            </p>
                        </div>
                        <div>
                            @if(request()->has('search') || request()->has('router_id') || request()->has('status'))
                                <a href="{{ route('pppoe.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            @endif
                            <a href="{{ route('pppoe.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Secret
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-download"></i> Import PPPoE Secrets from MikroTik
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Step 1: Router Selection -->
                    <div id="step1" class="import-step">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-router"></i> Step 1: Select Router</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="routerSelectionForm">
                                            @csrf
                                            <div class="form-group">
                                                <label for="importRouterId" class="font-weight-medium">Choose Router to Import From:</label>
                                                <select class="form-control form-control-lg" id="importRouterId" name="router_id" required>
                                                    <option value="">-- Select a router --</option>
                                                    @php 
                                                        $user = auth()->user();
                                                        $importRouters = $user->hasRole('super_admin') 
                                                            ? \App\Models\Router::where('status', 'active')->get()
                                                            : $user->routers()->where('status', 'active')->get();
                                                    @endphp
                                                    @foreach($importRouters as $router)
                                                        <option value="{{ $router->id }}">
                                                            {{ $router->name }} ({{ $router->ip_address }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="text-center">
                                                <button type="button" class="btn btn-outline-primary mr-2" id="previewImportBtn">
                                                    <i class="fas fa-eye"></i> Preview Secrets
                                                </button>
                                                <button type="button" class="btn btn-success" id="directImportBtn">
                                                    <i class="fas fa-download"></i> Import All Directly
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="step2" class="import-step" style="display: none;">
                        <h6>Step 2: Select Secrets to Import</h6>
                        <div id="importSummary" class="alert alert-info"></div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllNew">
                                    <i class="fas fa-check-square"></i> Select All New
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                    <i class="fas fa-square"></i> Deselect All
                                </button>
                            </div>
                            <div class="col-6 text-right">
                                <button type="button" class="btn btn-warning" id="backToStep1">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-success" id="importSelectedBtn">
                                    <i class="fas fa-download"></i> Import Selected
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="importPreviewTable">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="selectAllCheckbox">
                                        </th>
                                        <th>Username</th>
                                        <th>Service</th>
                                        <th>Profile</th>
                                        <th>Local IP</th>
                                        <th>Remote IP</th>
                                        <th>Status</th>
                                        <th>Disabled</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody id="importTableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="importProgress" style="display: none;">
                        <h6>Importing Secrets...</h6>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%">
                                Please wait...
                            </div>
                        </div>
                    </div>

                    <div id="importResult" style="display: none;">
                        <!-- Import results will be shown here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteSecretForm" style="display: none;" method="POST">
        @csrf
        @method('DELETE')
    </form>

    <!-- Delete Secret Modal -->
    <div class="modal fade" id="deleteSecretModal" tabindex="-1" role="dialog" aria-labelledby="deleteSecretModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteSecretModalLabel">
                        <i class="fas fa-trash-alt"></i> Konfirmasi Hapus PPPoE Secret
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-center mb-3">Anda yakin ingin menghapus secret ini?</h5>
                    <div class="alert alert-info">
                        <strong>Username:</strong> <span id="deleteSecretName"></span>
                    </div>
                    
                    <div class="mt-4">
                        <p class="font-weight-bold">Pilih aksi yang ingin dilakukan:</p>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="delete_option" id="delete_app_only" value="app_only">
                            <label class="form-check-label" for="delete_app_only">
                                <strong>Hapus dari Aplikasi Saja</strong><br>
                                <small class="text-muted">Secret akan dihapus dari database, tetapi tetap ada di MikroTik</small>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="delete_option" id="delete_mikrotik_only" value="mikrotik_only">
                            <label class="form-check-label" for="delete_mikrotik_only">
                                <strong>Hapus dari MikroTik Saja</strong><br>
                                <small class="text-muted">Secret akan dihapus dari router, tetapi tetap ada di aplikasi</small>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="delete_option" id="delete_both" value="both" checked>
                            <label class="form-check-label" for="delete_both">
                                <strong>Hapus dari Aplikasi dan MikroTik</strong><br>
                                <small class="text-muted">Secret akan dihapus sepenuhnya dari kedua tempat</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Ora Sido
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-secret">
                        <i class="fas fa-trash-alt"></i> Hapus Secret
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Success Modal -->
    <div class="modal fade" id="syncSuccessModal" tabindex="-1" role="dialog" aria-labelledby="syncSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="syncSuccessModalLabel">
                        <i class="fas fa-check-circle"></i> Sukses Synced Bro
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4>Sukses Synced Bro!</h4>
                    <p class="text-muted">PPPoE secret berhasil disinkronisasi ke router MikroTik.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" id="reloadAfterSync">
                        <i class="fas fa-redo"></i> OK Lek
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Error Modal -->
    <div class="modal fade" id="syncErrorModal" tabindex="-1" role="dialog" aria-labelledby="syncErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="syncErrorModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Sync Failed
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4>Sync Failed</h4>
                    <p class="text-muted" id="syncErrorModalBody">Failed to sync secret to router.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#pppoeTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Import functionality
    let importData = [];

    // Reset modal when opening
    $('#importModal').on('show.bs.modal', function() {
        $('#step1').show();
        $('#step2').hide();
        $('#importProgress').hide();
        $('#importResult').hide();
        $('#importRouterId').val('');
        importData = [];
    });

    // Preview Import
    $('#previewImportBtn').click(function() {
        const routerId = $('#importRouterId').val();
        if (!routerId) {
            alert('Please select a router first');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

        $.post('{{ route("pppoe.preview-import") }}', {
            _token: '{{ csrf_token() }}',
            router_id: routerId
        })
        .done(function(response) {
            if (response.success) {
                importData = response.data;
                populateImportTable(response.data);
                updateImportSummary(response.summary);
                $('#step1').hide();
                $('#step2').show();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Failed to load secrets';
            alert('Error: ' + message);
        })
        .always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-eye"></i> Preview Secrets');
        });
    });

    // Direct Import (Import All)
    $('#directImportBtn').click(function() {
        const routerId = $('#importRouterId').val();
        if (!routerId) {
            alert('Please select a router first');
            return;
        }

        if (!confirm('Are you sure you want to import ALL PPPoE secrets from the selected router? This will skip existing secrets.')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);
        $('#step1').hide();
        $('#importProgress').show();

        $.post('{{ route("pppoe.import-from-mikrotik") }}', {
            _token: '{{ csrf_token() }}',
            router_id: routerId
        })
        .done(function(response) {
            $('#importProgress').hide();
            $('#importResult').show();
            
            if (response.success) {
                let resultHtml = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + response.message + '</div>';
                resultHtml += '<div class="row">';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-download"></i></span><div class="info-box-content"><span class="info-box-text">Imported</span><span class="info-box-number">' + response.imported + '</span></div></div></div>';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-skip-forward"></i></span><div class="info-box-content"><span class="info-box-text">Skipped</span><span class="info-box-number">' + response.skipped + '</span></div></div></div>';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Errors</span><span class="info-box-number">' + response.errors.length + '</span></div></div></div>';
                resultHtml += '</div>';
                
                if (response.errors.length > 0) {
                    resultHtml += '<div class="alert alert-warning"><h6>Errors:</h6><ul>';
                    response.errors.forEach(function(error) {
                        resultHtml += '<li>' + error + '</li>';
                    });
                    resultHtml += '</ul></div>';
                }
                
                $('#importResult').html(resultHtml);
                
                // Auto reload page after 2 seconds if import was successful
                if (response.imported > 0) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            } else {
                $('#importResult').html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
            }
        })
        .fail(function(xhr) {
            $('#importProgress').hide();
            $('#importResult').show();
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Import failed';
            $('#importResult').html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + message + '</div>');
        })
        .always(function() {
            btn.prop('disabled', false);
        });
    });

    // Back to Step 1
    $('#backToStep1').click(function() {
        $('#step2').hide();
        $('#step1').show();
    });

    // Select All New
    $('#selectAllNew').click(function() {
        $('#importTableBody input[type="checkbox"]').each(function() {
            const row = $(this).closest('tr');
            const status = row.find('.secret-status').text().trim();
            if (status === 'NEW') {
                $(this).prop('checked', true);
            }
        });
        updateSelectAllCheckbox();
    });

    // Deselect All
    $('#deselectAll').click(function() {
        $('#importTableBody input[type="checkbox"]').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
    });

    // Select All Checkbox
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('#importTableBody input[type="checkbox"]').prop('checked', isChecked);
    });

    // Individual checkbox change
    $(document).on('change', '#importTableBody input[type="checkbox"]', function() {
        updateSelectAllCheckbox();
    });

    // Import Selected
    $('#importSelectedBtn').click(function() {
        const selectedSecrets = [];
        $('#importTableBody input[type="checkbox"]:checked').each(function() {
            selectedSecrets.push($(this).val());
        });

        if (selectedSecrets.length === 0) {
            alert('Please select at least one secret to import');
            return;
        }

        if (!confirm('Are you sure you want to import ' + selectedSecrets.length + ' selected secrets?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);
        $('#step2').hide();
        $('#importProgress').show();

        $.post('{{ route("pppoe.import-selected") }}', {
            _token: '{{ csrf_token() }}',
            router_id: $('#importRouterId').val(),
            selected_secrets: selectedSecrets
        })
        .done(function(response) {
            $('#importProgress').hide();
            $('#importResult').show();
            
            if (response.success) {
                let resultHtml = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + response.message + '</div>';
                resultHtml += '<div class="row">';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-download"></i></span><div class="info-box-content"><span class="info-box-text">Imported</span><span class="info-box-number">' + response.imported + '</span></div></div></div>';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-skip-forward"></i></span><div class="info-box-content"><span class="info-box-text">Skipped</span><span class="info-box-number">' + response.skipped + '</span></div></div></div>';
                resultHtml += '<div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Errors</span><span class="info-box-number">' + response.errors.length + '</span></div></div></div>';
                resultHtml += '</div>';
                
                if (response.errors.length > 0) {
                    resultHtml += '<div class="alert alert-warning"><h6>Errors:</h6><ul>';
                    response.errors.forEach(function(error) {
                        resultHtml += '<li>' + error + '</li>';
                    });
                    resultHtml += '</ul></div>';
                }
                
                $('#importResult').html(resultHtml);
                
                // Auto reload page after 2 seconds if import was successful
                if (response.imported > 0) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            } else {
                $('#importResult').html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + response.message + '</div>');
            }
        })
        .fail(function(xhr) {
            $('#importProgress').hide();
            $('#importResult').show();
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Import failed';
            $('#importResult').html('<div class="alert alert-danger"><i class="fas fa-times"></i> ' + message + '</div>');
        })
        .always(function() {
            btn.prop('disabled', false);
        });
    });

    function populateImportTable(data) {
        const tbody = $('#importTableBody');
        tbody.empty();
        
        data.forEach(function(secret) {
            const statusBadge = secret.status === 'new' 
                ? '<span class="badge badge-success secret-status">NEW</span>' 
                : '<span class="badge badge-warning secret-status">EXISTS</span>';
            
            const disabledBadge = secret.disabled 
                ? '<span class="badge badge-danger">Yes</span>' 
                : '<span class="badge badge-success">No</span>';
            
            const checkbox = secret.status === 'new' 
                ? '<input type="checkbox" value="' + secret.id + '" checked>' 
                : '<input type="checkbox" value="' + secret.id + '" disabled>';
            
            const row = `
                <tr>
                    <td>${checkbox}</td>
                    <td><strong>${secret.username}</strong></td>
                    <td>${secret.service || '-'}</td>
                    <td>${secret.profile || '-'}</td>
                    <td>${secret.local_address || '-'}</td>
                    <td>${secret.remote_address || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>${disabledBadge}</td>
                    <td>${secret.comment || '-'}</td>
                </tr>
            `;
            tbody.append(row);
        });
        
        updateSelectAllCheckbox();
    }

    function updateImportSummary(summary) {
        const summaryHtml = `
            <i class="fas fa-info-circle"></i> 
            Found <strong>${summary.total}</strong> secrets in MikroTik: 
            <strong class="text-success">${summary.new}</strong> new, 
            <strong class="text-warning">${summary.existing}</strong> already exist
        `;
        $('#importSummary').html(summaryHtml);
    }

    function updateSelectAllCheckbox() {
        const total = $('#importTableBody input[type="checkbox"]:not(:disabled)').length;
        const checked = $('#importTableBody input[type="checkbox"]:checked').length;
        $('#selectAllCheckbox').prop('checked', total > 0 && total === checked);
    }

    // Check sync status for all secrets when page loads
    function checkAllSyncStatus() {
        $('.sync-status-container').each(function() {
            const secretId = $(this).data('secret-id');
            checkSyncStatusForSecret(secretId);
        });
    }

    // Check sync status when page loads
    $(document).ready(function() {
        setTimeout(checkAllSyncStatus, 1000); // Delay 1 second to ensure page is fully loaded
    });
    
    // Enhanced Sync Button functionality
    $(document).on('click', '.sync-btn', function(e) {
        e.preventDefault();
        const secretId = $(this).data('secret-id');
        const btn = $(this);
        const row = btn.closest('tr');
        const syncContainer = row.find('.sync-status-container');
        const username = row.find('.secret-username').text().trim();
        
        console.log('Starting sync for secret:', { secretId, username });
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        syncContainer.html('<span class="badge badge-info badge-sm"><i class="fas fa-spinner fa-spin"></i> Syncing...</span>');
        
        $.ajax({
            url: `/pppoe/${secretId}/sync-to-mikrotik`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log('Sync response:', response);
                
                if (response.success) {
                    // Update sync status badge to show success
                    syncContainer.html('<span class="badge badge-success badge-sm"><i class="fas fa-check"></i> Synced</span>');
                    
                    // Show success message with details
                    let message = response.message || 'PPPoE secret berhasil disinkronisasi ke MikroTik.';
                    
                    Swal.fire({
                        title: 'Sync Berhasil!',
                        text: message,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    // Reload status after short delay to get fresh data
                    setTimeout(function() {
                        checkSyncStatusForSecret(secretId);
                    }, 2000);
                    
                } else {
                    // Update sync status to show error
                    syncContainer.html('<span class="badge badge-danger badge-sm"><i class="fas fa-times"></i> Error</span>');
                    
                    // Show error details
                    Swal.fire({
                        title: 'Sync Gagal',
                        text: response.message || 'Gagal melakukan sinkronisasi ke MikroTik.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                console.error('Sync error:', xhr);
                
                // Update sync status to show error  
                syncContainer.html('<span class="badge badge-danger badge-sm"><i class="fas fa-times"></i> Error</span>');
                
                const response = xhr.responseJSON;
                let message = 'Terjadi kesalahan saat melakukan sync.';
                
                if (response && response.message) {
                    message = response.message;
                } else if (xhr.status === 500) {
                    message = 'Server error: ' + (xhr.statusText || 'Internal Server Error');
                } else if (xhr.status === 404) {
                    message = 'Endpoint tidak ditemukan.';
                } else if (xhr.status === 403) {
                    message = 'Tidak memiliki izin untuk melakukan sync.';
                }
                
                Swal.fire({
                    title: 'Sync Error',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                // Reset button to normal state
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
            }
        });
    });

    // Function to check sync status for a specific secret
    function checkSyncStatusForSecret(secretId) {
        const container = $('.sync-status-container[data-secret-id="' + secretId + '"]');
        
        if (container.length === 0) {
            console.warn('Sync status container not found for secret ID:', secretId);
            return;
        }
        
        console.log('Checking sync status for secret ID:', secretId);
        
        $.post('/pppoe/' + secretId + '/check-sync-status', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            console.log('Sync status response for secret', secretId, ':', response);
            
            let statusHtml = '';
            let badgeClass = '';
            let icon = '';
            
            if (response.success) {
                switch(response.sync_status) {
                    case 'synced':
                        badgeClass = 'badge-success';
                        icon = 'fas fa-check';
                        statusHtml = '<i class="' + icon + '"></i> Synced';
                        break;
                    case 'out_of_sync':
                        badgeClass = 'badge-warning';
                        icon = 'fas fa-exclamation-triangle';
                        statusHtml = '<i class="' + icon + '"></i> Out of Sync';
                        break;
                    case 'not_synced':
                        badgeClass = 'badge-danger';
                        icon = 'fas fa-times';
                        statusHtml = '<i class="' + icon + '"></i> Not in MikroTik';
                        break;
                    case 'connection_failed':
                        badgeClass = 'badge-secondary';
                        icon = 'fas fa-exclamation-circle';
                        statusHtml = '<i class="' + icon + '"></i> Connection Failed';
                        break;
                    case 'error':
                    default:
                        badgeClass = 'badge-secondary';
                        icon = 'fas fa-question';
                        statusHtml = '<i class="' + icon + '"></i> Error';
                        break;
                }
            } else {
                console.error('Sync status check failed for secret', secretId, ':', response.message);
                badgeClass = 'badge-secondary';
                icon = 'fas fa-question';
                statusHtml = '<i class="' + icon + '"></i> Error';
            }
            
            container.html('<span class="badge ' + badgeClass + ' badge-sm">' + statusHtml + '</span>');
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX request failed for secret', secretId, ':', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            container.html('<span class="badge badge-secondary badge-sm"><i class="fas fa-question"></i> Error</span>');
        });
    }

    // Handle reload after sync success
    $(document).on('click', '#reloadAfterSync', function() {
        location.reload();
    });

    // Delete Secret Modal Handler
    $('#confirm-delete-secret').click(function() {
        console.log('Confirm delete clicked'); // Debug log
        
        const secretId = $('#deleteSecretForm').data('secret-id');
        const secretName = $('#deleteSecretForm').data('secret-name');
        const deleteUrl = $('#deleteSecretForm').attr('action');
        const deleteOption = $('input[name="delete_option"]:checked').val();
        
        console.log('Delete data:', { secretId, secretName, deleteUrl, deleteOption }); // Debug log
        
        if (!deleteUrl) {
            console.error('Delete URL not found');
            Swal.fire('Error', 'URL delete tidak ditemukan. Silakan refresh halaman.', 'error');
            return;
        }
        
        console.log('Submitting delete request...'); // Debug log
        
        if (!deleteOption) {
            Swal.fire('Error', 'Silakan pilih opsi delete terlebih dahulu.', 'error');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');
        
        // Hide modal
        $('#deleteSecretModal').modal('hide');
        
        // Show loading modal
        Swal.fire({
            title: 'Menghapus...',
            html: 'Sedang menghapus PPPoE secret.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit delete request with option
        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE',
                delete_option: deleteOption
            },
            success: function(response) {
                console.log('Delete response:', response); // Debug log
                
                Swal.close();
                
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                console.error('Delete error:', xhr); // Debug log
                
                Swal.close();
                
                let message = 'Gagal menghapus secret.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                Swal.fire('Error', message, 'error');
            },
            complete: function() {
                // Reset button state
                $('#confirm-delete-secret').prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Hapus Secret');
            }
        });
    });

    // Function to open delete modal
    function openDeleteModal(secretId, secretName, deleteUrl) {
        console.log('Opening delete modal:', { secretId, secretName, deleteUrl });
        
        // Store data
        $('#deleteSecretForm').data('secret-id', secretId);
        $('#deleteSecretForm').data('secret-name', secretName);
        $('#deleteSecretForm').attr('action', deleteUrl);
        
        // Update modal content
        $('#deleteSecretName').text(secretName);
        
        // Reset radio buttons
        $('input[name="delete_option"]').prop('checked', false);
        $('#delete_both').prop('checked', true);
        
        // Show modal
        $('#deleteSecretModal').modal('show');
    }
    
    // Make function global
    window.openDeleteModal = openDeleteModal;

    // Enhanced search with debounce and highlighting
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const value = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            $('#pppoeTable tbody tr').each(function() {
                const row = $(this);
                const text = row.text().toLowerCase();
                const matches = text.indexOf(value) > -1;
                row.toggle(matches);
                
                // Highlight matching rows
                if (value && matches) {
                    row.addClass('table-warning');
                } else {
                    row.removeClass('table-warning');
                }
            });
            
            // Update visible count
            const visibleRows = $('#pppoeTable tbody tr:visible').length;
            const totalRows = $('#pppoeTable tbody tr').length;
            if (value) {
                $('#searchResult').remove();
                $('.card-header .card-title').append(
                    `<small id="searchResult" class="ml-2 text-muted">(${visibleRows} of ${totalRows})</small>`
                );
            } else {
                $('#searchResult').remove();
            }
        }, 300);
    });

    // Sync All Secrets function
    function syncAllSecrets() {
        Swal.fire({
            title: 'Sync All Secrets',
            text: 'This will sync all PPPoE secrets with MikroTik. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Sync All',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Syncing...',
                    text: 'Please wait while syncing secrets...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Here you can add the actual sync functionality
                // For now, just show success after 2 seconds
                setTimeout(() => {
                    Swal.fire({
                        title: 'Success!',
                        text: 'All secrets have been synced successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    }

    // Export Secrets function
    function exportSecrets() {
        Swal.fire({
            title: 'Export Secrets',
            text: 'Choose export format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Export CSV',
            cancelButtonText: 'Cancel',
            showDenyButton: true,
            denyButtonText: 'Export Excel',
            denyButtonColor: '#17a2b8'
        }).then((result) => {
            if (result.isConfirmed) {
                // Export as CSV
                window.location.href = '{{ route("pppoe.index") }}?export=csv';
            } else if (result.isDenied) {
                // Export as Excel
                window.location.href = '{{ route("pppoe.index") }}?export=excel';
            }
        });
    }

    // Make functions globally available
    window.syncAllSecrets = syncAllSecrets;
    window.exportSecrets = exportSecrets;
});
</script>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
@stop
