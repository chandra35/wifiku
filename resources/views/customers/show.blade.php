@extends('adminlte::page')

@section('title', 'Detail Pelanggan')

@section('content_header')
    <h1>
        Detail Pelanggan
        <small>Informasi lengkap pelanggan</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $customer->customer_id }} - {{ $customer->name }}</h3>
                <div class="card-tools">
                    @if($customer->status == 'active')
                        <span class="badge badge-success">Aktif</span>
                    @elseif($customer->status == 'inactive')
                        <span class="badge badge-warning">Nonaktif</span>
                    @elseif($customer->status == 'suspended')
                        <span class="badge badge-danger">Suspend</span>
                    @elseif($customer->status == 'terminated')
                        <span class="badge badge-dark">Terminate</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($customer->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary"><i class="fas fa-user"></i> Data Pribadi</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID Pelanggan:</strong></td>
                                <td>{{ $customer->customer_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. HP:</strong></td>
                                <td>{{ $customer->phone }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $customer->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. KTP:</strong></td>
                                <td>{{ $customer->identity_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Lahir:</strong></td>
                                <td>{{ $customer->birth_date ? (is_string($customer->birth_date) ? $customer->birth_date : $customer->birth_date->format('d/m/Y')) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin:</strong></td>
                                <td>{{ $customer->gender == 'male' ? 'Laki-laki' : ($customer->gender == 'female' ? 'Perempuan' : '-') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-success"><i class="fas fa-map-marker-alt"></i> Alamat</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td>{{ $customer->address }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kelurahan:</strong></td>
                                <td>{{ $customer->village->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kecamatan:</strong></td>
                                <td>{{ $customer->district->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kota:</strong></td>
                                <td>{{ $customer->city->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Provinsi:</strong></td>
                                <td>{{ $customer->province->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Pos:</strong></td>
                                <td>{{ $customer->postal_code ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-warning"><i class="fas fa-wifi"></i> Paket Internet</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Paket:</strong></td>
                                <td>{{ $customer->package->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Harga:</strong></td>
                                <td>Rp {{ number_format($customer->package->price ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kecepatan:</strong></td>
                                <td>{{ $customer->package->speed ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tgl Pasang:</strong></td>
                                <td>{{ $customer->installation_date ? (is_string($customer->installation_date) ? $customer->installation_date : $customer->installation_date->format('d/m/Y')) : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-info"><i class="fas fa-money-bill"></i> Billing</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Siklus Tagihan:</strong></td>
                                <td>
                                    @switch($customer->billing_cycle)
                                        @case('monthly')
                                            Bulanan
                                            @break
                                        @case('quarterly')
                                            Triwulan
                                            @break
                                        @case('semi-annual')
                                            6 Bulan
                                            @break
                                        @case('annual')
                                            Tahunan
                                            @break
                                        @default
                                            {{ ucfirst($customer->billing_cycle) }}
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tagihan Berikutnya:</strong></td>
                                <td>{{ $customer->next_billing_date ? (is_string($customer->next_billing_date) ? $customer->next_billing_date : $customer->next_billing_date->format('d/m/Y')) : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($customer->status == 'active')
                                        <span class="badge badge-success">Aktif</span>
                                    @elseif($customer->status == 'inactive')
                                        <span class="badge badge-warning">Nonaktif</span>
                                    @elseif($customer->status == 'suspended')
                                        <span class="badge badge-danger">Suspend</span>
                                    @elseif($customer->status == 'terminated')
                                        <span class="badge badge-dark">Terminate</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($customer->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Catatan:</strong></td>
                                <td>{{ $customer->notes ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Pelanggan
                </a>
                <button class="btn btn-primary" onclick="generatePppoe('{{ $customer->id }}')">
                    <i class="fas fa-wifi"></i> Generate PPPoE
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PPPoE Secret</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Status PPPoE</h5>
                    <p class="mb-2"><strong>Username:</strong> john_doe_001</p>
                    <p class="mb-2"><strong>Password:</strong> ********</p>
                    <p class="mb-0"><strong>Profile:</strong> paket_10m</p>
                </div>
                
                <button class="btn btn-success btn-block mb-2">
                    <i class="fas fa-sync"></i> Sync ke MikroTik
                </button>
                <button class="btn btn-warning btn-block">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pembayaran</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-success">{{ date('d M Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-money-bill bg-green"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Pembayaran Diterima</h3>
                            <div class="timeline-body">
                                Pembayaran untuk bulan {{ date('F Y') }} sebesar Rp 150.000
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-user bg-blue"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Pelanggan Terdaftar</h3>
                            <div class="timeline-body">
                                Pelanggan mendaftar dengan paket Paket 10M
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function generatePppoe(customerId) {
    if (confirm('Generate PPPoE Secret untuk pelanggan ini?')) {
        $.ajax({
            url: '/customers/' + customerId + '/generate-pppoe',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error('Gagal membuat PPPoE Secret');
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            }
        });
    }
}
</script>
@stop
