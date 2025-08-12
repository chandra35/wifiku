@extends('adminlte::page')

@section('title', 'Pengaturan Aplikasi')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-sliders-h mr-2"></i>Pengaturan Aplikasi</h1>
            <p class="text-muted">Atur prefix ID pelanggan, PPN, dan catatan invoice sesuai kebutuhan bisnis Anda</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan Aplikasi</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fas fa-check mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
@endif

<div class="row">
    <!-- Left col (Info Card) -->
    <div class="col-md-3">
        <!-- Settings Info -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; margin: 0 auto;">
                        <i class="fas fa-cogs fa-2x"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center">Pengaturan Aplikasi</h3>

                <p class="text-muted text-center">
                    <span class="badge badge-primary">System Configuration</span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Prefix ID</b> <a class="float-right text-primary">{{ $prefix }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>PPN Rate</b> <a class="float-right text-success">{{ $ppn }}%</a>
                    </li>
                    <li class="list-group-item">
                        <b>Invoice Note</b> 
                        <span class="float-right">
                            <span class="badge badge-{{ $invoice ? 'success' : 'secondary' }}">
                                {{ $invoice ? 'Set' : 'Empty' }}
                            </span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Last Updated</b> <a class="float-right">{{ now()->format('M Y') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right col (Main Content) -->
    <div class="col-md-9">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-2"></i>Form Pengaturan Aplikasi
                </h3>
            </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_prefix" class="form-label">
                                        <i class="fas fa-hashtag mr-1"></i>Prefix ID Pelanggan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="customer_prefix" id="customer_prefix" class="form-control" 
                                           value="{{ old('customer_prefix', $prefix) }}" maxlength="10" required
                                           placeholder="Contoh: CUS">
                                    <small class="form-text text-muted">Prefix untuk nomor ID pelanggan otomatis</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ppn" class="form-label">
                                        <i class="fas fa-percentage mr-1"></i>PPN (%)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="ppn" id="ppn" class="form-control" 
                                           value="{{ old('ppn', $ppn) }}" min="0" max="100" required
                                           placeholder="Contoh: 11">
                                    <small class="form-text text-muted">Persentase pajak yang dikenakan</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="invoice_note" class="form-label">
                                <i class="fas fa-sticky-note mr-1"></i>Catatan Invoice
                            </label>
                            <textarea name="invoice_note" id="invoice_note" class="form-control" maxlength="255" rows="3"
                                      placeholder="Catatan tambahan yang akan muncul di invoice...">{{ old('invoice_note', $invoice) }}</textarea>
                            <small class="form-text text-muted">Catatan tambahan pada footer invoice (opsional)</small>
                        </div>
                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
