@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>User Details</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit User
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- User Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Full Name:</strong><br>
                            {{ $user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Email Address:</strong><br>
                            {{ $user->email }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Role:</strong><br>
                            <span class="badge badge-{{ $user->role->name === 'super_admin' ? 'danger' : 'info' }} badge-lg">
                                {{ $user->role->display_name }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Account Created:</strong><br>
                            {{ $user->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Account Created:</strong><br>
                            {{ $user->created_at->format('d M Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong><br>
                            {{ $user->updated_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Routers -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assigned Routers</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $user->routers->count() }} routers</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($user->routers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Router Name</th>
                                        <th>IP Address</th>
                                        <th>Port</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->routers as $router)
                                        <tr>
                                            <td>{{ $router->name }}</td>
                                            <td>{{ $router->ip_address }}</td>
                                            <td>{{ $router->port }}</td>
                                            <td>
                                                <span class="badge badge-{{ $router->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($router->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('routers.show', $router) }}" 
                                                   class="btn btn-xs btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-router fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No routers assigned to this user</p>
                            @if($user->role->name === 'super_admin')
                                <small class="text-muted">Super Admin has access to all routers</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- PPPoE Secrets -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PPPoE Secrets</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $user->pppoeSecrets->count() }} secrets</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($user->pppoeSecrets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Router</th>
                                        <th>Service</th>
                                        <th>Profile</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->pppoeSecrets as $secret)
                                        <tr>
                                            <td>{{ $secret->username }}</td>
                                            <td>{{ $secret->router->name ?? 'N/A' }}</td>
                                            <td>{{ $secret->service }}</td>
                                            <td>{{ $secret->profile }}</td>
                                            <td>
                                                <span class="badge badge-{{ $secret->disabled ? 'danger' : 'success' }}">
                                                    {{ $secret->disabled ? 'Disabled' : 'Active' }}
                                                </span>
                                            </td>
                                            <td>{{ $secret->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-key fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No PPPoE secrets created by this user</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- User Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-router"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Assigned Routers</span>
                                    <span class="info-box-number">{{ $user->routers->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-key"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">PPPoE Secrets</span>
                                    <span class="info-box-number">{{ $user->pppoeSecrets->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Days Since Created</span>
                                    <span class="info-box-number">{{ $user->created_at->diffInDays() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" 
                              class="delete-user-form" data-user-name="{{ $user->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-block delete-user-btn">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // SweetAlert2 Delete User
    $('.delete-user-btn').click(function(e) {
        e.preventDefault();
        const form = $(this).closest('.delete-user-form');
        const userName = form.data('user-name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus User',
            html: `Apakah Anda yakin ingin menghapus user <strong>"${userName}"</strong>?<br><br>
                   <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait user.</small>`,
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
                    title: 'Menghapus User...',
                    html: 'Sedang menghapus user dari sistem.',
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
@stop

@section('css')
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>
@stop
