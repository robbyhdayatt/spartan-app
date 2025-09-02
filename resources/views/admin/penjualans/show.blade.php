@extends('adminlte::page')
@section('title', 'Detail Penjualan')
@section('content_header')
    <h1>Faktur Penjualan: {{ $penjualan->nomor_faktur }}</h1>
@stop
@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-globe"></i> SPARTAN, Inc.
                <small class="float-right">Tanggal: {{ $penjualan->tanggal_jual->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari
            <address>
                <strong>{{ $penjualan->gudang->nama_gudang }}</strong><br>
                Sales: {{ $penjualan->sales->nama ?? 'Tanpa Sales' }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            Kepada
            <address>
                <strong>{{ $penjualan->konsumen->nama_konsumen }}</strong><br>
                {{ $penjualan->konsumen->alamat }}<br>
                Telepon: {{ $penjualan->konsumen->telepon }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Faktur #{{ $penjualan->nomor_faktur }}</b><br>
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
                        <th>Diambil dari Rak</th>
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
                        <td>{{ $detail->rak->nama_rak }}</td>
                        <td>Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-6"></div>
        <div class="col-6">
            <p class="lead">Jumlah Tagihan</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Total:</th>
                        <td><strong>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-12">
            <a href="#" onclick="window.print();" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
             <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary float-right">Kembali</a>
        </div>
    </div>
</div>
@stop
