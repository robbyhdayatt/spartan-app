@extends('adminlte::page')

@section('title', 'Detail Penjualan')

@section('content_header')
    <h1>Detail Penjualan: {{ $penjualan->nomor_faktur }}</h1>
@stop

@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-file-invoice"></i> SpartanApp
                <small class="float-right">Tanggal Jual: {{ \Carbon\Carbon::parse($penjualan->tanggal_jual)->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari
            <address>
                <strong>SpartanApp, Inc.</strong><br>
                Gudang: {{ $penjualan->gudang->nama_gudang }}<br>
                Alamat Gudang: {{ $penjualan->gudang->alamat_gudang }}<br>
                Phone: (xxx) xxx-xxxx<br>
                Email: info@spartan.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            Kepada
            <address>
                <strong>{{ $penjualan->konsumen->nama_konsumen }}</strong><br>
                {{ $penjualan->konsumen->alamat }}<br>
                Phone: {{ $penjualan->konsumen->telepon }}<br>
                Email: {{ $penjualan->konsumen->email }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Nomor Faktur:</b> {{ $penjualan->nomor_faktur }}<br>
            <b>Sales:</b> {{ $penjualan->sales->name ?? 'N/A' }}<br>
            <b>Tanggal Dibuat:</b> {{ $penjualan->created_at->format('d/m/Y H:i') }}
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Part</th>
                        <th>Kode Part</th>
                        <th>Rak</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penjualan->details as $detail)
                    <tr>
                        <td>{{ $detail->qty_jual }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td>{{ $detail->part->kode_part }}</td>
                        <td>{{ $detail->rak->kode_rak }}</td>
                        <td>{{ 'Rp ' . number_format($detail->harga_jual, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            {{-- Bisa diisi dengan metode pembayaran atau catatan lain --}}
        </div>
        <div class="col-6">
            <p class="lead">Detail Pembayaran</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Subtotal:</th>
                        <td>{{ 'Rp ' . number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>PPN (11%):</th>
                        <td>{{ 'Rp ' . number_format($penjualan->pajak, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Grand Total:</th>
                        <td><strong>{{ 'Rp ' . number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary">Kembali</a>
            {{-- <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
                <i class="fas fa-download"></i> Generate PDF
            </button> --}}
        </div>
    </div>
</div>
@stop
