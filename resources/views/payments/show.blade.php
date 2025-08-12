@extends('adminlte::page')

@section('title', 'Detail Tagihan')

@section('content_header')
    <h1>
        Detail Tagihan
        <small>{{ $payment->invoice_number }}</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice"></i> Informasi Tagihan
                </h3>
                <div class="card-tools">
                    <span class="badge {{ $payment->getStatusBadgeClass() }} badge-lg">
                        {{ $payment->getStatusLabel() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nomor Invoice:</strong></td>
                                <td>{{ $payment->invoice_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Periode Tagihan:</strong></td>
                                <td>{{ $payment->billing_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jatuh Tempo:</strong></td>
                                <td>
                                    <span class="{{ $payment->isOverdue() ? 'text-danger font-weight-bold' : '' }}">
                                        {{ $payment->due_date->format('d/m/Y') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Tagihan:</strong></td>
                                <td class="h5 text-primary">{{ $payment->getFormattedAmount() }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            @if($payment->status === 'paid')
                            <tr>
                                <td><strong>Tanggal Bayar:</strong></td>
                                <td>{{ $payment->paid_date ? $payment->paid_date->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dikonfirmasi Oleh:</strong></td>
                                <td>{{ $payment->confirmedBy->name ?? '-' }}</td>
                            </tr>
                            @endif
                            @if($payment->isOverdue())
                            <tr>
                                <td><strong>Hari Terlambat:</strong></td>
                                <td>
                                    <span class="text-danger font-weight-bold">
                                        {{ $payment->getDaysOverdue() }} hari
                                    </span>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Dibuat Oleh:</strong></td>
                                <td>{{ $payment->createdBy->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Dibuat:</strong></td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($payment->notes)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Catatan:</strong>
                        <p class="mt-2">{{ $payment->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
            <div class="card-footer">
                @if($payment->status !== 'paid')
                    <button type="button" class="btn btn-success" onclick="markAsPaid('{{ $payment->id }}')">
                        <i class="fas fa-check"></i> Konfirmasi Pembayaran
                    </button>
                @endif
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Data Pelanggan
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>ID Pelanggan:</strong></td>
                        <td>{{ $payment->customer->customer_id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nama:</strong></td>
                        <td>{{ $payment->customer->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>No. HP:</strong></td>
                        <td>{{ $payment->customer->phone }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($payment->customer->status == 'active')
                                <span class="badge badge-success">Aktif</span>
                            @elseif($payment->customer->status == 'suspended')
                                <span class="badge badge-warning">Suspend</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($payment->customer->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
                <a href="{{ route('customers.show', $payment->customer->id) }}" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-eye"></i> Lihat Detail Pelanggan
                </a>
            </div>
        </div>

        <!-- Package Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-box"></i> Paket Internet
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nama Paket:</strong></td>
                        <td>{{ $payment->customer->package->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kecepatan:</strong></td>
                        <td>{{ $payment->customer->package->rate_limit ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Harga Paket:</strong></td>
                        <td>{{ $payment->customer->package ? 'Rp ' . number_format($payment->customer->package->price, 0, ',', '.') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Siklus Tagihan:</strong></td>
                        <td>
                            @switch($payment->customer->billing_cycle)
                                @case('monthly')
                                    Bulanan
                                    @break
                                @case('quarterly')
                                    Triwulan (3 Bulan)
                                    @break
                                @case('semi-annual')
                                    6 Bulan
                                    @break
                                @case('annual')
                                    Tahunan
                                    @break
                                @default
                                    {{ ucfirst($payment->customer->billing_cycle) }}
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Riwayat Pembayaran
                </h3>
            </div>
            <div class="card-body">
                @php
                    $allPayments = $payment->customer->payments()->orderBy('billing_date', 'asc')->get();
                    $installDate = $payment->customer->installation_date;
                    $now = now();
                    $periods = [];
                    $date = $installDate->copy();
                    $cycle = $payment->customer->billing_cycle ?? 'monthly';
                    $addPeriod = fn($d) => match($cycle) {
                        'monthly' => $d->addMonth(),
                        'quarterly' => $d->addMonths(3),
                        'semi-annual' => $d->addMonths(6),
                        'annual' => $d->addYear(),
                        default => $d->addMonth()
                    };
                    // Generate period from install to now
                    while ($date <= $now) {
                        $periods[] = $date->copy();
                        $addPeriod($date);
                    }
                @endphp
                <form>
                <ul class="list-group">
                @foreach($periods as $period)
                    @php
                        $p = $allPayments->first(fn($pay) => $pay->billing_date->format('Y-m') === $period->format('Y-m'));
                        $isPaid = $p && $p->status === 'paid';
                    @endphp
                    <li class="list-group-item d-flex align-items-center {{ $isPaid ? 'bg-success text-white' : ($p && $p->isOverdue() ? 'bg-danger text-white' : '') }}">
                        <input type="checkbox" disabled {{ $isPaid ? 'checked' : '' }} class="mr-2">
                        <span class="flex-grow-1">{{ $period->format('F Y') }}</span>
                        @if($p)
                            <span class="badge {{ $p->getStatusBadgeClass() }} ml-2">{{ $p->getStatusLabel() }}</span>
                        @else
                            <span class="badge badge-secondary ml-2">Belum Ada Data</span>
                        @endif
                    </li>
                @endforeach
                </ul>
                </form>
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
function markAsPaid(paymentId) {
    if (confirm('Konfirmasi pembayaran ini? Pelanggan akan otomatis diaktifkan jika sedang suspend.')) {
        $.ajax({
            url: '/payments/' + paymentId + '/mark-as-paid',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal konfirmasi pembayaran');
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan sistem: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
}
</script>
@stop
