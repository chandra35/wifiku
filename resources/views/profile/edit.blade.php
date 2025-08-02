@extends('adminlte::page')

@section('title', 'Pengaturan Profile')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Pengaturan Profile</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan Profile</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Profile Information -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-2"></i>
                    Informasi Profile
                </h3>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" 
                               class="form-control" 
                               value="{{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No Role' }}" 
                               readonly>
                        <small class="form-text text-muted">Role tidak dapat diubah sendiri. Hubungi administrator untuk mengubah role.</small>
                    </div>

                    <div class="form-group">
                        <label>Member Since</label>
                        <input type="text" 
                               class="form-control" 
                               value="{{ $user->created_at->format('d M Y') }}" 
                               readonly>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lock mr-2"></i>
                    Ubah Password
                </h3>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        @error('current_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Password minimal 8 karakter.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key mr-2"></i>
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Account Information -->
<div class="row">
    <div class="col-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Akun
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>User ID:</strong>
                        <p class="text-muted">{{ $user->id }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Terakhir Login:</strong>
                        <p class="text-muted">{{ $user->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Total PPPoE Secrets:</strong>
                        <p class="text-muted">{{ $user->pppoeSecrets->count() }} secrets</p>
                    </div>
                    @if($user->role && $user->role->name !== 'super_admin')
                    <div class="col-md-3">
                        <strong>Assigned Routers:</strong>
                        <p class="text-muted">{{ $user->routers->count() }} routers</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Password strength indicator
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        var strengthText = '';
        var strengthClass = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Lemah';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'Sedang';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'Kuat';
                strengthClass = 'text-success';
                break;
        }
        
        $('#password').next('.form-text').html('Password minimal 8 karakter. Kekuatan: <span class="' + strengthClass + '">' + strengthText + '</span>');
    });
});
</script>
@stop
