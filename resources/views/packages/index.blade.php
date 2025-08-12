@extends('adminlte::page')

@section('title', 'Paket Internet')

@section('content_header')
    <h1>
        Paket Internet
        <small>Management paket untuk pelanggan</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Paket</h3>
                <div class="card-tools">
                    <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Paket
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="packages-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Paket</th>
                                <th>PPP Profile</th>
                                <th>Harga</th>
                                <th>Rate Limit</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                @if(auth()->user()->role->name === 'super_admin')
                                    <th>Created By</th>
                                    <th>Jumlah Pelanggan</th>
                                @endif
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $index => $package)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $package->name }}</td>
                                <td>
                                    @if($package->pppProfile)
                                        <span class="badge badge-info">{{ $package->pppProfile->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>Rp {{ number_format($package->price, 0, ',', '.') }}</strong>
                                        @if($package->price_before_tax)
                                            <br><small class="text-muted">Sebelum PPN: Rp {{ number_format($package->price_before_tax, 0, ',', '.') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $package->rate_limit }}</td>
                                <td>{{ Str::limit($package->description, 50) ?? '-' }}</td>
                                <td>
                                    @if($package->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                @if(auth()->user()->role->name === 'super_admin')
                                    <td>
                                        <div>
                                            <strong>{{ $package->createdBy->name ?? 'System' }}</strong>
                                            <br><small class="text-muted">{{ $package->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $package->customers->count() }} Pelanggan</span>
                                    </td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('packages.show', $package->id) }}" 
                                           class="btn btn-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('packages.edit', $package->id) }}" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-{{ $package->is_active ? 'secondary' : 'success' }} btn-sm"
                                                onclick="toggleStatus('{{ $package->id }}')"
                                                title="{{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas fa-{{ $package->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                        </button>
                                        <form method="POST" action="{{ route('packages.destroy', $package->id) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
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
                                <td colspan="{{ auth()->user()->role->name === 'super_admin' ? '10' : '8' }}" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> Belum ada data paket.
                                        <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm ml-2">
                                            <i class="fas fa-plus"></i> Tambah Paket Pertama
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
    $('#packages-table').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
});

function toggleStatus(packageId) {
    $.ajax({
        url: '/packages/' + packageId + '/toggle-status',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error('Gagal mengubah status paket');
            }
        },
        error: function() {
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}
</script>
@stop
