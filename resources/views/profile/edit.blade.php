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
        
        <!-- Area Information -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt mr-1"></i> Area Information
                </h3>
            </div>
            <div class="card-body">
                @if($user->hasCompleteAreaInfo())
                    <div class="mb-2">
                        <small class="text-muted d-block">Province:</small>
                        <strong>{{ $user->province->name ?? 'Not set' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">City/Regency:</small>
                        <strong>{{ $user->city->name ?? 'Not set' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">District:</small>
                        <strong>{{ $user->district->name ?? 'Not set' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Village:</small>
                        <strong>{{ $user->village->name ?? 'Not set' }}</strong>
                    </div>
                    @if($user->full_address)
                    <hr>
                    <div class="mb-0">
                        <small class="text-muted d-block">Complete Address:</small>
                        <small>{{ $user->full_address }}</small>
                    </div>
                    @endif
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                        <p class="mb-2">Area information not set</p>
                        <a href="#address" onclick="$('#address-tab').click();" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> Add Area Info
                        </a>
                    </div>
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
                        <a class="nav-link" id="address-tab" data-toggle="pill" href="#address" role="tab" aria-controls="address" aria-selected="false">
                            <i class="fas fa-map-marker-alt mr-2"></i>Address & Area
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
                    
                    <!-- Address & Area Tab -->
                    <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="alert alert-info">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <strong>Area Information:</strong> Please select your location details. This information helps us provide better service and support.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="province_id">
                                            <i class="fas fa-map mr-1"></i>Province <span class="text-muted">(Provinsi)</span>
                                        </label>
                                        <select name="province_id" id="province_id" class="form-control" style="width: 100%;">
                                            <option value="">Select Province</option>
                                        </select>
                                        <div class="loading-spinner d-none" id="province-loading">
                                            <small class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Loading provinces...</small>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="city_id">
                                            <i class="fas fa-city mr-1"></i>City/Regency <span class="text-muted">(Kota/Kabupaten)</span>
                                        </label>
                                        <select name="city_id" id="city_id" class="form-control" style="width: 100%;" disabled>
                                            <option value="">Select City/Regency</option>
                                        </select>
                                        <div class="loading-spinner d-none" id="city-loading">
                                            <small class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Loading cities...</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district_id">
                                            <i class="fas fa-building mr-1"></i>District <span class="text-muted">(Kecamatan)</span>
                                        </label>
                                        <select name="district_id" id="district_id" class="form-control" style="width: 100%;" disabled>
                                            <option value="">Select District</option>
                                        </select>
                                        <div class="loading-spinner d-none" id="district-loading">
                                            <small class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Loading districts...</small>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="village_id">
                                            <i class="fas fa-home mr-1"></i>Village <span class="text-muted">(Kelurahan/Desa)</span>
                                        </label>
                                        <select name="village_id" id="village_id" class="form-control" style="width: 100%;" disabled>
                                            <option value="">Select Village</option>
                                        </select>
                                        <div class="loading-spinner d-none" id="village-loading">
                                            <small class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Loading villages...</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_address">
                                    <i class="fas fa-address-card mr-1"></i>Complete Address <span class="text-muted">(Alamat Lengkap)</span>
                                </label>
                                <textarea name="full_address" 
                                          id="full_address" 
                                          rows="4" 
                                          class="form-control @error('full_address') is-invalid @enderror" 
                                          placeholder="Enter your detailed address (street name, house number, RT/RW, etc.)">{{ old('full_address', $user->full_address) }}</textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Enter your detailed address. The regional information (Province, City, District, Village) will be automatically added.
                                </small>
                                @error('full_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <!-- Address Preview Card -->
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-eye mr-2"></i>Address Preview
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div id="address-preview" class="text-muted">
                                        @if($user->hasCompleteAreaInfo())
                                            <div class="mb-2">
                                                <strong>Current Address:</strong><br>
                                                {{ $user->getCompleteAddress() }}
                                            </div>
                                        @else
                                            <i class="fas fa-info-circle mr-2"></i>Your complete address will appear here as you fill in the fields above.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for other data -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="company_name" value="{{ $user->company_name }}">
                            <input type="hidden" name="company_address" value="{{ $user->company_address }}">
                            <input type="hidden" name="company_phone" value="{{ $user->company_phone }}">
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Update Address Information
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

/* Area Form Styling */
#address .form-group select {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-image: none;
    min-height: 38px;
}

#address .form-group select:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#address .form-group select:disabled {
    background-color: #e9ecef;
    opacity: 1;
    cursor: not-allowed;
}

#address .loading-spinner {
    position: static;
    background: none;
    padding: 5px 0;
    margin-top: 5px;
}

#address .loading-spinner small {
    color: #6c757d;
    font-size: 0.75rem;
}

/* Address Preview Card */
#address-preview {
    min-height: 50px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

/* Form Label Styling */
#address .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

#address .form-group label .text-muted {
    font-weight: 400;
    font-size: 0.875rem;
}

/* Tab content padding */
.tab-content {
    padding-top: 1rem;
}

#address-preview {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 15px;
    min-height: 60px;
}

.loading-spinner.d-none {
    display: none !important;
}

.form-group .loading-spinner {
    position: relative;
    background: transparent;
    font-size: 14px;
    color: #6c757d;
    margin-top: 5px;
    padding: 5px 0;
}

.clickable-photo:hover {
    opacity: 0.8;
    transition: opacity 0.3s ease;
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

// Area/Regional Dropdowns Functionality
$(document).ready(function() {
    // Load provinces on page load
    loadProvinces();
    
    // Load existing area data if available
    var currentProvinceId = '{{ $user->province_id }}';
    var currentCityId = '{{ $user->city_id }}';
    var currentDistrictId = '{{ $user->district_id }}';
    var currentVillageId = '{{ $user->village_id }}';
    
    // Province change event
    $('#province_id').on('change', function() {
        var provinceId = $(this).val();
        $('#city_id').empty().append('<option value="">Select City/Regency</option>').prop('disabled', true);
        $('#district_id').empty().append('<option value="">Select District</option>').prop('disabled', true);
        $('#village_id').empty().append('<option value="">Select Village</option>').prop('disabled', true);
        
        if (provinceId) {
            loadCities(provinceId);
        }
        updateAddressPreview();
    });
    
    // City change event
    $('#city_id').on('change', function() {
        var cityId = $(this).val();
        $('#district_id').empty().append('<option value="">Select District</option>').prop('disabled', true);
        $('#village_id').empty().append('<option value="">Select Village</option>').prop('disabled', true);
        
        if (cityId) {
            loadDistricts(cityId);
        }
        updateAddressPreview();
    });
    
    // District change event
    $('#district_id').on('change', function() {
        var districtId = $(this).val();
        $('#village_id').empty().append('<option value="">Select Village</option>').prop('disabled', true);
        
        if (districtId) {
            loadVillages(districtId);
        }
        updateAddressPreview();
    });
    
    // Village change event
    $('#village_id').on('change', function() {
        updateAddressPreview();
    });
    
    // Full address change event
    $('#full_address').on('input', function() {
        updateAddressPreview();
    });
    
    // Load provinces
    function loadProvinces() {
        $('#province-loading').removeClass('d-none');
        
        $.ajax({
            url: '{{ url("/api/covered-areas/provinces") }}',
            type: 'GET',
            success: function(response) {
                $('#province_id').empty().append('<option value="">Select Province</option>');
                
                $.each(response, function(index, province) {
                    var selected = province.id == currentProvinceId ? 'selected' : '';
                    $('#province_id').append('<option value="' + province.id + '" ' + selected + '>' + province.name + '</option>');
                });
                
                $('#province-loading').addClass('d-none');
                
                // Load cities if province is pre-selected
                if (currentProvinceId) {
                    loadCities(currentProvinceId);
                }
            },
            error: function() {
                $('#province-loading').addClass('d-none');
                console.error('Error loading provinces');
            }
        });
    }
    
    // Load cities based on province
    function loadCities(provinceId) {
        $('#city-loading').removeClass('d-none');
        
        $.ajax({
            url: '{{ url("/api/covered-areas/provinces") }}/' + provinceId + '/cities',
            type: 'GET',
            success: function(response) {
                $('#city_id').empty().append('<option value="">Select City/Regency</option>').prop('disabled', false);
                
                $.each(response, function(index, city) {
                    var selected = city.id == currentCityId ? 'selected' : '';
                    $('#city_id').append('<option value="' + city.id + '" ' + selected + '>' + city.name + '</option>');
                });
                
                $('#city-loading').addClass('d-none');
                
                // Load districts if city is pre-selected
                if (currentCityId) {
                    loadDistricts(currentCityId);
                }
            },
            error: function() {
                $('#city-loading').addClass('d-none');
                console.error('Error loading cities');
            }
        });
    }
    
    // Load districts based on city
    function loadDistricts(cityId) {
        $('#district-loading').removeClass('d-none');
        
        $.ajax({
            url: '{{ url("/api/covered-areas/cities") }}/' + cityId + '/districts',
            type: 'GET',
            success: function(response) {
                $('#district_id').empty().append('<option value="">Select District</option>').prop('disabled', false);
                
                $.each(response, function(index, district) {
                    var selected = district.id == currentDistrictId ? 'selected' : '';
                    $('#district_id').append('<option value="' + district.id + '" ' + selected + '>' + district.name + '</option>');
                });
                
                $('#district-loading').addClass('d-none');
                
                // Load villages if district is pre-selected
                if (currentDistrictId) {
                    loadVillages(currentDistrictId);
                }
            },
            error: function() {
                $('#district-loading').addClass('d-none');
                console.error('Error loading districts');
            }
        });
    }
    
    // Load villages based on district
    function loadVillages(districtId) {
        $('#village-loading').removeClass('d-none');
        
        $.ajax({
            url: '{{ url("/api/covered-areas/districts") }}/' + districtId + '/villages',
            type: 'GET',
            success: function(response) {
                $('#village_id').empty().append('<option value="">Select Village</option>').prop('disabled', false);
                
                $.each(response, function(index, village) {
                    var selected = village.id == currentVillageId ? 'selected' : '';
                    $('#village_id').append('<option value="' + village.id + '" ' + selected + '>' + village.name + '</option>');
                });
                
                $('#village-loading').addClass('d-none');
            },
            error: function() {
                $('#village-loading').addClass('d-none');
                console.error('Error loading villages');
            }
        });
    }
    
    // Update address preview
    function updateAddressPreview() {
        var fullAddress = $('#full_address').val();
        var provinceName = $('#province_id option:selected').text();
        var cityName = $('#city_id option:selected').text();
        var districtName = $('#district_id option:selected').text();
        var villageName = $('#village_id option:selected').text();
        
        var addressParts = [];
        
        if (fullAddress && fullAddress.trim() !== '') {
            addressParts.push(fullAddress.trim());
        }
        
        if (villageName && villageName !== 'Select Village') {
            addressParts.push(villageName);
        }
        
        if (districtName && districtName !== 'Select District') {
            addressParts.push(districtName);
        }
        
        if (cityName && cityName !== 'Select City/Regency') {
            addressParts.push(cityName);
        }
        
        if (provinceName && provinceName !== 'Select Province') {
            addressParts.push(provinceName);
        }
        
        if (addressParts.length > 0) {
            $('#address-preview').html('<div class="mb-2"><strong>Complete Address:</strong><br>' + addressParts.join(', ') + '</div>');
        } else {
            $('#address-preview').html('<i class="fas fa-info-circle mr-2"></i>Your complete address will appear here as you fill in the fields above.');
        }
    }
    
    // Initial address preview update
    setTimeout(function() {
        updateAddressPreview();
    }, 1000);
});
</script>
@stop
