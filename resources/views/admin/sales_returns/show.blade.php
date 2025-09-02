@extends('adminlte::page')
@section('title', 'Detail Retur Penjualan')
@section('content_header')<h1>Detail Retur: {{ $salesReturn->nomor_retur_jual }}</h1>@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Retur</h3>
        <div class="card-tools"><a href="{{ route('admin.sales-returns.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a></div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>No. Retur:</strong> {{ $salesReturn->nomor_retur_jual }}</p>
                <p><strong>Tgl. Retur:</strong> {{ $salesReturn->tanggal_retur->format('d F Y') }}</p>
                <p><strong>Konsumen:</strong> {{ $salesReturn->konsumen->nama_konsumen }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Referensi Faktur:</strong> <a href="{{ route('admin.penjualans.show', $salesReturn->penjualan_id) }}">{{ $salesReturn->penjualan->nomor_faktur }}</a></p>
                <p><strong>Gudang Penerima:</strong> {{ $salesReturn->gudang->nama_gudang }}</p>
                <p><strong>Dibuat oleh:</strong> {{ $salesReturn->createdBy->nama }}</p>
            </div>
        </div>
        <h5 class="mt-4">Detail Item Diretur</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Part</th><th>Nama Part</th><th class="text-right">Qty Diretur</th><th class="text-right">Harga Saat Jual</th><th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesReturn->details as $detail)
                <tr>
                    <td>{{ $detail->part->kode_part }}</td><td>{{ $detail->part->nama_part }}</td><td class="text-right">{{ $detail->qty_retur }}</td><td class="text-right">{{ number_format($detail->harga_saat_jual, 0, ',', '.') }}</td><td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="4" class="text-right">Total Nilai Retur:</th><th class="text-right">Rp {{ number_format($salesReturn->total_retur, 0, ',', '.') }}</th></tr>
            </tfoot>
        </table>
    </div>
</div>
@stop
