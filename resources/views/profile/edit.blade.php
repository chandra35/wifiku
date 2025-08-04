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

<div class="row">
    <!-- Left col (Profile Picture & Info) -->
    <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($user->getPicPhotoUrl())
                        <img class="profile-user-img img-fluid img-circle clickable-photo" 
                             src="{{ $user->getPicPhotoUrl() }}" 
                             alt="User profile picture"
                             style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                             onclick="document.getElementById('pic_photo').click()"
                             title="Click to change photo">
                    @else
                        <img class="profile-user-img img-fluid img-circle clickable-photo" 
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23dee2e6'/%3E%3Ctext x='50' y='60' text-anchor='middle' font-size='30' fill='%236c757d'%3E{{ substr($user->name, 0, 1) }}%3C/text%3E%3C/svg%3E" 
                             alt="User profile picture"
                             style="cursor: pointer;"
                             onclick="document.getElementById('pic_photo').click()"
                             title="Click to upload photo">
                    @endif
                    
                    <!-- Camera overlay icon -->
                    <div class="photo-overlay" onclick="document.getElementById('pic_photo').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>

                <p class="text-muted text-center">
                    <span class="badge badge-{{ $user->role->name === 'super_admin' ? 'danger' : ($user->role->name === 'admin' ? 'warning' : 'info') }}">
                        {{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No Role' }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>PPPoE Secrets</b> <a class="float-right">{{ $user->pppoeSecrets->count() }}</a>
                    </li>
                    @if($user->role && $user->role->name !== 'super_admin')
                    <li class="list-group-item">
                        <b>Assigned Routers</b> <a class="float-right">{{ $user->routers->count() }}</a>
                    </li>
                    @endif
                    <li class="list-group-item">
                        <b>Data Completion</b> 
                        <span class="float-right">
                            <span class="badge badge-{{ $user->hasCompleteCompanyInfo() ? 'success' : 'warning' }}">
                                {{ $user->hasCompleteCompanyInfo() ? '100%' : number_format(($user->company_name ? 20 : 0) + ($user->company_address ? 20 : 0) + ($user->company_phone ? 20 : 0) + ($user->pic_photo ? 20 : 0) + ($user->isp_logo ? 20 : 0), 0) }}%
                            </span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> <a class="float-right">{{ $user->created_at->format('M Y') }}</a>
                    </li>
                </ul>

                <!-- Upload Photo Form (Hidden) -->
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="photo-form" style="display: none;">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden fields -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="company_name" value="{{ $user->company_name }}">
                    <input type="hidden" name="company_address" value="{{ $user->company_address }}">
                    <input type="hidden" name="company_phone" value="{{ $user->company_phone }}">
                    
                    <input type="file" 
                           id="pic_photo" 
                           name="pic_photo" 
                           class="d-none" 
                           accept="image/jpeg,image/png,image/jpg"
                           onchange="uploadPhoto();">
                </form>

                @if($user->getPicPhotoUrl())
                <button type="button" class="btn btn-danger btn-block btn-sm mt-2" onclick="deletePicPhoto()">
                    <i class="fas fa-trash mr-1"></i> Remove Photo
                </button>
                @endif
            </div>
        </div>

        <!-- Company Logo -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-image mr-1"></i> Company Logo
                </h3>
            </div>
            <div class="card-body text-center">
                @if($user->getIspLogoUrl())
                    <img src="{{ $user->getIspLogoUrl() }}" 
                         alt="Company Logo" 
                         class="img-fluid mb-2 clickable-logo" 
                         style="max-height: 150px; cursor: pointer;"
                         onclick="document.getElementById('isp_logo').click()"
                         title="Click to change logo">
                @else
                    <div class="text-muted mb-3 clickable-logo" 
                         style="cursor: pointer;"
                         onclick="document.getElementById('isp_logo').click()"
                         title="Click to upload logo">
                        <i class="fas fa-image fa-3x"></i>
                        <p class="mt-2">Click to upload logo</p>
                    </div>
                @endif

                <!-- Upload Logo Form (Hidden) -->
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="logo-form" style="display: none;">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden fields -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="company_name" value="{{ $user->company_name }}">
                    <input type="hidden" name="company_address" value="{{ $user->company_address }}">
                    <input type="hidden" name="company_phone" value="{{ $user->company_phone }}">
                    
                    <input type="file" 
                           id="isp_logo" 
                           name="isp_logo" 
                           class="d-none" 
                           accept="image/jpeg,image/png,image/jpg"
                           onchange="uploadLogo();">
                </form>

                @if($user->getIspLogoUrl())
                <button type="button" class="btn btn-danger btn-block btn-sm mt-2" onclick="deleteIspLogo()">
                    <i class="fas fa-trash mr-1"></i> Remove Logo
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Right col (Main Content) -->
    <div class="col-md-9">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-tab" data-toggle="pill" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                            <i class="fas fa-user mr-2"></i>Personal Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="company-tab" data-toggle="pill" href="#company" role="tab" aria-controls="company" aria-selected="false">
                            <i class="fas fa-building mr-2"></i>Company Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="security-tab" data-toggle="pill" href="#security" role="tab" aria-controls="security" aria-selected="false">
                            <i class="fas fa-shield-alt mr-2"></i>Security
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">
                                            <i class="fas fa-user mr-1"></i>Full Name
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
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">
                                            <i class="fas fa-envelope mr-1"></i>Email Address
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
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-info-circle mr-1"></i>Account Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <p><strong>User ID:</strong><br>
                                                    <span class="text-muted">#{{ $user->id }}</span></p>
                                                </div>
                                                <div class="col-md-3">
                                                    <p><strong>Role:</strong><br>
                                                    <span class="badge badge-{{ $user->role->name === 'super_admin' ? 'danger' : ($user->role->name === 'admin' ? 'warning' : 'info') }}">
                                                        {{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No Role' }}
                                                    </span></p>
                                                </div>
                                                <div class="col-md-3">
                                                    <p><strong>Member Since:</strong><br>
                                                    <span class="text-muted">{{ $user->created_at->format('d M Y') }}</span></p>
                                                </div>
                                                <div class="col-md-3">
                                                    <p><strong>Last Updated:</strong><br>
                                                    <span class="text-muted">{{ $user->updated_at->format('d M Y') }}</span></p>
                                                </div>
                                            </div>
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
                                    <i class="fas fa-save mr-2"></i>Update Personal Information
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
                                <strong>Important:</strong> This company information will be used for invoice generation in the future.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_name">
                                            <i class="fas fa-building mr-1"></i>Company / ISP Name
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('company_name') is-invalid @enderror" 
                                               id="company_name" 
                                               name="company_name" 
                                               value="{{ old('company_name', $user->company_name) }}" 
                                               placeholder="e.g., PT. Internet Indonesia">
                                        @error('company_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="company_phone">
                                            <i class="fas fa-phone mr-1"></i>Company Phone
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('company_phone') is-invalid @enderror" 
                                               id="company_phone" 
                                               name="company_phone" 
                                               value="{{ old('company_phone', $user->company_phone) }}" 
                                               placeholder="e.g., +62 21 1234567">
                                        @error('company_phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_address">
                                            <i class="fas fa-map-marker-alt mr-1"></i>Company Address
                                        </label>
                                        <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                                  id="company_address" 
                                                  name="company_address" 
                                                  rows="5"
                                                  placeholder="Complete company address for invoicing">{{ old('company_address', $user->company_address) }}</textarea>
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
                                    <i class="fas fa-save mr-2"></i>Update Company Information
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-shield-alt mr-2"></i>
                                <strong>Account Security:</strong> Use a strong and unique password. Don't use the same password as other accounts.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_password">
                                            <i class="fas fa-key mr-1"></i>Current Password
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
                                            <i class="fas fa-lock mr-1"></i>New Password
                                        </label>
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Password must be at least 8 characters.</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">
                                            <i class="fas fa-lock mr-1"></i>Confirm New Password
                                        </label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key mr-2"></i>Change Password
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-info-circle mr-1"></i>Password Requirements
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success mr-2"></i>At least 8 characters</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Mix of uppercase and lowercase</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Include numbers</li>
                                                <li><i class="fas fa-check text-success mr-2"></i>Use symbols (!@#$%)</li>
                                                <li><i class="fas fa-times text-danger mr-2"></i>Don't use personal information</li>
                                                <li><i class="fas fa-times text-danger mr-2"></i>Don't reuse passwords</li>
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

/* Clickable photo and logo styles */
.clickable-photo, .clickable-logo {
    transition: all 0.3s ease;
}

.clickable-photo:hover, .clickable-logo:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.profile-user-img {
    position: relative;
}

.photo-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    cursor: pointer;
    z-index: 1;
}

.box-profile:hover .photo-overlay {
    opacity: 1;
}

.clickable-logo:hover {
    background-color: rgba(0, 123, 255, 0.1);
    border-radius: 8px;
    padding: 10px;
}

/* Loading spinner */
.loading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
}

.loading-spinner > div {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    text-align: center;
}
</style>
@stop

@section('js')
<!-- Loading Spinner -->
<div class="loading-spinner" id="loading-spinner">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Uploading...</p>
    </div>
</div>

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
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }
        
        $('#password').next('.form-text').html('Password must be at least 8 characters. Strength: <span class="' + strengthClass + '">' + strengthText + '</span>');
    });
});

