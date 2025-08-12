@extends('adminlte::page')

@section('title', 'Invoice Pembayaran')

@section('content_header')
    <h1>
        Invoice Pembayaran
        <small>{{ $customer->name }} ({{ $customer->customer_id }})</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice"></i> Invoice Pembayaran
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Nama Pelanggan:</strong> {{ $customer->name }}<br>
                    <strong>ID Pelanggan:</strong> {{ $customer->customer_id }}<br>
                    <strong>Paket:</strong> {{ $customer->package->name ?? '-' }}<br>
                    <strong>Alamat:</strong> {{ $customer->getFullAddress() }}<br>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $i => $payment)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $payment->billing_date->format('M Y') }}</td>
                            <td>{{ $payment->due_date->format('d/m/Y') }}</td>
                            <td>{{ $payment->getFormattedAmount() }}</td>
                            <td><span class="badge {{ $payment->getStatusBadgeClass() }}">{{ $payment->getStatusLabel() }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total</th>
                            <th colspan="2">Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-4">
                    <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}<br>
                    <strong>Petugas:</strong> {{ auth()->user()->name ?? '-' }}
                </div>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
@media print {
    .main-footer, .main-header, .btn, .card-header, .card-footer { display: none !important; }
    .card { box-shadow: none !important; }
}
</style>
@stop
