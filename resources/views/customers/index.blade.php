@extends('adminlte::page')

@section('title', 'Data Pelanggan')

@section('content_header')
    <h1>
        Data Pelanggan
        <small>Management pelanggan WiFi</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Pelanggan</h3>
                <div class="card-tools">
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pelanggan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="customers-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Pelanggan</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Paket</th>
                                <th>Status</th>
                                <th>Tgl Daftar</th>
                                @if(auth()->user()->role->name === 'super_admin')
                                    <th>Created By</th>
                                @endif
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $customer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $customer->customer_id }}</strong>
                                </td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>{{ $customer->package->name ?? '-' }}</td>
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
                                <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                @if(auth()->user()->role->name === 'super_admin')
                                    <td>
                                        <div>
                                            <strong>{{ $customer->createdBy->name ?? 'System' }}</strong>
                                            <br><small class="text-muted">{{ $customer->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('customers.show', $customer->id) }}" 
                                           class="btn btn-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-{{ $customer->status == 'active' ? 'secondary' : 'success' }} btn-sm"
                                                onclick="toggleStatus('{{ $customer->id }}')"
                                                title="{{ $customer->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas fa-{{ $customer->status == 'active' ? 'user-slash' : 'user-check' }}"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm"
                                                onclick="generatePppoe('{{ $customer->id }}')"
                                                title="Generate PPPoE">
                                            <i class="fas fa-wifi"></i>
                                        </button>
                                        <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role->name === 'super_admin' ? '10' : '9' }}" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> Belum ada data pelanggan.
                                        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm ml-2">
                                            <i class="fas fa-plus"></i> Tambah Pelanggan Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
    $('#customers-table').DataTable({
        responsive: true,
        order: [[7, 'desc']], // Order by created date
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
});

function toggleStatus(customerId) {
    $.ajax({
        url: '/customers/' + customerId + '/toggle-status',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error('Gagal mengubah status pelanggan');
            }
        },
        error: function() {
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}

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