// Upload Photo Function
function uploadPhoto() {
    var fileInput = document.getElementById('pic_photo');
    var file = fileInput.files[0];
    
    if (!file) return;
    
    // Validate file type
    var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        alert('Unsupported file format. Use JPG or PNG.');
        fileInput.value = '';
        return;
    }
    
    // Validate file size (2MB = 2048KB)
    if (file.size > 2048 * 1024) {
        alert('File size too large. Maximum 2MB.');
        fileInput.value = '';
        return;
    }
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    
    // Submit form
    document.getElementById('photo-form').submit();
}

// Upload Logo Function
function uploadLogo() {
    var fileInput = document.getElementById('isp_logo');
    var file = fileInput.files[0];
    
    if (!file) return;
    
    // Validate file type
    var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        alert('Unsupported file format. Use JPG or PNG.');
        fileInput.value = '';
        return;
    }
    
    // Validate file size (2MB = 2048KB)
    if (file.size > 2048 * 1024) {
        alert('File size too large. Maximum 2MB.');
        fileInput.value = '';
        return;
    }
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    
    // Submit form
    document.getElementById('logo-form').submit();
}

// Delete Functions
function deletePicPhoto() {
    if (confirm('Are you sure you want to delete the profile photo?')) {
        // Show loading spinner
        document.getElementById('loading-spinner').style.display = 'block';
        
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
                    document.getElementById('loading-spinner').style.display = 'none';
                }
            },
            error: function() {
                alert('An error occurred while deleting the photo');
                document.getElementById('loading-spinner').style.display = 'none';
            }
        });
    }
}

function deleteIspLogo() {
    if (confirm('Are you sure you want to delete the company logo?')) {
        // Show loading spinner
        document.getElementById('loading-spinner').style.display = 'block';
        
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
                    document.getElementById('loading-spinner').style.display = 'none';
                }
            },
            error: function() {
                alert('An error occurred while deleting the logo');
                document.getElementById('loading-spinner').style.display = 'none';
            }
        });
    }
}
</script>
@stop
