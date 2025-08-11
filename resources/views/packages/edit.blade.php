@extends('adminlte::page')

@section('title', 'Edit Paket')

@section('content_header')
    <h1>
        Edit Paket Internet
        <small>Ubah data paket</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Paket</h3>
                <div class="card-tools">
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form method="POST" action="{{ route('packages.update', $package->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" 
                               value="{{ old('name', $package->name) }}" 
                               placeholder="Contoh: Paket 10 Mbps" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="router_id">Router <span class="text-danger">*</span></label>
                        <select class="form-control @error('router_id') is-invalid @enderror" 
                                id="router_id" name="router_id" required>
                            <option value="">Pilih Router</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}" {{ old('router_id', $package->router_id) == $router->id ? 'selected' : '' }}>
                                    {{ $router->name }} ({{ $router->ip_address }})
                                </option>
                            @endforeach
                        </select>
                        @error('router_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Pilih router untuk memfilter PPP Profile</small>
                    </div>

                    <div class="form-group">
                        <label for="ppp_profile_id">PPP Profile <span class="text-danger">*</span></label>
                        <select class="form-control @error('ppp_profile_id') is-invalid @enderror" 
                                id="ppp_profile_id" name="ppp_profile_id" required>
                            <option value="">Pilih PPP Profile</option>
                            @foreach($pppProfiles as $profile)
                                <option value="{{ $profile->id }}" 
                                        {{ old('ppp_profile_id', $package->ppp_profile_id) == $profile->id ? 'selected' : '' }}
                                        data-rate-limit="{{ $profile->rate_limit }}"
                                        data-name="{{ $profile->name }}">
                                    {{ $profile->name }} - {{ $profile->rate_limit }}
                                </option>
                            @endforeach
                        </select>
                        @error('ppp_profile_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Pilih PPP Profile untuk auto-populate field teknis</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Harga Paket (Termasuk PPN 11%) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" 
                                       value="{{ old('price', number_format($package->price, 0, '', '')) }}" 
                                       placeholder="166500" required>
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
                                       id="price_before_tax" name="price_before_tax" 
                                       value="{{ old('price_before_tax', number_format($package->price_before_tax, 0, '', '')) }}" 
                                       placeholder="150000" readonly>
                                <small id="ppn_display" class="form-text text-muted">Otomatis dihitung dari harga final</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="rate_limit">Rate Limit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('rate_limit') is-invalid @enderror" 
                                       id="rate_limit" name="rate_limit" 
                                       value="{{ old('rate_limit', $package->rate_limit) }}" 
                                       placeholder="10M/10M" readonly required>
                                @error('rate_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Otomatis terisi dari PPP Profile</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Deskripsi paket untuk pelanggan">{{ old('description', $package->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="billing_cycle">Siklus Tagihan <span class="text-danger">*</span></label>
                        <select class="form-control @error('billing_cycle') is-invalid @enderror" 
                                id="billing_cycle" name="billing_cycle" required>
                            <option value="monthly" {{ old('billing_cycle', $package->billing_cycle) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="quarterly" {{ old('billing_cycle', $package->billing_cycle) == 'quarterly' ? 'selected' : '' }}>3 Bulan</option>
                            <option value="semi-annual" {{ old('billing_cycle', $package->billing_cycle) == 'semi-annual' ? 'selected' : '' }}>6 Bulan</option>
                            <option value="annual" {{ old('billing_cycle', $package->billing_cycle) == 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        @error('billing_cycle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Status Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Paket
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
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
            $pppProfileSelect.prop('disabled', true).html('<option value="">Loading...</option>');
            
            // AJAX call to get PPP Profiles
            $.get('{{ route("packages.get-ppp-profiles") }}', { router_id: routerId })
                .done(function(data) {
                    $pppProfileSelect.html('<option value="">Pilih PPP Profile</option>');
                    
                    if (data.length > 0) {
                        $.each(data, function(index, profile) {
                            $pppProfileSelect.append(
                                $('<option></option>')
                                    .val(profile.id)
                                    .text(profile.display_name)
                                    .attr('data-rate-limit', profile.rate_limit)
                                    .attr('data-name', profile.name)
                            );
                        });
                        $pppProfileSelect.prop('disabled', false);
                    } else {
                        $pppProfileSelect.html('<option value="">Tidak ada PPP Profile</option>');
                    }
                })
                .fail(function() {
                    $pppProfileSelect.html('<option value="">Error loading profiles</option>');
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
