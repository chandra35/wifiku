@extends('adminlte::page')

@section('title', 'Detail Paket')

@section('content_header')
    <h1>
        Detail Paket Internet
        <small>Informasi lengkap paket</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Paket 10M</h3>
                <div class="card-tools">
                    <span class="badge badge-success">Aktif</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama Paket:</strong></td>
                                <td>Paket 10M</td>
                            </tr>
                            <tr>
                                <td><strong>Harga:</strong></td>
                                <td>Rp 150.000</td>
                            </tr>
                            <tr>
                                <td><strong>Rate Limit:</strong></td>
                                <td>10M/10M</td>
                            </tr>
                            <tr>
                                <td><strong>Burst Limit:</strong></td>
                                <td>15M/15M</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                            <tr>
                                <td><strong>MikroTik Profile:</strong></td>
                                <td>paket_10m</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ date('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Diperbarui:</strong></td>
                                <td>{{ date('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <strong>Deskripsi:</strong>
                        <p class="mt-2">Paket internet 10 Mbps untuk rumahan dengan kualitas stabil dan harga terjangkau.</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('packages.edit', 1) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Paket
                </a>
                <a href="{{ route('packages.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Penggunaan</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Pelanggan</span>
                        <span class="info-box-number">25</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pelanggan Aktif</span>
                        <span class="info-box-number">23</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pelanggan Suspend</span>
                        <span class="info-box-number">2</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-primary btn-block mb-2" onclick="syncToMikrotik(1)">
                    <i class="fas fa-sync"></i> Sync ke MikroTik
                </button>
                <button class="btn btn-info btn-block mb-2">
                    <i class="fas fa-chart-bar"></i> Lihat Laporan
                </button>
                <button class="btn btn-success btn-block">
                    <i class="fas fa-download"></i> Export Data
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function syncToMikrotik(packageId) {
    $.ajax({
        url: '/packages/' + packageId + '/sync-to-mikrotik',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error('Gagal sinkronisasi ke MikroTik');
            }
        },
        error: function() {
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}
</script>
@stop
