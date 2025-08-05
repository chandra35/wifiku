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

<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fas fa-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
@endif

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

<!-- Main Profile Content -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-toggle="pill" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                            <i class="fas fa-user mr-2"></i>Informasi Pribadi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="company-tab" data-toggle="pill" href="#company" role="tab" aria-controls="company" aria-selected="false">
                            <i class="fas fa-building mr-2"></i>Informasi Perusahaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="photos-tab" data-toggle="pill" href="#photos" role="tab" aria-controls="photos" aria-selected="false">
                            <i class="fas fa-images mr-2"></i>Foto & Logo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="security-tab" data-toggle="pill" href="#security" role="tab" aria-controls="security" aria-selected="false">
                            <i class="fas fa-shield-alt mr-2"></i>Keamanan
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profile-tabContent">
                    
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="name">
                                            <i class="fas fa-user mr-1"></i>Nama Lengkap
                                        </label>
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
                                        <label for="email">
                                            <i class="fas fa-envelope mr-1"></i>Email
                                        </label>
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
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h4 class="card-title">Informasi Akun</h4>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>User ID:</strong><br>
                                            <small class="text-muted">{{ $user->id }}</small></p>
                                            
                                            <p><strong>Role:</strong><br>
                                            <span class="badge badge-{{ $user->role->name === 'super_admin' ? 'danger' : ($user->role->name === 'admin' ? 'warning' : 'info') }}">
                                                {{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No Role' }}
                                            </span></p>
                                            
                                            <p><strong>Member Since:</strong><br>
                                            <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small></p>
                                            
                                            <p><strong>Terakhir Login:</strong><br>
                                            <small class="text-muted">{{ $user->updated_at->format('d M Y H:i') }}</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for other data to maintain them -->
                            <input type="hidden" name="company_name" value="{{ $user->company_name }}">
                            <input type="hidden" name="company_address" value="{{ $user->company_address }}">
                            <input type="hidden" name="company_phone" value="{{ $user->company_phone }}">
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Company Information Tab -->
                    <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Informasi Penting:</strong> Data perusahaan ini akan digunakan untuk generate invoice/billing di masa depan.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_name">
                                            <i class="fas fa-building mr-1"></i>Nama Perusahaan / ISP
                                        </label>
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
                                        <label for="company_phone">
                                            <i class="fas fa-phone mr-1"></i>Telepon Perusahaan
                                        </label>
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
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_address">
                                            <i class="fas fa-map-marker-alt mr-1"></i>Alamat Perusahaan
                                        </label>
                                        <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                                  id="company_address" 
                                                  name="company_address" 
                                                  rows="5"
                                                  placeholder="Alamat lengkap perusahaan untuk invoice">{{ old('company_address', $user->company_address) }}</textarea>
                                        @error('company_address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for other data -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Simpan Informasi Perusahaan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Photos & Logo Tab -->
                    <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Catatan:</strong> Foto dan logo ini akan digunakan untuk generate invoice. Pastikan foto berkualitas baik (max 2MB, format JPG/PNG).
                            </div>
                            
                            <div class="row">
                                <!-- PIC Photo -->
                                <div class="col-md-6">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-user-circle mr-2"></i>Foto PIC (Person In Charge)
                                            </h4>
                                        </div>
                                        <div class="card-body text-center">
                                            @if($user->getPicPhotoUrl())
                                                <img id="pic-preview" 
                                                     src="{{ $user->getPicPhotoUrl() }}" 
                                                     alt="Foto PIC" 
                                                     class="img-thumbnail mb-3" 
                                                     style="max-width: 200px; max-height: 200px;">
                                                <br>
                                                <button type="button" class="btn btn-danger btn-sm mb-3" onclick="deletePicPhoto()">
                                                    <i class="fas fa-trash mr-1"></i>Hapus Foto
                                                </button>
                                            @else
                                                <div id="pic-placeholder" class="mb-3">
                                                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                                                    <p class="text-muted mt-2">Belum ada foto PIC</p>
                                                </div>
                                            @endif
                                            
                                            <div class="form-group">
                                                <label for="pic_photo" class="btn btn-primary btn-block">
                                                    <i class="fas fa-upload mr-2"></i>Pilih Foto PIC
                                                </label>
                                                <input type="file" 
                                                       id="pic_photo" 
                                                       name="pic_photo" 
                                                       class="d-none" 
                                                       accept="image/jpeg,image/png,image/jpg"
                                                       onchange="previewPicPhoto(this)">
                                                <small class="form-text text-muted">Max 2MB, format JPG/PNG</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ISP Logo -->
                                <div class="col-md-6">
                                    <div class="card card-outline card-success">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-image mr-2"></i>Logo ISP / Perusahaan
                                            </h4>
                                        </div>
                                        <div class="card-body text-center">
                                            @if($user->getIspLogoUrl())
                                                <img id="logo-preview" 
                                                     src="{{ $user->getIspLogoUrl() }}" 
                                                     alt="Logo ISP" 
                                                     class="img-thumbnail mb-3" 
                                                     style="max-width: 200px; max-height: 200px;">
                                                <br>
                                                <button type="button" class="btn btn-danger btn-sm mb-3" onclick="deleteIspLogo()">
                                                    <i class="fas fa-trash mr-1"></i>Hapus Logo
                                                </button>
                                            @else
                                                <div id="logo-placeholder" class="mb-3">
                                                    <i class="fas fa-image fa-5x text-muted"></i>
                                                    <p class="text-muted mt-2">Belum ada logo ISP</p>
                                                </div>
                                            @endif
                                            
                                            <div class="form-group">
                                                <label for="isp_logo" class="btn btn-success btn-block">
                                                    <i class="fas fa-upload mr-2"></i>Pilih Logo ISP
                                                </label>
                                                <input type="file" 
                                                       id="isp_logo" 
                                                       name="isp_logo" 
                                                       class="d-none" 
                                                       accept="image/jpeg,image/png,image/jpg"
                                                       onchange="previewIspLogo(this)">
                                                <small class="form-text text-muted">Max 2MB, format JPG/PNG</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for other data -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="company_name" value="{{ $user->company_name }}">
                            <input type="hidden" name="company_address" value="{{ $user->company_address }}">
                            <input type="hidden" name="company_phone" value="{{ $user->company_phone }}">
                            
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Simpan Foto & Logo
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="alert alert-danger">
                                <i class="fas fa-shield-alt mr-2"></i>
                                <strong>Keamanan Akun:</strong> Gunakan password yang kuat dan unik. Jangan gunakan password yang sama dengan akun lain.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_password">
                                            <i class="fas fa-key mr-1"></i>Password Saat Ini
                                        </label>
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
                                        <label for="password">
                                            <i class="fas fa-lock mr-1"></i>Password Baru
                                        </label>
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
                                        <label for="password_confirmation">
                                            <i class="fas fa-lock mr-1"></i>Konfirmasi Password Baru
                                        </label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key mr-2"></i>Ubah Password
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-header">
                                            <h4 class="card-title">Tips Keamanan</h4>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success mr-2"></i>Gunakan minimal 8 karakter</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Kombinasi huruf besar dan kecil</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Gunakan angka</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Gunakan simbol (!@#$%)</li>
                                                <li><i class="fas fa-times text-danger mr-2"></i>Jangan gunakan informasi pribadi</li>
                                                <li><i class="fas fa-times text-danger mr-2"></i>Jangan gunakan password yang sama</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Readiness Status -->
<div class="row">
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
@stop

@section('css')
<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}
.nav-tabs .nav-link {
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
}
.nav-tabs .nav-link.active {
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}
.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.alert-dismissible {
    position: relative;
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
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new img element if doesn't exist
                var newImg = document.createElement('img');
                newImg.id = 'pic-preview';
                newImg.src = e.target.result;
                newImg.alt = 'Foto PIC';
                newImg.className = 'img-thumbnail mb-3';
                newImg.style.maxWidth = '200px';
                newImg.style.maxHeight = '200px';
                
                placeholder.parentNode.replaceChild(newImg, placeholder);
                
                // Add delete button
                var deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn btn-danger btn-sm mb-3';
                deleteBtn.innerHTML = '<i class="fas fa-trash mr-1"></i>Hapus Foto';
                deleteBtn.onclick = function() { deletePicPhoto(); };
                
                newImg.parentNode.insertBefore(deleteBtn, newImg.nextSibling);
                newImg.parentNode.insertBefore(document.createElement('br'), deleteBtn);
            }
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
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new img element if doesn't exist
                var newImg = document.createElement('img');
                newImg.id = 'logo-preview';
                newImg.src = e.target.result;
                newImg.alt = 'Logo ISP';
                newImg.className = 'img-thumbnail mb-3';
                newImg.style.maxWidth = '200px';
                newImg.style.maxHeight = '200px';
                
                placeholder.parentNode.replaceChild(newImg, placeholder);
                
                // Add delete button
                var deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn btn-danger btn-sm mb-3';
                deleteBtn.innerHTML = '<i class="fas fa-trash mr-1"></i>Hapus Logo';
                deleteBtn.onclick = function() { deleteIspLogo(); };
                
                newImg.parentNode.insertBefore(deleteBtn, newImg.nextSibling);
                newImg.parentNode.insertBefore(document.createElement('br'), deleteBtn);
            }
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
