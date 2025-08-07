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
</style>
@stop

@section('title', 'PPP Profiles')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>PPP Profiles Management</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('ppp-profiles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Profile
                </a>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-download"></i> Import from MikroTik
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $profiles->total() }}</h3>
                    <p>Total Profiles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $profiles->where('mikrotik_id', '!=', null)->count() }}</h3>
                    <p>Synced Profiles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-link"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $profiles->where('mikrotik_id', null)->count() }}</h3>
                    <p>Not Synced</p>
                </div>
                <div class="icon">
                    <i class="fas fa-unlink"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $profiles->where('created_at', '>=', today())->count() }}</h3>
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
            <form method="GET" action="{{ route('ppp-profiles.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Search Profile Name</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Enter profile name...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="router">Filter by Router</label>
                            <select class="form-control" id="router" name="router">
                                <option value="">All Routers</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ request('router') == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sync_status">Sync Status</label>
                            <select class="form-control" id="sync_status" name="sync_status">
                                <option value="">All Status</option>
                                <option value="synced" {{ request('sync_status') == 'synced' ? 'selected' : '' }}>Synced</option>
                                <option value="not_synced" {{ request('sync_status') == 'not_synced' ? 'selected' : '' }}>Not Synced</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('ppp-profiles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Profiles Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PPP Profiles List</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 15%;">Profile</th>
                            <th style="width: 12%;">Router</th>
                            <th style="width: 12%;" class="d-none d-md-table-cell">Local IP</th>
                            <th style="width: 12%;" class="d-none d-lg-table-cell">Remote IP</th>
                            <th style="width: 10%;" class="d-none d-lg-table-cell">Rate Limit</th>
                            <th style="width: 8%;" class="text-center">Status</th>
                            @if(auth()->user()->role && auth()->user()->role->name === 'super_admin')
                                <th style="width: 10%;" class="d-none d-xl-table-cell">Created By</th>
                            @endif
                            <th style="width: 15%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($profiles as $profile)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $profile->name }}</strong>
                                    <div class="small text-muted">
                                        @if($profile->only_one)
                                            <span class="badge badge-warning badge-sm">Only One</span>
                                        @endif
                                        @if($profile->session_timeout)
                                            <span class="badge badge-secondary badge-sm">{{ gmdate('H:i:s', $profile->session_timeout) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong>{{ $profile->router->name }}</strong>
                                    <small class="text-muted">{{ $profile->router->ip_address }}</small>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($profile->local_address)
                                    <code class="small">{{ $profile->local_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if($profile->remote_address)
                                    <code class="small">{{ $profile->remote_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if($profile->rate_limit)
                                    <span class="badge badge-info badge-sm">{{ $profile->rate_limit }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($profile->mikrotik_id)
                                    <span class="badge badge-success badge-sm">
                                        <i class="fas fa-link"></i> Synced
                                    </span>
                                @else
                                    <span class="badge badge-warning badge-sm">
                                        <i class="fas fa-unlink"></i> Not Synced
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->role && auth()->user()->role->name === 'super_admin')
                                <td class="d-none d-xl-table-cell">
                                    @if($profile->createdBy)
                                        <div class="d-flex flex-column">
                                            <span class="small">{{ $profile->createdBy->name }}</span>
                                            <small class="text-muted">{{ $profile->created_at->format('d M Y') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted small">Unknown</span>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('ppp-profiles.show', $profile->id) }}" 
                                       class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('ppp-profiles.edit', $profile->id) }}" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('ppp-profiles.destroy', $profile->id) }}" 
                                          method="POST" style="display: inline;" 
                                          class="delete-profile-form" data-profile-name="{{ $profile->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm delete-profile-btn" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @if(!$profile->mikrotik_id)
                                    <button class="btn btn-success btn-sm mt-1 sync-btn" 
                                            data-profile-id="{{ $profile->id }}" title="Sync to MikroTik">
                                        <i class="fas fa-sync"></i> Sync
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (auth()->user()->role && auth()->user()->role->name === 'super_admin') ? '8' : '7' }}" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle"></i> No PPP profiles found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
        @if($profiles->hasPages())
            <div class="card-footer">
                {{ $profiles->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Profiles from MikroTik</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Router Selection -->
                    <div id="step1" class="import-step">
                        <h6><i class="fas fa-network-wired"></i> Step 1: Select Router</h6>
                        <form id="routerSelectionForm">
                            @csrf
                            <div class="form-group">
                                <label for="import_router_id">Select Router</label>
                                <select class="form-control" id="import_router_id" name="router_id" required>
                                    <option value="">Choose Router...</option>
                                    @foreach($routers as $router)
                                        <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Import Information:</h6>
                                <ul class="mb-0 pl-3">
                                    <li>This will fetch all PPP profiles from the selected MikroTik router</li>
                                    <li>You can preview and select which profiles to import</li>
                                    <li>Existing profiles with the same name will be marked</li>
                                </ul>
                            </div>
                        </form>
                    </div>

                    <!-- Step 2: Loading -->
                    <div id="step2" class="import-step" style="display: none;">
                        <h6><i class="fas fa-spinner fa-spin"></i> Step 2: Fetching Profiles</h6>
                        <div class="text-center">
                            <div class="progress mb-3">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%" id="fetchProgress">
                                </div>
                            </div>
                            <p class="text-muted">Connecting to MikroTik router and fetching profiles...</p>
                        </div>
                    </div>

                    <!-- Step 3: Preview -->
                    <div id="step3" class="import-step" style="display: none;">
                        <h6><i class="fas fa-eye"></i> Step 3: Preview & Select Profiles</h6>
                        <div id="importSummary" class="mb-3"></div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="selectAllProfiles">
                                <label class="custom-control-label" for="selectAllProfiles">
                                    <strong>Select All New Profiles</strong>
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th width="40px">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="selectAllCheckbox">
                                                <label class="custom-control-label" for="selectAllCheckbox"></label>
                                            </div>
                                        </th>
                                        <th>Profile Name</th>
                                        <th>Rate Limit</th>
                                        <th>Local Address</th>
                                        <th>Remote Address</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Preview data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Step 4: Import Progress -->
                    <div id="step4" class="import-step" style="display: none;">
                        <h6><i class="fas fa-download"></i> Step 4: Importing Profiles</h6>
                        <div class="text-center">
                            <div class="progress mb-3">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                     role="progressbar" style="width: 0%" id="importProgress">
                                </div>
                            </div>
                            <p class="text-muted" id="importStatusText">Importing selected profiles...</p>
                        </div>
                    </div>

                    <!-- Step 5: Results -->
                    <div id="step5" class="import-step" style="display: none;">
                        <h6><i class="fas fa-check-circle text-success"></i> Step 5: Import Completed</h6>
                        <div id="importResults"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelBtn" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">
                        <i class="fas fa-arrow-right"></i> Next: Fetch Profiles
                    </button>
                    <button type="button" class="btn btn-success" id="importBtn" style="display: none;">
                        <i class="fas fa-download"></i> Import Selected
                    </button>
                    <button type="button" class="btn btn-primary" id="finishBtn" style="display: none;" data-dismiss="modal">
                        <i class="fas fa-check"></i> Finish
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    let currentStep = 1;
    let previewData = [];
    let selectedProfiles = [];

    $(document).ready(function() {
        // Handle reload after sync success (using event delegation)
        $(document).on('click', '#reloadAfterSync', function() {
            location.reload();
        });

        // Also reload when success modal is hidden
        $('#syncSuccessModal').on('hidden.bs.modal', function () {
            location.reload();
        });

        // Import Button Click - Start Import Process
        $('#importProfilesBtn').click(function() {
            resetImportModal();
            $('#importModal').modal('show');
        });

        // Next Button - Move to next step
        $('#nextBtn').click(function() {
            if (currentStep === 1) {
                const routerId = $('#import_router_id').val();
                if (!routerId) {
                    Swal.fire('Error', 'Please select a router first', 'error');
                    return;
                }
                fetchProfiles(routerId);
            }
        });

        // Import Button - Import selected profiles
        $('#importBtn').click(function() {
            if (selectedProfiles.length === 0) {
                Swal.fire('Warning', 'Please select at least one profile to import', 'warning');
                return;
            }
            importSelectedProfiles();
        });

        // Select All Checkbox
        $('#selectAllCheckbox').change(function() {
            const isChecked = $(this).is(':checked');
            $('.profile-checkbox:not([data-exists="true"])').prop('checked', isChecked);
            updateSelectedProfiles();
        });

        // Select All New Profiles
        $('#selectAllProfiles').change(function() {
            const isChecked = $(this).is(':checked');
            $('.profile-checkbox[data-exists="false"]').prop('checked', isChecked);
            updateSelectedProfiles();
        });

        // Profile checkbox change
        $(document).on('change', '.profile-checkbox', function() {
            updateSelectedProfiles();
        });

        // Reset modal when closed
        $('#importModal').on('hidden.bs.modal', function() {
            resetImportModal();
        });

        // Sync to MikroTik
        $('.sync-btn').click(function() {
            const btn = $(this);
            const profileId = btn.data('profile-id');
            const originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
            
            $.post(`/ppp-profiles/${profileId}/sync-to-mikrotik`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    btn.removeClass('btn-success').addClass('btn-info')
                       .html('<i class="fas fa-check"></i> Synced')
                       .prop('disabled', true);
                    
                    // Update sync status badge in the table row
                    const row = btn.closest('tr');
                    const statusBadge = row.find('.badge');
                    if (response.profile && response.profile.mikrotik_id) {
                        statusBadge.removeClass('badge-warning').addClass('badge-success')
                            .html('<i class="fas fa-link"></i> Synced');
                    }
                    
                    // Show success modal
                    $('#syncSuccessModal').modal('show');
                } else {
                    btn.prop('disabled', false).html(originalText);
                    
                    // Show error modal
                    $('#syncErrorModalBody').text(response.message || 'Failed to sync profile to router');
                    $('#syncErrorModal').modal('show');
                }
            })
            .fail(function(xhr) {
                btn.prop('disabled', false).html(originalText);
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Sync failed';
                
                $('#syncErrorModalBody').text(message);
                $('#syncErrorModal').modal('show');
            });
        });
    });

    // Handle reload after sync success
    $('#reloadAfterSync').click(function() {
        location.reload();
    });

    // Also reload when success modal is hidden
    $('#syncSuccessModal').on('hidden.bs.modal', function () {
        location.reload();
    });

    function resetImportModal() {
        currentStep = 1;
        previewData = [];
        selectedProfiles = [];
        
        // Reset steps visibility
        $('.import-step').hide();
        $('#step1').show();
        
        // Reset buttons
        $('#nextBtn').show().html('<i class="fas fa-arrow-right"></i> Next: Fetch Profiles');
        $('#importBtn').hide();
        $('#finishBtn').hide();
        
        // Reset form
        $('#import_router_id').val('');
        $('#previewTableBody').empty();
        $('.progress-bar').css('width', '0%');
    }

    function showStep(step) {
        $('.import-step').hide();
        $(`#step${step}`).show();
        currentStep = step;
    }

    function fetchProfiles(routerId) {
        showStep(2);
        $('#nextBtn').hide();
        
        // Animate progress bar
        animateProgress('#fetchProgress', 100, 2000);
        
        $.ajax({
            url: '{{ route("ppp-profiles.preview-import") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                router_id: routerId
            },
            success: function(response) {
                if (response.success) {
                    previewData = response.data;
                    showPreview();
                } else {
                    Swal.fire('Error', response.message || 'Failed to fetch profiles', 'error');
                    resetImportModal();
                }
            },
            error: function(xhr) {
                let message = 'Failed to fetch profiles from MikroTik';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire('Error', message, 'error');
                resetImportModal();
            }
        });
    }

    function showPreview() {
        showStep(3);
        
        const newProfiles = previewData.filter(p => !p.exists);
        const existingProfiles = previewData.filter(p => p.exists);
        
        // Show summary
        const summaryHtml = `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">New Profiles</span>
                            <span class="info-box-number">${newProfiles.length}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Already Exists</span>
                            <span class="info-box-number">${existingProfiles.length}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#importSummary').html(summaryHtml);
        
        // Populate preview table
        let tableHtml = '';
        previewData.forEach(profile => {
            const statusBadge = profile.exists 
                ? '<span class="badge badge-warning">Exists</span>'
                : '<span class="badge badge-success">New</span>';
            
            const checkboxDisabled = profile.exists ? 'disabled' : '';
            const rowClass = profile.exists ? 'table-warning' : '';
            
            // Generate unique ID for checkbox
            const checkboxId = `profile_${profile.name.replace(/[^a-zA-Z0-9]/g, '_')}`;
            
            tableHtml += `
                <tr class="${rowClass}">
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input profile-checkbox" 
                                   id="${checkboxId}" 
                                   data-profile="${profile.name}"
                                   data-exists="${profile.exists}"
                                   ${checkboxDisabled}>
                            <label class="custom-control-label" for="${checkboxId}"></label>
                        </div>
                    </td>
                    <td>${profile.name}</td>
                    <td>${profile.rate_limit || '-'}</td>
                    <td>${profile.local_address || '-'}</td>
                    <td>${profile.remote_address || '-'}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
        $('#previewTableBody').html(tableHtml);
        
        // Show import button if there are new profiles
        if (newProfiles.length > 0) {
            $('#importBtn').show();
        }
        
        updateSelectedProfiles();
    }

    function updateSelectedProfiles() {
        selectedProfiles = [];
        $('.profile-checkbox:checked:not(:disabled)').each(function() {
            const profileName = $(this).data('profile');
            // Find the full profile data from previewData
            const profileData = previewData.find(p => p.name === profileName);
            if (profileData) {
                selectedProfiles.push(profileData);
            }
        });
        
        const selectedCount = selectedProfiles.length;
        if (selectedCount > 0) {
            $('#importBtn').html(`<i class="fas fa-download"></i> Import Selected (${selectedCount})`);
        } else {
            $('#importBtn').html('<i class="fas fa-download"></i> Import Selected');
        }
    }

    function importSelectedProfiles() {
        showStep(4);
        $('#importBtn').hide();
        
        const routerId = $('#import_router_id').val();
        
        // Animate progress bar
        animateProgress('#importProgress', 100, 3000);
        
        $.ajax({
            url: '{{ route("ppp-profiles.import-selected") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                router_id: routerId,
                profiles: selectedProfiles
            },
            success: function(response) {
                showImportResults(response);
            },
            error: function(xhr) {
                let message = 'Failed to import profiles';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire('Error', message, 'error');
                resetImportModal();
            }
        });
    }

    function showImportResults(response) {
        showStep(5);
        
        let resultsHtml = '';
        
        if (response.success) {
            const imported = response.imported || 0;
            const skipped = response.skipped || 0;
            const errors = response.errors || [];
            
            resultsHtml += `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> Import Completed Successfully!</h6>
                    <ul class="mb-0">
                        <li><strong>${imported}</strong> profiles imported successfully</li>
                        ${skipped > 0 ? `<li><strong>${skipped}</strong> profiles skipped (already exist)</li>` : ''}
                    </ul>
                </div>
            `;
            
            if (errors.length > 0) {
                resultsHtml += `
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Some Issues Occurred:</h6>
                        <ul class="mb-0">
                            ${errors.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
        } else {
            resultsHtml += `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-circle"></i> Import Failed</h6>
                    <p class="mb-0">${response.message || 'Unknown error occurred'}</p>
                </div>
            `;
        }
        
        $('#importResults').html(resultsHtml);
        $('#finishBtn').show();
        
        // Reload page after successful import
        if (response.success && response.imported > 0) {
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    }

    function animateProgress(selector, targetWidth, duration) {
        $(selector).animate({
            width: targetWidth + '%'
        }, duration);
    }

    // SweetAlert2 Delete Profile
    $('.delete-profile-btn').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('.delete-profile-form');
        const profileName = form.data('profile-name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus PPP Profile',
            html: `Apakah Anda yakin ingin menghapus PPP profile <strong>"${profileName}"</strong>?<br><br>
                   <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Profile ini juga akan dihapus dari MikroTik router.</small>`,
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
                
                // Submit via AJAX instead of form submit
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'PPP Profile berhasil dihapus.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            // Reload page after success
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Gagal menghapus PPP Profile.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
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
                <p class="text-muted">PPP profile berhasil disinkronisasi ke router MikroTik.</p>
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
                <p class="text-muted" id="syncErrorModalBody">Failed to sync profile to router.</p>
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
