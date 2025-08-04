@extends('adminlte::page')

@section('title', 'Covered Areas - Manajemen Area Coverage')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Covered Areas</h1>
            <p class="text-muted">Kelola area jangkauan WiFi Anda</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Covered Areas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt mr-2"></i>
                        Daftar Area Coverage
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="btnTambahArea">
                            <i class="fas fa-plus mr-1"></i>
                            Tambah Area
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($coveredAreas->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Belum ada area coverage</h4>
                            <p class="text-muted">Tambahkan area coverage pertama Anda untuk mulai mengelola jangkauan WiFi</p>
                            <button type="button" class="btn btn-primary" id="btnTambahAreaEmpty">
                                <i class="fas fa-plus mr-1"></i>
                                Tambah Area Coverage
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="coveredAreasTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        @if(auth()->user()->role === 'Super Admin')
                                            <th width="15%">Admin/POP</th>
                                        @endif
                                        <th width="25%">Provinsi</th>
                                        <th width="20%">Kab/Kota</th>
                                        <th width="20%">Kecamatan</th>
                                        <th width="15%">Desa/Kelurahan</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coveredAreas as $index => $area)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            @if(auth()->user()->role === 'Super Admin')
                                                <td>
                                                    <strong>{{ $area->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $area->user->email }}</small>
                                                </td>
                                            @endif
                                            <td>{{ $area->province->name ?? '-' }}</td>
                                            <td>{{ $area->city->name ?? '-' }}</td>
                                            <td>{{ $area->district->name ?? '-' }}</td>
                                            <td>{{ $area->village->name ?? 'Semua Desa' }}</td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm {{ $area->status === 'active' ? 'btn-success' : 'btn-secondary' }} btn-toggle-status"
                                                        data-id="{{ $area->id }}"
                                                        data-status="{{ $area->status }}">
                                                    <i class="fas {{ $area->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                                    {{ ucfirst($area->status) }}
                                                </button>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-info btn-edit" 
                                                            data-id="{{ $area->id }}"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-delete" 
                                                            data-id="{{ $area->id }}"
                                                            data-area="{{ $area->complete_area }}"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Area -->
