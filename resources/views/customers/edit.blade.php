@extends('adminlte::page')

@section('title', 'Edit Pelanggan')

@section('content_header')
    <h1>
        Edit Pelanggan
        <small>Ubah data pelanggan</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Pelanggan - {{ $customer->customer_id }}</h3>
            </div>
            <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary"><i class="fas fa-user"></i> Data Pribadi</h5>
                            <hr>
                            
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">No. HP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $customer->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="identity_number">No. KTP/NIK</label>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" 
                                       id="identity_number" name="identity_number" value="{{ old('identity_number', $customer->identity_number) }}">
                                @error('identity_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="birth_date">Tanggal Lahir</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                       id="birth_date" name="birth_date" value="{{ old('birth_date', $customer->birth_date ? (is_string($customer->birth_date) ? $customer->birth_date : $customer->birth_date->format('Y-m-d')) : '') }}">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="gender">Jenis Kelamin</label>
                                <select class="form-control @error('gender') is-invalid @enderror" 
                                        id="gender" name="gender">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-success"><i class="fas fa-map-marker-alt"></i> Alamat</h5>
                            <hr>

                            <div class="form-group">
                                <label for="address">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3" 
                                          placeholder="Alamat lengkap dengan RT/RW" required>{{ old('address', $customer->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="province_id">Provinsi</label>
                                <select class="form-control @error('province_id') is-invalid @enderror" 
                                        id="province_id" name="province_id">
                                    <option value="">Pilih Provinsi</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->code }}" {{ old('province_id', $customer->province_id) == $province->code ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="city_id">Kota/Kabupaten</label>
                                <select class="form-control @error('city_id') is-invalid @enderror" 
                                        id="city_id" name="city_id">
                                    <option value="">Pilih Kota/Kabupaten</option>
                                    @if($customer->city_id)
                                        <option value="{{ $customer->city_id }}" selected>{{ $customer->city->name ?? '' }}</option>
                                    @endif
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district_id">Kecamatan</label>
                                        <select class="form-control @error('district_id') is-invalid @enderror" 
                                                id="district_id" name="district_id">
                                            <option value="">Pilih Kecamatan</option>
                                            @if($customer->district_id)
                                                <option value="{{ $customer->district_id }}" selected>{{ $customer->district->name ?? '' }}</option>
                                            @endif
                                        </select>
                                        @error('district_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="village_id">Kelurahan/Desa</label>
                                        <select class="form-control @error('village_id') is-invalid @enderror" 
                                                id="village_id" name="village_id">
                                            <option value="">Pilih Kelurahan/Desa</option>
                                            @if($customer->village_id)
                                                <option value="{{ $customer->village_id }}" selected>{{ $customer->village->name ?? '' }}</option>
                                            @endif
                                        </select>
                                        @error('village_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="postal_code">Kode Pos</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" 
                                       placeholder="12345">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-warning"><i class="fas fa-wifi"></i> Paket Internet</h5>
                            <hr>

                            <div class="form-group">
                                <label for="package_id">Pilih Paket <span class="text-danger">*</span></label>
                                <select class="form-control @error('package_id') is-invalid @enderror" 
                                        id="package_id" name="package_id" required>
                                    <option value="">Pilih Paket Internet</option>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}" {{ old('package_id', $customer->package_id) == $package->id ? 'selected' : '' }}>
                                            {{ $package->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('package_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="installation_date">Tanggal Pasang <small class="text-muted">(format: dd/mm/yyyy)</small></label>
                                <input type="date" class="form-control @error('installation_date') is-invalid @enderror" 
                                       id="installation_date" name="installation_date" 
                                       value="{{ old('installation_date', $customer->installation_date ? (is_string($customer->installation_date) ? $customer->installation_date : $customer->installation_date->format('Y-m-d')) : '') }}">
                                @error('installation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan format Indonesia: dd/mm/yyyy
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-info"><i class="fas fa-money-bill"></i> Billing & Status</h5>
                            <hr>

                            <div class="form-group">
                                <label for="billing_cycle">Siklus Tagihan <span class="text-danger">*</span></label>
                                <select class="form-control @error('billing_cycle') is-invalid @enderror" 
                                        id="billing_cycle" name="billing_cycle" required>
                                    <option value="">Pilih Siklus Tagihan</option>
                                    <option value="monthly" {{ old('billing_cycle', $customer->billing_cycle) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="quarterly" {{ old('billing_cycle', $customer->billing_cycle) == 'quarterly' ? 'selected' : '' }}>Triwulan (3 Bulan)</option>
                                    <option value="semi-annual" {{ old('billing_cycle', $customer->billing_cycle) == 'semi-annual' ? 'selected' : '' }}>6 Bulan</option>
                                    <option value="annual" {{ old('billing_cycle', $customer->billing_cycle) == 'annual' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                                @error('billing_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="next_billing_date">Tagihan Berikutnya <small class="text-muted">(format: dd/mm/yyyy)</small></label>
                                <input type="date" class="form-control @error('next_billing_date') is-invalid @enderror" 
                                       id="next_billing_date" name="next_billing_date" 
                                       value="{{ old('next_billing_date', $customer->next_billing_date ? (is_string($customer->next_billing_date) ? $customer->next_billing_date : $customer->next_billing_date->format('Y-m-d')) : '') }}">
                                @error('next_billing_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan format Indonesia: dd/mm/yyyy
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="status">Status Pelanggan <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                    <option value="suspended" {{ old('status', $customer->status) == 'suspended' ? 'selected' : '' }}>Suspend</option>
                                    <option value="terminated" {{ old('status', $customer->status) == 'terminated' ? 'selected' : '' }}>Terminate</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Catatan tambahan...">{{ old('notes', $customer->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Pelanggan
                    </button>
                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Pelanggan</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-id-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">ID Pelanggan</span>
                        <span class="info-box-number">{{ $customer->customer_id }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Terdaftar</span>
                        <span class="info-box-number">{{ $customer->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dibuat Oleh</span>
                        <span class="info-box-number">{{ $customer->createdBy->name ?? 'System' }}</span>
                    </div>
                </div>

                @if($customer->package)
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-wifi"></i> Paket Saat Ini</h5>
                    <p class="mb-2"><strong>Nama:</strong> {{ $customer->package->name }}</p>
                    <p class="mb-2"><strong>Harga:</strong> Rp {{ number_format($customer->package->price, 0, ',', '.') }}</p>
                    <p class="mb-0"><strong>Kecepatan:</strong> {{ $customer->package->speed ?? '-' }}</p>
                </div>
                @endif

                @if($customer->province_id || $customer->city_id)
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-map-marker-alt"></i> Lokasi Saat Ini</h5>
                    <p class="mb-1"><strong>Provinsi:</strong> {{ $customer->province->name ?? '-' }}</p>
                    <p class="mb-1"><strong>Kota:</strong> {{ $customer->city->name ?? '-' }}</p>
                    <p class="mb-1"><strong>Kecamatan:</strong> {{ $customer->district->name ?? '-' }}</p>
                    <p class="mb-0"><strong>Desa:</strong> {{ $customer->village->name ?? '-' }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.info-box {
    margin-bottom: 15px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Format phone number
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Format identity number
    $('#identity_number').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Format postal code
    $('#postal_code').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Location cascade with AJAX
    $('#province_id').change(function() {
        let provinceId = $(this).val();
        $('#city_id').prop('disabled', !provinceId).html('<option value="">Pilih Kota/Kabupaten</option>');
        $('#district_id').prop('disabled', true).html('<option value="">Pilih Kecamatan</option>');
        $('#village_id').prop('disabled', true).html('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (provinceId) {
            $.get(`/location/cities/${provinceId}`)
                .done(function(cities) {
                    cities.forEach(function(city) {
                        $('#city_id').append(`<option value="${city.id}">${city.name}</option>`);
                    });
                })
                .fail(function() {
                    alert('Gagal memuat data kota/kabupaten');
                });
        }
    });
    
    $('#city_id').change(function() {
        let cityId = $(this).val();
        $('#district_id').prop('disabled', !cityId).html('<option value="">Pilih Kecamatan</option>');
        $('#village_id').prop('disabled', true).html('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (cityId) {
            $.get(`/location/districts/${cityId}`)
                .done(function(districts) {
                    districts.forEach(function(district) {
                        $('#district_id').append(`<option value="${district.id}">${district.name}</option>`);
                    });
                })
                .fail(function() {
                    alert('Gagal memuat data kecamatan');
                });
        }
    });
    
    $('#district_id').change(function() {
        let districtId = $(this).val();
        $('#village_id').prop('disabled', !districtId).html('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (districtId) {
            $.get(`/location/villages/${districtId}`)
                .done(function(villages) {
                    villages.forEach(function(village) {
                        $('#village_id').append(`<option value="${village.id}">${village.name}</option>`);
                    });
                })
                .fail(function() {
                    alert('Gagal memuat data kelurahan/desa');
                });
        }
    });

    // Load existing city, district, village on page load
    @if($customer->province_id)
        $('#province_id').trigger('change');
        
        @if($customer->city_id)
            setTimeout(function() {
                $('#city_id').val('{{ $customer->city_id }}').trigger('change');
                
                @if($customer->district_id)
                    setTimeout(function() {
                        $('#district_id').val('{{ $customer->district_id }}').trigger('change');
                        
                        @if($customer->village_id)
                            setTimeout(function() {
                                $('#village_id').val('{{ $customer->village_id }}');
                            }, 1000);
                        @endif
                    }, 1000);
                @endif
            }, 1000);
        @endif
    @endif
});
</script>
@stop
