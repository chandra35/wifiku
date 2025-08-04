@extends('adminlte::page')

@section('title', 'Pengaturan Profile')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-user-cog mr-2"></i>
                Pengaturan Profile
            </h1>
            <p class="text-muted mb-0">Kelola informasi pribadi dan perusahaan Anda</p>
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

<!-- Status Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No Role' }}</h3>
                <p>Role Akun</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tag"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $user->pppoeSecrets->count() }}</h3>
                <p>PPPoE Secrets</p>
            </div>
            <div class="icon">
                <i class="fas fa-network-wired"></i>
            </div>
        </div>
    </div>
    
    @if($user->role && $user->role->name !== 'super_admin')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $user->routers->count() }}</h3>
                <p>Assigned Routers</p>
            </div>
            <div class="icon">
                <i class="fas fa-router"></i>
            </div>
        </div>
    </div>
    @endif
    
    <div class="col-lg-3 col-6">
        <div class="small-box {{ $user->hasCompleteCompanyInfo() ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h3>{{ $user->hasCompleteCompanyInfo() ? '100%' : number_format(($user->company_name ? 20 : 0) + ($user->company_address ? 20 : 0) + ($user->company_phone ? 20 : 0) + ($user->pic_photo ? 20 : 0) + ($user->isp_logo ? 20 : 0), 0) }}%</h3>
                <p>Data Lengkap</p>
            </div>
            <div class="icon">
                <i class="fas fa-{{ $user->hasCompleteCompanyInfo() ? 'check-circle' : 'exclamation-triangle' }}"></i>
            </div>
        </div>
    </div>