<div class="modal fade" id="modalArea" tabindex="-1" role="dialog" aria-labelledby="modalAreaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formArea">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalAreaLabel">
                        <i class="fas fa-map-marked-alt mr-2"></i>Tambah Area Coverage
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select class="form-control" id="province_id" name="province_id" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city_id" class="form-label">Kab/Kota <span class="text-danger">*</span></label>
                                <select class="form-control" id="city_id" name="city_id" required disabled>
                                    <option value="">Pilih Kab/Kota</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select class="form-control" id="district_id" name="district_id" required disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="village_id" class="form-label">
                                    Desa/Kelurahan 
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <select class="form-control" id="village_id" name="village_id" disabled>
                                    <option value="">Semua Desa/Kelurahan</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Kosongkan untuk coverage seluruh kecamatan
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="2" 
                                  placeholder="Deskripsi singkat area coverage..."></textarea>
                    </div>
                    
                    <div class="form-group" id="statusGroup" style="display: none;">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control form-control-sm" id="status" name="status">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmit">
                        <i class="fas fa-save mr-1"></i>
                        <span id="submitText">Simpan</span>
                        <i class="fas fa-spinner fa-spin d-none" id="submitSpinner"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" role="dialog" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="modalConfirmDeleteLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus area coverage:</p>
                <div class="alert alert-warning">
                    <strong id="deleteAreaName"></strong>
                </div>
                <p class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmDelete">
                    <i class="fas fa-trash mr-1"></i>
                    <span id="deleteText">Hapus</span>
                    <i class="fas fa-spinner fa-spin d-none" id="deleteSpinner"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let editingId = null;
    let table = null;
    
    // Initialize DataTable
    if ($('#coveredAreasTable').length) {
        // For now, use simple DataTable without server-side processing
        table = $('#coveredAreasTable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            columnDefs: [
                { orderable: false, targets: [0, 3, 4] }
            ]
        });
    }
    
    // Load provinces
    function loadProvinces() {
        console.log('Loading provinces...');
        $.get('{{ url("/api/covered-areas/provinces") }}')
            .done(function(data) {
                console.log('Provinces loaded:', data.length, 'provinces');
                let options = '<option value="">Pilih Provinsi</option>';
                data.forEach(function(province) {
                    options += `<option value="${province.id}">${province.name}</option>`;
                });
                $('#province_id').html(options);
                console.log('Province dropdown populated');
            })
            .fail(function(xhr) {
                console.error('Failed to load provinces:', xhr);
                Swal.fire('Error', 'Gagal memuat data provinsi', 'error');
            });
    }
    
    // Load cities by province
    function loadCities(provinceId, selectedCityId = null) {
        console.log('Loading cities for province:', provinceId);
        
        if (!provinceId) {
            $('#city_id').html('<option value="">Pilih Kab/Kota</option>').prop('disabled', true);
            resetDistricts();
            return;
        }
        
        // Show loading state
        $('#city_id').html('<option value="">Loading...</option>').prop('disabled', true);
        
        $.get(`{{ url('/api/covered-areas/provinces') }}/${provinceId}/cities`)
            .done(function(data) {
                console.log('Cities loaded:', data.length, 'cities');
                let options = '<option value="">Pilih Kab/Kota</option>';
                data.forEach(function(city) {
                    const selected = selectedCityId && city.id == selectedCityId ? 'selected' : '';
                    options += `<option value="${city.id}" ${selected}>${city.name}</option>`;
                });
                $('#city_id').html(options).prop('disabled', false);
                
                console.log('City dropdown enabled, options count:', data.length);
                
                if (selectedCityId) {
                    $('#city_id').trigger('change');
                }
            })
            .fail(function(xhr) {
                console.error('Failed to load cities:', xhr);
                $('#city_id').html('<option value="">Error loading cities</option>').prop('disabled', true);
                Swal.fire('Error', 'Gagal memuat data kabupaten/kota', 'error');
            });
    }
    
    // Load districts by city
    function loadDistricts(cityId, selectedDistrictId = null) {
        if (!cityId) {
            resetDistricts();
            return;
        }
        
        $.get(`{{ url('/api/covered-areas/cities') }}/${cityId}/districts`)
            .done(function(data) {
                let options = '<option value="">Pilih Kecamatan</option>';
                data.forEach(function(district) {
                    const selected = selectedDistrictId && district.id == selectedDistrictId ? 'selected' : '';
                    options += `<option value="${district.id}" ${selected}>${district.name}</option>`;
                });
                $('#district_id').html(options).prop('disabled', false);
                
                if (selectedDistrictId) {
                    $('#district_id').trigger('change');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Gagal memuat data kecamatan', 'error');
            });
    }
    
    // Load villages by district
    function loadVillages(districtId, selectedVillageId = null) {
        if (!districtId) {
            resetVillages();
            return;
        }
        
        $.get(`{{ url('/api/covered-areas/districts') }}/${districtId}/villages`)
            .done(function(data) {
                let options = '<option value="">Semua Desa/Kelurahan</option>';
                data.forEach(function(village) {
                    const selected = selectedVillageId && village.id == selectedVillageId ? 'selected' : '';
                    options += `<option value="${village.id}" ${selected}>${village.name}</option>`;
                });
                $('#village_id').html(options).prop('disabled', false);
            })
            .fail(function() {
                Swal.fire('Error', 'Gagal memuat data desa/kelurahan', 'error');
            });
    }
    
    function resetDistricts() {
        $('#district_id').html('<option value="">Pilih Kecamatan</option>').prop('disabled', true);
        resetVillages();
    }
    
    function resetVillages() {
        $('#village_id').html('<option value="">Semua Desa/Kelurahan</option>').prop('disabled', false);
    }
    
    // Event handlers for cascading dropdowns
    function setupEventHandlers() {
        // Remove existing handlers to prevent duplicates
        $('#province_id').off('change.cascading');
        $('#city_id').off('change.cascading');
        $('#district_id').off('change.cascading');
        
        // Province change handler
        $('#province_id').on('change.cascading', function() {
            const provinceId = $(this).val();
            console.log('Province selected:', provinceId);
            loadCities(provinceId);
        });
        
        // City change handler
        $('#city_id').on('change.cascading', function() {
            const cityId = $(this).val();
            console.log('City selected:', cityId);
            loadDistricts(cityId);
        });
        
        // District change handler
        $('#district_id').on('change.cascading', function() {
            const districtId = $(this).val();
            console.log('District selected:', districtId);
            loadVillages(districtId);
        });
    }
    
    // Show modal for adding new area
    $('#btnTambahArea, #btnTambahAreaEmpty').click(function() {
        editingId = null;
        $('#modalAreaLabel').html('<i class="fas fa-map-marked-alt mr-2"></i>Tambah Area Coverage');
        $('#formArea')[0].reset();
        $('#statusGroup').hide();
        resetDistricts();
        resetVillages();
        loadProvinces();
        
        // Setup event handlers after modal is shown
        $('#modalArea').one('shown.bs.modal', function() {
            setupEventHandlers();
        });
        
        $('#modalArea').modal('show');
    });
    
    // Edit area
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        editingId = id;
        
        $('#modalAreaLabel').html('<i class="fas fa-edit mr-2"></i>Edit Area Coverage');
        $('#statusGroup').show();
        
        $.get(`{{ url('/covered-areas') }}/${id}/edit`)
            .done(function(response) {
                // Populate form with existing data
                loadProvinces();
                
                // Setup event handlers first
                $('#modalArea').one('shown.bs.modal', function() {
                    setupEventHandlers();
                });
                
                // Wait for provinces to load, then set values
                setTimeout(() => {
                    $('#province_id').val(response.coveredArea.province_id);
                    loadCities(response.coveredArea.province_id, response.coveredArea.city_id);
                    
                    setTimeout(() => {
                        $('#city_id').val(response.coveredArea.city_id);
                        loadDistricts(response.coveredArea.city_id, response.coveredArea.district_id);
                        
                        setTimeout(() => {
                            $('#district_id').val(response.coveredArea.district_id);
                            loadVillages(response.coveredArea.district_id, response.coveredArea.village_id);
                            
                            $('#description').val(response.coveredArea.description);
                            $('#status').val(response.coveredArea.status);
                        }, 500);
                    }, 500);
                }, 500);
                
                $('#modalArea').modal('show');
            })
            .fail(function() {
                Swal.fire('Error', 'Gagal memuat data area', 'error');
            });
    });
    
    // Submit form
    $('#formArea').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#btnSubmit');
        const submitText = $('#submitText');
        const submitSpinner = $('#submitSpinner');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        submitText.text('Menyimpan...');
        submitSpinner.removeClass('d-none');
        
        const formData = $(this).serialize();
        const url = editingId ? 
            `{{ url('/covered-areas') }}/${editingId}` : 
            '{{ route("covered-areas.store") }}';
        const method = editingId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: formData + (editingId ? '&_method=PUT' : ''),
            beforeSend: function() {
                console.log('Sending request to:', url);
                console.log('Method:', method);
                console.log('Data:', formData);
            },
            success: function(response) {
                console.log('Response received:', response);
                if (response.success) {
                    $('#modalArea').modal('hide');
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Data berhasil disimpan!');
                    }
                    
                    // Reload page untuk refresh data
                    console.log('Reloading page to refresh data...');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    
                    // Reset form and editing state
                    $('#formArea')[0].reset();
                    editingId = null;
                    $('#modalAreaLabel').html('<i class="fas fa-map-marked-alt mr-2"></i>Tambah Area Coverage');
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', response.message, 'error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                let message = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', message, 'error');
                } else {
                    alert('Error: ' + message);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false);
                submitText.text('Simpan');
                submitSpinner.addClass('d-none');
            }
        });
    });
    
    // Toggle status
    $(document).on('click', '.btn-toggle-status', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        Swal.fire({
            title: 'Konfirmasi',
            text: `Ubah status area menjadi ${newStatus === 'active' ? 'aktif' : 'tidak aktif'}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('/covered-areas') }}/${id}/toggle-status`, {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Status berhasil diubah!');
                        }
                        
                        // Reload DataTable atau halaman
                        if (typeof table !== 'undefined' && table !== null) {
                            table.ajax.reload(null, false);
                        } else {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', response.message, 'error');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                })
                .fail(function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Gagal mengubah status', 'error');
                    } else {
                        alert('Gagal mengubah status');
                    }
                });
            }
        });
    });
    
    // Delete area - dengan modal konfirmasi
    let deleteAreaId = null;
    
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const area = $(this).data('area');
        
        deleteAreaId = id;
        
        // Gunakan SweetAlert2 jika tersedia, jika tidak gunakan modal Bootstrap
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Yakin ingin menghapus area coverage:<br><strong>${area}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteArea(id);
                }
            });
        } else {
            // Fallback ke modal Bootstrap
            $('#deleteAreaName').text(area);
            $('#modalConfirmDelete').modal('show');
        }
    });
    
    // Handler untuk konfirmasi delete dari modal Bootstrap
    $('#btnConfirmDelete').click(function() {
        if (deleteAreaId) {
            $('#modalConfirmDelete').modal('hide');
            deleteArea(deleteAreaId);
        }
    });
    
    // Function untuk delete area
    function deleteArea(id) {
        const deleteBtn = $('#btnConfirmDelete');
        const deleteText = $('#deleteText');
        const deleteSpinner = $('#deleteSpinner');
        
        // Disable button dan show loading
        deleteBtn.prop('disabled', true);
        deleteText.text('Menghapus...');
        deleteSpinner.removeClass('d-none');
        
        $.ajax({
            url: `{{ url('/covered-areas') }}/${id}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Area berhasil dihapus!');
                    }
                    
                    // Reload DataTable atau halaman
                    if (typeof table !== 'undefined' && table !== null) {
                        table.ajax.reload(null, false);
                    } else {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', response.message, 'error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Gagal menghapus area', 'error');
                } else {
                    alert('Gagal menghapus area');
                }
            },
            complete: function() {
                // Re-enable button
                deleteBtn.prop('disabled', false);
                deleteText.text('Hapus');
                deleteSpinner.addClass('d-none');
                deleteAreaId = null;
            }
        });
    }
    
    // Initialize
    loadProvinces();
});
</script>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}

#coveredAreasTable_wrapper .row {
    margin-bottom: 1rem;
}

.form-group {
    position: relative;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

.modal-header.bg-primary {
    border-bottom: none;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

/* Responsive modal for smaller screens */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .row {
        margin: 0;
    }
    
    .col-md-6 {
        padding-left: 0;
        padding-right: 0;
    }
}

/* Input styling */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control:disabled {
    background-color: #f8f9fa;
    opacity: 0.8;
}

/* Button styling */
.btn-sm {
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
}

.close {
    opacity: 0.8;
}

.close:hover {
    opacity: 1;
}
</style>
@stop
