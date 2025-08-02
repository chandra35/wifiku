@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>User Management</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
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
            <h3 class="card-title">User List</h3>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Assigned Routers</th>
                                <th>PPPoE Secrets</th>
                                <th>Created At</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role->name === 'super_admin' ? 'danger' : 'info' }}">
                                            {{ $user->role->display_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->routers->count() > 0)
                                            @foreach($user->routers as $router)
                                                <span class="badge badge-secondary">{{ $router->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No routers assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $user->pppoeSecrets->count() ?? 0 }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('users.show', $user) }}" 
                                               class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('users.destroy', $user) }}" 
                                                  method="POST" style="display: inline;"
                                                  class="delete-form"
                                                  data-user-name="{{ $user->name }}"
                                                  data-user-email="{{ $user->email }}">
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
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No users found</h5>
                    <p class="text-muted">Start by adding your first user.</p>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // SweetAlert2 for delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        
        const form = $(this).closest('.delete-form');
        const userName = form.data('user-name');
        const userEmail = form.data('user-email');
        
        Swal.fire({
            title: 'Konfirmasi Hapus User',
            html: `Apakah Anda yakin ingin menghapus user:<br>
                   <strong>"${userName}"</strong><br>
                   <small class="text-muted">${userEmail}</small><br><br>
                   <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan! Semua data terkait user akan ikut terhapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus User!',
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
                    html: 'Sedang menghapus user dan semua data terkait.',
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
</style>
@stop