</div>
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
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
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
                        <label for="company_name">Nama Perusahaan / ISP</label>
                        <input type="text" 
                               class="form-control @error('company_name') is-invalid @enderror" 
                               id="company_name" 
                               name="company_name" 
                               value="{{ old('company_name', $user->company_name) }}" 
                               placeholder="Contoh: PT. Internet Indonesia">
                        @error('company_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_address">Alamat Perusahaan</label>
                        <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                  id="company_address" 
                                  name="company_address" 
                                  rows="3"
                                  placeholder="Alamat lengkap perusahaan untuk invoice">{{ old('company_address', $user->company_address) }}</textarea>
                        @error('company_address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_phone">Telepon Perusahaan</label>
                        <input type="text" 
                               class="form-control @error('company_phone') is-invalid @enderror" 
                               id="company_phone" 
                               name="company_phone" 
                               value="{{ old('company_phone', $user->company_phone) }}" 
                               placeholder="Contoh: +62 21 1234567">
                        @error('company_phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
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

<!-- Profile Images -->
<div class="row">
    <!-- PIC Photo -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-circle mr-2"></i>
                    Foto PIC (Person In Charge)
                </h3>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->pic_photo)
                        <img src="{{ asset('storage/' . $user->pic_photo) }}" 
                             alt="Foto PIC" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;"
                             id="pic-preview">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                             style="width: 200px; height: 200px; margin: 0 auto;" 
                             id="pic-placeholder">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <input type="file" 
                               class="form-control-file @error('pic_photo') is-invalid @enderror" 
                               id="pic_photo" 
                               name="pic_photo" 
                               accept="image/jpeg,image/png,image/jpg"
                               onchange="previewPicPhoto(this)"
                               style="display: none;">
                        <label for="pic_photo" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i>
                            Pilih Foto
                        </label>
                        @error('pic_photo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-sm" id="upload-pic-btn" style="display: none;">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Foto
                    </button>
                </form>
                
                @if($user->pic_photo)
                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="deletePicPhoto()">
                        <i class="fas fa-trash mr-1"></i>
                        Hapus Foto
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- ISP Logo -->
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building mr-2"></i>
                    Logo ISP / Perusahaan
                </h3>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->isp_logo)
                        <img src="{{ asset('storage/' . $user->isp_logo) }}" 
                             alt="Logo ISP" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;"
                             id="logo-preview">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                             style="width: 200px; height: 200px; margin: 0 auto;" 
                             id="logo-placeholder">
                            <i class="fas fa-building fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <input type="file" 
                               class="form-control-file @error('isp_logo') is-invalid @enderror" 
                               id="isp_logo" 
                               name="isp_logo" 
                               accept="image/jpeg,image/png,image/jpg"
                               onchange="previewIspLogo(this)"
                               style="display: none;">
                        <label for="isp_logo" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i>
                            Pilih Logo
                        </label>
                        @error('isp_logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-info btn-sm" id="upload-logo-btn" style="display: none;">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Logo
                    </button>
                </form>
                
                @if($user->isp_logo)
                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="deleteIspLogo()">
                        <i class="fas fa-trash mr-1"></i>
                        Hapus Logo
                    </button>
                @endif
            </div>
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
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card card-outline {{ $user->hasCompleteCompanyInfo() ? 'card-success' : 'card-warning' }}">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-{{ $user->hasCompleteCompanyInfo() ? 'check-circle' : 'exclamation-triangle' }} mr-2"></i>
                                    Status Kelengkapan Data untuk Invoice Generation
                                </h4>
                            </div>
                            <div class="card-body">
                                @if($user->hasCompleteCompanyInfo())
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Data Lengkap!</strong> Semua informasi perusahaan dan foto sudah tersedia untuk generate invoice.
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-3">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <strong>Data Belum Lengkap!</strong> Lengkapi informasi berikut untuk dapat generate invoice:
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li class="{{ empty($user->company_name) ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-{{ empty($user->company_name) ? 'times' : 'check' }} mr-2"></i>
                                                    Nama Perusahaan / ISP
                                                </li>
                                                <li class="{{ empty($user->company_address) ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-{{ empty($user->company_address) ? 'times' : 'check' }} mr-2"></i>
                                                    Alamat Perusahaan
                                                </li>
                                                <li class="{{ empty($user->company_phone) ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-{{ empty($user->company_phone) ? 'times' : 'check' }} mr-2"></i>
                                                    Telepon Perusahaan
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li class="{{ empty($user->pic_photo) ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-{{ empty($user->pic_photo) ? 'times' : 'check' }} mr-2"></i>
                                                    Foto PIC (Person In Charge)
                                                </li>
                                                <li class="{{ empty($user->isp_logo) ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-{{ empty($user->isp_logo) ? 'times' : 'check' }} mr-2"></i>
                                                    Logo ISP / Perusahaan
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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

// Image preview functions
function previewPicPhoto(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        
        // Validate file type
        var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG atau PNG.');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var preview = document.getElementById('pic-preview');
            var placeholder = document.getElementById('pic-placeholder');
            var uploadBtn = document.getElementById('upload-pic-btn');
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new img element if doesn't exist
                var newImg = document.createElement('img');
                newImg.id = 'pic-preview';
                newImg.src = e.target.result;
                newImg.alt = 'Foto PIC';
                newImg.className = 'img-thumbnail';
                newImg.style.maxWidth = '200px';
                newImg.style.maxHeight = '200px';
                
                placeholder.parentNode.replaceChild(newImg, placeholder);
            }
            
            uploadBtn.style.display = 'inline-block';
        }
        
        reader.readAsDataURL(file);
    }
}

function previewIspLogo(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        
        // Validate file type
        var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG atau PNG.');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var preview = document.getElementById('logo-preview');
            var placeholder = document.getElementById('logo-placeholder');
            var uploadBtn = document.getElementById('upload-logo-btn');
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new img element if doesn't exist
                var newImg = document.createElement('img');
                newImg.id = 'logo-preview';
                newImg.src = e.target.result;
                newImg.alt = 'Logo ISP';
                newImg.className = 'img-thumbnail';
                newImg.style.maxWidth = '200px';
                newImg.style.maxHeight = '200px';
                
                placeholder.parentNode.replaceChild(newImg, placeholder);
            }
            
            uploadBtn.style.display = 'inline-block';
        }
        
        reader.readAsDataURL(file);
    }
}

function deletePicPhoto() {
    if (confirm('Apakah Anda yakin ingin menghapus foto PIC?')) {
        $.ajax({
            url: '{{ route("profile.delete-pic-photo") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus foto');
            }
        });
    }
}

function deleteIspLogo() {
    if (confirm('Apakah Anda yakin ingin menghapus logo ISP?')) {
        $.ajax({
            url: '{{ route("profile.delete-isp-logo") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus logo');
            }
        });
    }
}
</script>
@stop
