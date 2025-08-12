@extends('adminlte::page')

@section('title', 'Tagihan Belum Bayar')

@section('content_header')
    <h1>
        Tagihan Belum Bayar
        <small>Monitoring pembayaran pelanggan</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\Payment::where('status', 'pending')->whereMonth('billing_date', now()->month)->whereYear('billing_date', now()->year)->whereNull('paid_date')->whereHas('customer')->count() }}</h3>
                        <p>Belum Bayar (Bulan Berjalan)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\Payment::whereIn('status', ['pending','overdue'])->where('due_date', '<', now()->toDateString())->whereNull('paid_date')->whereHas('customer')->count() }}</h3>
                        <p>Terlambat</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\Payment::where('status', 'paid')->whereMonth('paid_date', now()->month)->count() }}</h3>
                        <p>Sudah Bayar (Bulan Ini)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>Rp {{ number_format(\App\Models\Payment::whereIn('status', ['pending','overdue'])->whereNull('paid_date')->whereHas('customer')->whereMonth('billing_date', '<=', now()->month)->whereYear('billing_date', '<=', now()->year)->sum('amount'), 0, ',', '.') }}</h3>
                        <p>Total Tunggakan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Daftar Tagihan
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-danger btn-sm" onclick="autoSuspendOverdue()">
                        <i class="fas fa-ban"></i> Auto Suspend Terlambat
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status', 'pending') == 'pending' ? 'active' : '' }}" 
                           href="{{ route('payments.index', ['status' => 'pending']) }}">
                            <i class="fas fa-clock text-warning"></i> Belum Bayar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'overdue' ? 'active' : '' }}" 
                           href="{{ route('payments.index', ['status' => 'overdue']) }}">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Terlambat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'paid' ? 'active' : '' }}" 
                           href="{{ route('payments.index', ['status' => 'paid']) }}">
                            <i class="fas fa-check-circle text-success"></i> Sudah Bayar
                        </a>
                    </li>
                </ul>

                <!-- Filter Pelanggan & Search Form -->
                <form method="GET" class="mb-3">
                    <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <select name="customer_id" class="form-control select2">
                                <option value="">-- Semua Pelanggan --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->name }} ({{ $cust->customer_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 mb-2">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari nama pelanggan, ID pelanggan, atau nomor invoice..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            @if(request('search') || request('customer_id'))
                                <a href="{{ route('payments.index', ['status' => request('status', 'pending')]) }}" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Payments Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Periode Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Bulan Terlambat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <form id="bulkPaymentForm" method="POST" action="{{ route('payments.bulkPay') }}">
                            @csrf
                            <tbody>
                            @if(request('status') == 'overdue')
                                @forelse($payments as $index => $customer)
                                <tr class="table-danger">
                                    <td>{{ $payments->firstItem() + $index }}</td>
                                    <td>-</td>
                                    <td>
                                        <strong>{{ $customer->name }}</strong>
                                        <br><small class="text-muted">{{ $customer->customer_id }}</small>
                                    </td>
                                    <td>{{ $customer->package->name ?? '-' }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                        <ul class="mb-0 pl-3">
                                            @foreach($customer->getOverduePayments() as $ovd)
                                                <li>{{ \Carbon\Carbon::parse($ovd->billing_date)->translatedFormat('F Y') }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-success btn-sm" onclick="showPayMultiModal('{{ $customer->id }}')" title="Konfirmasi Bayar">
                                                <i class="fas fa-check"></i> Bayar
                                            </button>
                                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary btn-sm" title="Lihat Pelanggan">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> Tidak ada pelanggan yang terlambat.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            @else
                                @forelse($payments as $index => $payment)
                                <tr class="{{ $payment->isOverdue() ? 'table-danger' : '' }}">
                                    <td>
                                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}">
                                        {{ $payments->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <strong>{{ $payment->invoice_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $payment->customer->name }}</strong>
                                            <br><small class="text-muted">{{ $payment->customer->customer_id }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $payment->customer->package->name ?? '-' }}</td>
                                    <td>{{ $payment->billing_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="{{ $payment->isOverdue() ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $payment->due_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->getFormattedAmount() }}</td>
                                    <td>
                                        <span class="badge {{ $payment->getStatusBadgeClass() }}">
                                            {{ $payment->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->isOverdue())
                                            <span class="text-danger font-weight-bold">
                                                {{ $payment->getDaysOverdue() }} hari
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('payments.show', $payment->id) }}" 
                                               class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($payment->status !== 'paid')
                                                <button type="button" 
                                                        class="btn btn-success btn-sm"
                                                        onclick="showPayMultiModal('{{ $payment->customer->id }}')"
                                                        title="Konfirmasi Bayar">
                                                    <i class="fas fa-check"></i> Bayar
                                                </button>
                                            @endif
                                            <a href="{{ route('customers.show', $payment->customer->id) }}" 
                                               class="btn btn-secondary btn-sm" title="Lihat Pelanggan">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> 
                                            @if(request('status') == 'paid')
                                                Belum ada pembayaran yang dikonfirmasi.
                                            @else
                                                Semua tagihan sudah dibayar. Excellent!
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            @endif
                            </tbody>
                        </form>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <button type="button" class="btn btn-success" onclick="submitBulkPayment()">
                            <i class="fas fa-check-double"></i> Bayar Terpilih
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printInvoiceSelected()">
                            <i class="fas fa-file-invoice"></i> Cetak Invoice
                        </button>
                    </div>
                    <div>
                        {{ $payments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Modal Konfirmasi Multi Bulan Bayar -->
<div class="modal fade" id="payMultiModal" tabindex="-1" role="dialog" aria-labelledby="payMultiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payMultiModalLabel">Konfirmasi Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="payMultiForm">
                    <div id="payMultiList"></div>
                </form>
                <div id="payMultiLoading" class="text-center" style="display:none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div>Memproses pembayaran...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="payMultiConfirmBtn">Konfirmasi Bayar</button>
            </div>
        </div>
    </div>
</div>
<script>
function showPayMultiModal(customerId) {
    $('#payMultiList').html('<div class="text-center text-muted">Memuat data tagihan...</div>');
    $('#payMultiLoading').hide();
    $('#payMultiConfirmBtn').show();
    $('#payMultiModal').modal('show');
    // Ambil data tagihan overdue & bulan berjalan via AJAX (API route)
    $.get('/api/customer/' + customerId + '/unpaid-payments', function(data) {
        if (!data || !data.length) {
            $('#payMultiList').html('<div class="alert alert-info">Tidak ada tagihan yang bisa dibayar.</div>');
            $('#payMultiConfirmBtn').hide();
            return;
        }
        let html = '<b>Pilih bulan yang ingin dibayarkan:</b><ul class="list-group mb-2">';
        data.forEach(function(payment) {
            html += '<li class="list-group-item">'
                + '<input type="checkbox" name="payment_ids[]" value="' + payment.id + '" checked> '
                + '<b>' + payment.billing_month + '</b> - ' + payment.amount_fmt
                + ' <span class="badge badge-' + (payment.is_overdue ? 'danger' : 'warning') + '">' + payment.status_label + '</span>'
                + '</li>';
        });
        html += '</ul>';
        $('#payMultiList').html(html);
    });
    // Konfirmasi bayar
    $('#payMultiConfirmBtn').off('click').on('click', function() {
        let checked = $('#payMultiForm input[name="payment_ids[]"]:checked').map(function(){ return this.value; }).get();
        if (checked.length === 0) {
            toastr.warning('Pilih minimal satu bulan yang ingin dibayar.');
            return;
        }
        $('#payMultiLoading').show();
        $('#payMultiConfirmBtn').hide();
        $.ajax({
            url: '/payments/bulk-pay',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                payment_ids: checked
            },
            success: function(response) {
                console.log('Pembayaran sukses:', response);
                $('#payMultiModal').modal('hide');
                if (typeof toastr !== 'undefined') {
                    toastr.success('Pembayaran berhasil dikonfirmasi.');
                } else {
                    alert('Pembayaran berhasil dikonfirmasi.');
                }
                setTimeout(function(){ location.reload(); }, 1000);
            },
            error: function(xhr) {
                console.log('Pembayaran gagal:', xhr);
                $('#payMultiModal').modal('hide');
                var msg = 'Terjadi kesalahan sistem: ' + (xhr.responseJSON?.message || 'Unknown error');
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
                setTimeout(function(){ location.reload(); }, 1500);
            }
        });
    });
}
// ...existing code for submitBulkPayment, printInvoiceSelected, autoSuspendOverdue, etc...

function submitBulkPayment() {
    if ($('input[name="payment_ids[]"]:checked').length === 0) {
        toastr.warning('Pilih minimal satu tagihan yang ingin dibayar.');
        return;
    }
    if (confirm('Konfirmasi pembayaran untuk tagihan yang dipilih?')) {
        $('#bulkPaymentForm').submit();
    }
}

function printInvoiceSelected() {
    var selected = $('input[name="payment_ids[]"]:checked').map(function(){ return this.value; }).get();
    if (selected.length === 0) {
        toastr.warning('Pilih minimal satu tagihan untuk cetak invoice.');
        return;
    }
    var url = '/payments/invoice?ids=' + selected.join(',');
    window.open(url, '_blank');
}

function autoSuspendOverdue() {
    if (confirm('Auto suspend semua pelanggan yang terlambat bayar lebih dari 3 hari?')) {
        $.ajax({
            url: '/payments/auto-suspend-overdue',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal suspend pelanggan');
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan sistem: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
}

// Auto refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@stop
