@extends('adminlte::page')

@section('title', 'Dashboard Admin Gudang')

@section('content_header')
    <h1>Operasional Gudang</h1>
@stop

@section('content')
    {{-- Widget Operasional Cepat --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('admin.penjualans.create') }}">
                <div class="info-box shadow-sm bg-danger">
                    <span class="info-box-icon"><i class="fas fa-cash-register"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Penjualan Baru</span>
                        <span class="info-box-number">Input Manual</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('admin.receivings.index') }}">
                <div class="info-box shadow-sm bg-info">
                    <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Penerimaan</span>
                        <span class="info-box-number">Inbound</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('admin.qc.index') }}">
                <div class="info-box shadow-sm bg-warning">
                    <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Quality Control</span>
                        <span class="info-box-number">Cek Barang</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <a href="{{ route('admin.putaway.index') }}">
                <div class="info-box shadow-sm bg-success">
                    <span class="info-box-icon"><i class="fas fa-people-carry"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Putaway</span>
                        <span class="info-box-number">Simpan Rak</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Tabel Stok Kritis --}}
        <div class="col-md-12">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Peringatan Stok Minimum (Segera Order!)
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($lowStockParts->isEmpty())
                        <div class="p-4 text-center text-success">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h5>Stok Aman! Tidak ada part di bawah batas minimum.</h5>
                        </div>
                    @else
                        <table class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th>Kode Part</th>
                                    <th>Nama Part</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Minimum Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockParts as $part)
                                <tr>
                                    <td>{{ $part->kode_part }}</td>
                                    <td>{{ $part->nama_part }}</td>
                                    <td class="font-weight-bold text-danger">{{ $part->current_stock }}</td>
                                    <td>{{ $part->stok_minimum }}</td>
                                    <td>
                                        <span class="badge badge-danger">CRITICAL</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reports.rekomendasi-po') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-shopping-cart"></i> Buat PO
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop