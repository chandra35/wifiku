@extends('adminlte::page')

@section('title', 'Tambah Pelanggan')

@section('content_header')
    <h1>
        Tambah Pelanggan
        <small>Daftarkan pelanggan baru</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Pendaftaran Pelanggan</h3>
            </div>
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary"><i class="fas fa-user"></i> Data Pribadi</h5>
                            <hr>
                            
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Nama lengkap pelanggan" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">No. HP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" 
                                       placeholder="081234567890" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="email@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="identity_number">No. KTP/NIK</label>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" 
                                       id="identity_number" name="identity_number" value="{{ old('identity_number') }}" 
                                       placeholder="3201234567890123">
                                @error('identity_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="birth_date">Tanggal Lahir</label>
                                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                               id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                                        @error('birth_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Jenis Kelamin</label>
                                        <select class="form-control @error('gender') is-invalid @enderror" 
                                                id="gender" name="gender">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-success"><i class="fas fa-map-marker-alt"></i> Alamat</h5>
                            <hr>

                            <div class="form-group">
                                <label for="address">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3" 
                                          placeholder="Alamat lengkap dengan RT/RW" required>{{ old('address') }}</textarea>
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
                                        <option value="{{ $province->code }}" {{ old('province_id') == $province->code ? 'selected' : '' }}>
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
                                        id="city_id" name="city_id" disabled>
                                    <option value="">Pilih Kota/Kabupaten</option>
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
                                                id="district_id" name="district_id" disabled>
                                            <option value="">Pilih Kecamatan</option>
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
                                                id="village_id" name="village_id" disabled>
                                            <option value="">Pilih Kelurahan/Desa</option>
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
                                       id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
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
                                        <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
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
                                       value="{{ old('installation_date', date('Y-m-d')) }}">
                                @error('installation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan format Indonesia: dd/mm/yyyy
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-info"><i class="fas fa-money-bill"></i> Billing</h5>
                            <hr>

                            <div class="form-group">
                                <label for="billing_cycle">Siklus Tagihan</label>
                                <select class="form-control @error('billing_cycle') is-invalid @enderror" 
                                        id="billing_cycle" name="billing_cycle">
                                    <option value="monthly" {{ old('billing_cycle', 'monthly') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>3 Bulan</option>
                                    <option value="semi-annual" {{ old('billing_cycle') == 'semi-annual' ? 'selected' : '' }}>6 Bulan</option>
                                    <option value="annual" {{ old('billing_cycle') == 'annual' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                                @error('billing_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="next_billing_date">Tanggal Tagihan Berikutnya <small class="text-muted">(format: dd/mm/yyyy)</small></label>
                                <input type="date" class="form-control @error('next_billing_date') is-invalid @enderror" 
                                       id="next_billing_date" name="next_billing_date" 
                                       value="{{ old('next_billing_date', date('Y-m-d', strtotime('+1 month'))) }}">
                                @error('next_billing_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Gunakan format Indonesia: dd/mm/yyyy
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="status" 
                                   name="status" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status">Aktifkan Pelanggan</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pelanggan
                    </button>
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
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Panduan!</h5>
                    <ul class="mb-0">
                        <li>Field bertanda (*) wajib diisi</li>
                        <li>ID Pelanggan akan dibuat otomatis</li>
                        <li>Pastikan data benar sebelum menyimpan</li>
                        <li>PPPoE Secret dapat dibuat setelah data tersimpan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
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
});
</script>
@stop
