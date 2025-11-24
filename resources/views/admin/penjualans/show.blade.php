@extends('adminlte::page')

@section('title', 'Detail Penjualan')

@section('content_header')
    <h1>Detail Faktur: {{ $penjualan->nomor_faktur }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Transaksi</h3>
        <div class="card-tools">
            <a href="{{ route('admin.penjualans.pdf', $penjualan) }}" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf"></i> Ekspor PDF</a>
            <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Nomor Faktur:</strong><br>
                {{ $penjualan->nomor_faktur }}
            </div>
            <div class="col-md-4">
                <strong>Tanggal Jual:</strong><br>
                {{ $penjualan->tanggal_jual->format('d F Y') }}
            </div>
            <div class="col-md-4">
                <strong>Sales:</strong><br>
                {{ $penjualan->sales->nama ?? 'N/A' }}
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <strong>Konsumen:</strong><br>
                {{ $penjualan->nama_konsumen }}
            </div>
            <div class="col-md-4">
                <strong>Gudang:</strong><br>
                {{ $penjualan->gudang->nama_gudang }}
            </div>
        </div>
        <hr>

        <h4>Detail Item</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode Part</th>
                        <th>Nama Part</th>
                        <th>Qty</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penjualan->details as $detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->part->kode_part }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td>{{ $detail->qty_jual }}</td>
                        <td class="text-right">@rupiah($detail->harga_jual)</td>
                        <td class="text-right">@rupiah($detail->subtotal)</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td class="text-right">@rupiah($penjualan->subtotal)</td>
                            </tr>
                            {{-- BARIS BARU UNTUK DISKON --}}
                            <tr>
                                <th>Total Diskon:</th>
                                <td class="text-right text-success">@rupiah($penjualan->total_diskon)</td>
                            </tr>
                            <tr>
                                <th>PPN (11%):</th>
                                <td class="text-right">@rupiah($penjualan->pajak)</td>
                            </tr>
                            <tr>
                                <th>Total Keseluruhan:</th>
                                <td class="text-right h4"><strong>@rupiah($penjualan->total_harga)</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
