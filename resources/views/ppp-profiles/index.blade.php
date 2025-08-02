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
                                @foreach($profiles->pluck('router')->unique('id') as $router)
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
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Profile Name</th>
                        <th>Router</th>
                        <th>Local Address</th>
                        <th>Remote Address</th>
                        <th>Rate Limit</th>
                        <th>Session Timeout</th>
                        <th>Sync Status</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th>Created By</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        <tr>
                            <td>
                                <strong>{{ $profile->name }}</strong>
                                @if($profile->only_one)
                                    <br><small class="badge badge-warning">Only One</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('routers.show', $profile->router->id) }}" class="text-decoration-none">
                                    {{ $profile->router->name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $profile->router->ip_address }}</small>
                            </td>
                            <td>
                                @if($profile->local_address)
                                    <code>{{ $profile->local_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($profile->remote_address)
                                    <code>{{ $profile->remote_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($profile->rate_limit)
                                    <span class="badge badge-info">{{ $profile->rate_limit }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($profile->session_timeout)
                                    {{ gmdate('H:i:s', $profile->session_timeout) }}
                                @else
                                    <span class="text-muted">Unlimited</span>
                                @endif
                            </td>
                            <td>
                                @if($profile->mikrotik_id)
                                    <span class="badge badge-success">
                                        <i class="fas fa-link"></i> Synced
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-unlink"></i> Not Synced
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->isSuperAdmin())
                                <td>
                                    @if($profile->createdBy)
                                        {{ $profile->createdBy->name }}
                                        <br>
                                        <small class="text-muted">{{ $profile->created_at->format('d M Y') }}</small>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('ppp-profiles.show', $profile->id) }}" 
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('ppp-profiles.edit', $profile->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('ppp-profiles.destroy', $profile->id) }}" 
                                          method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this PPP profile? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @if(!$profile->mikrotik_id)
                                    <br>
                                    <button class="btn btn-xs btn-success mt-1 sync-btn" 
                                            data-profile-id="{{ $profile->id }}" title="Sync to MikroTik">
                                        <i class="fas fa-sync"></i> Sync
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isSuperAdmin() ? '9' : '8' }}" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> No PPP profiles found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                    
                    // Show success message
                    $('<div class="alert alert-success alert-dismissible">' +
                      '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                      '<i class="fas fa-check"></i> ' + response.message +
                      '</div>').prependTo('.content').delay(3000).fadeOut();
                } else {
                    btn.prop('disabled', false).html(originalText);
                    
                    // Show error message
                    $('<div class="alert alert-danger alert-dismissible">' +
                      '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                      '<i class="fas fa-times"></i> ' + response.message +
                      '</div>').prependTo('.content').delay(3000).fadeOut();
                }
            })
            .fail(function(xhr) {
                btn.prop('disabled', false).html(originalText);
                const response = xhr.responseJSON;
                const message = response && response.message ? response.message : 'Sync failed';
                
                $('<div class="alert alert-danger alert-dismissible">' +
                  '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                  '<i class="fas fa-times"></i> ' + message +
                  '</div>').prependTo('.content').delay(3000).fadeOut();
            });
        });
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
</script>
@stop
