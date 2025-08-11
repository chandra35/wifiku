@extends('adminlte::page')

@section('title', 'Tambah Paket')

@section('content_header')
    <h1>
        Tambah Paket Internet
        <small>Buat paket baru untuk pelanggan</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Paket</h3>
            </div>
            <form method="POST" action="{{ route('packages.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Paket <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Contoh: Paket 10M" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="router_id">Router <span class="text-danger">*</span></label>
                                <select class="form-control @error('router_id') is-invalid @enderror" 
                                        id="router_id" name="router_id" required>
                                    <option value="">Pilih Router</option>
                                    @foreach($routers as $router)
                                        <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                            {{ $router->name }} ({{ $router->ip_address }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('router_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Pilih router untuk memfilter PPP Profile</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ppp_profile_id">PPP Profile <span class="text-danger">*</span></label>
                                <select class="form-control @error('ppp_profile_id') is-invalid @enderror" 
                                        id="ppp_profile_id" name="ppp_profile_id" required disabled>
                                    <option value="">Pilih Router terlebih dahulu</option>
                                </select>
                                @error('ppp_profile_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">PPP Profile akan muncul setelah memilih router</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Harga Paket (Termasuk PPN 11%) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price') }}" 
                                       placeholder="166.500" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Harga final yang akan dibayar pelanggan</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price_before_tax">Harga Sebelum PPN</label>
                                <input type="text" class="form-control" 
                                       id="price_before_tax" name="price_before_tax" value="{{ old('price_before_tax') }}" 
                                       placeholder="150.000" readonly>
                                <small id="ppn_display" class="form-text text-muted">Otomatis dihitung dari harga final</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="rate_limit">Rate Limit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('rate_limit') is-invalid @enderror" 
                                       id="rate_limit" name="rate_limit" value="{{ old('rate_limit') }}" 
                                       placeholder="10M/10M" readonly>
                                @error('rate_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Otomatis terisi dari PPP Profile yang dipilih</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Deskripsi paket untuk pelanggan">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="billing_cycle">Siklus Tagihan <span class="text-danger">*</span></label>
                        <select class="form-control @error('billing_cycle') is-invalid @enderror" 
                                id="billing_cycle" name="billing_cycle" required>
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" 
                                   name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Paket Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Paket
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">
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
                        <li>Rate Limit menentukan kecepatan maksimal</li>
                        <li>Burst memberikan kecepatan tambahan sementara</li>
                        <li>PPP Profile akan otomatis dibuat di MikroTik</li>
                        <li>Pastikan nama paket unik</li>
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
    // Format rupiah function
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    // Parse rupiah to number
    function parseRupiah(str) {
        return parseInt(str.replace(/[^\d]/g, '')) || 0;
    }
    
    // Format number input as rupiah
    function formatNumberInput(input) {
        let value = parseRupiah(input.val());
        if (value > 0) {
            input.val(value.toLocaleString('id-ID'));
        }
    }
    
    // Calculate price before tax from final price
    function calculatePriceBeforeTax() {
        let finalPrice = parseRupiah($('#price').val());
        if (finalPrice > 0) {
            let priceBeforeTax = Math.round(finalPrice / 1.11);
            let ppnAmount = finalPrice - priceBeforeTax;
            $('#price_before_tax').val(priceBeforeTax.toLocaleString('id-ID'));
            $('#ppn_display').text('PPN 11%: ' + formatRupiah(ppnAmount) + ' (Harga sebelum PPN: ' + formatRupiah(priceBeforeTax) + ')');
        } else {
            $('#price_before_tax').val('');
            $('#ppn_display').text('Otomatis dihitung dari harga final');
        }
    }
    
    // Load PPP Profiles when router is selected
    $('#router_id').change(function() {
        let routerId = $(this).val();
        let $pppProfileSelect = $('#ppp_profile_id');
        
        if (routerId) {
            // Enable PPP Profile dropdown and show loading
            $pppProfileSelect.prop('disabled', true).html('<option value="">Loading PPP Profiles...</option>');
            
            // AJAX call to get PPP Profiles
            let ajaxUrl = '{{ route("packages.get-ppp-profiles") }}';
            console.log('Making AJAX call to:', ajaxUrl, 'with router_id:', routerId);
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.get(ajaxUrl, { router_id: routerId })
                .done(function(data) {
                    console.log('PPP Profiles response successful:', data);
                    $pppProfileSelect.html('<option value="">Pilih PPP Profile</option>');
                    
                    if (data && data.length > 0) {
                        $.each(data, function(index, profile) {
                            console.log('Adding profile:', profile.name);
                            $pppProfileSelect.append(
                                $('<option></option>')
                                    .val(profile.id)
                                    .text(profile.display_name)
                                    .attr('data-rate-limit', profile.rate_limit || '')
                                    .attr('data-name', profile.name || '')
                            );
                        });
                        $pppProfileSelect.prop('disabled', false);
                        console.log('PPP Profile dropdown populated with', data.length, 'profiles');
                    } else {
                        $pppProfileSelect.html('<option value="">Tidak ada PPP Profile untuk router ini</option>');
                        console.log('No PPP Profiles found for router');
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('AJAX request failed!');
                    console.error('URL:', ajaxUrl);
                    console.error('Router ID:', routerId);
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    
                    let errorMessage = 'Error: ' + error;
                    if (xhr.status === 404) {
                        errorMessage = 'Error: Endpoint not found (404)';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Error: Access denied (403)';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error: Server error (500)';
                    }
                    
                    $pppProfileSelect.html('<option value="">' + errorMessage + '</option>');
                });
        } else {
            // Reset PPP Profile dropdown
            $pppProfileSelect.prop('disabled', true).html('<option value="">Pilih Router terlebih dahulu</option>');
            // Clear technical fields
            $('#rate_limit').val('');
        }
    });
    
    // Auto-populate fields when PPP Profile is selected
    $('#ppp_profile_id').change(function() {
        let selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            // Get data attributes from selected option
            $('#rate_limit').val(selectedOption.data('rate-limit') || '');
        } else {
            // Clear all fields if no profile selected
            $('#rate_limit').val('');
        }
    });
    
    // Format final price input and calculate price before tax
    $('#price').on('input', function() {
        formatNumberInput($(this));
        calculatePriceBeforeTax();
    });
    
    // Auto generate package name from profile (optional fallback)
    $('#name').on('input', function() {
        // No longer auto-generating mikrotik profile name since it's handled by PPP Profile
    });
    
    // Initial formatting for existing values
    formatNumberInput($('#price'));
    calculatePriceBeforeTax();
});
</script>
@stop
