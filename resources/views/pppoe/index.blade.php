@extends('adminlte::page')

@section('title', 'PPPoE Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>PPPoE Secret Management</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-download"></i> Import from MikroTik
                </button>
                <a href="{{ route('pppoe.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New PPPoE Secret
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PPPoE Secrets List</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control float-right" placeholder="Search secrets...">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($pppoeSecrets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="pppoeTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Username</th>
                                <th>Router</th>
                                <th>Service</th>
                                <th>Profile</th>
                                <th>Local IP</th>
                                <th>Remote IP</th>
                                <th>Status</th>
                                <th>Sync Status</th>
                                @if(auth()->user()->isSuperAdmin())
                                    <th>Created By</th>
                                @endif
                                <th>Created At</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pppoeSecrets as $index => $secret)
                                <tr>
                                    <td>{{ $pppoeSecrets->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $secret->username }}</strong>
                                        @if($secret->comment)
                                            <br><small class="text-muted">{{ Str::limit($secret->comment, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $secret->router->name }}</span>
                                        <br><small class="text-muted">{{ $secret->router->ip_address }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $secret->service ?: 'pppoe' }}</span>
                                    </td>
                                    <td>
                                        {{ $secret->profile ?: '-' }}
                                    </td>
                                    <td>
                                        {{ $secret->local_address ?: '-' }}
                                    </td>
                                    <td>
                                        {{ $secret->remote_address ?: '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $secret->disabled ? 'danger' : 'success' }}">
                                            {{ $secret->disabled ? 'Disabled' : 'Active' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="sync-status-container" data-secret-id="{{ $secret->id }}">
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-spinner fa-spin"></i> Checking...
                                            </span>
                                        </div>
                                    </td>
                                    @if(auth()->user()->isSuperAdmin())
                                        <td>
                                            {{ $secret->user->name }}
                                            <br><small class="text-muted">{{ $secret->user->email }}</small>
                                        </td>
                                    @endif
                                    <td>
                                        {{ $secret->created_at->format('d M Y') }}
                                        <br><small class="text-muted">{{ $secret->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('pppoe.show', $secret) }}" 
                                               class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pppoe.edit', $secret) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('pppoe.destroy', $secret) }}" 
                                                  method="POST" style="display: inline;"
                                                  class="delete-form"
                                                  data-secret-name="{{ $secret->username }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger delete-btn" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $pppoeSecrets->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-wifi fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No PPPoE secrets found</h5>
                    <p class="text-muted">Start by creating your first PPPoE secret.</p>
                    <a href="{{ route('pppoe.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New PPPoE Secret
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->total() }}</h3>
                    <p>Total PPPoE Secrets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wifi"></i>
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
                    <i class="fas fa-check"></i>
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
                    <i class="fas fa-pause"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $pppoeSecrets->where('created_at', '>=', now()->subDay())->count() }}</h3>
                    <p>Created Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-download"></i> Import PPPoE Secrets from MikroTik
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="step1" class="import-step">
                        <h6>Step 1: Select Router</h6>
                        <form id="routerSelectionForm">
                            @csrf
                            <div class="form-group">
                                <label for="importRouterId">Select Router:</label>
                                <select class="form-control" id="importRouterId" name="router_id" required>
                                    <option value="">Choose a router...</option>
                                    @php $user = auth()->user(); @endphp
                                    @if($user->hasRole('super_admin'))
                                        @foreach(\App\Models\Router::where('status', 'active')->get() as $router)
                                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                                        @endforeach
                                    @else
                                        @foreach($user->routers()->where('status', 'active')->get() as $router)
                                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" id="previewImportBtn">
                                <i class="fas fa-eye"></i> Preview Secrets
                            </button>
                            <button type="button" class="btn btn-success" id="directImportBtn">
                                <i class="fas fa-download"></i> Import All Directly
                            </button>
                        </form>
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
            const container = $(this);
            const secretId = container.data('secret-id');
            
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
                
                container.html('<span class="badge ' + badgeClass + '">' + statusHtml + '</span>');
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX request failed for secret', secretId, ':', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                container.html('<span class="badge badge-secondary"><i class="fas fa-question"></i> Error</span>');
            });
        });
    }

    // Check sync status when page loads
    $(document).ready(function() {
        setTimeout(checkAllSyncStatus, 1000); // Delay 1 second to ensure page is fully loaded
    });
    
    // SweetAlert2 for delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        
        const form = $(this).closest('.delete-form');
        const secretName = form.data('secret-name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus PPPoE secret <strong>"${secretName}"</strong>?<br><br>
                   <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Secret ini juga akan dihapus dari MikroTik router.</small>`,
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
                    title: 'Menghapus...',
                    html: 'Sedang menghapus PPPoE secret dari database dan MikroTik.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                form.submit();
            }
        });
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
    padding: 2em !important;
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
