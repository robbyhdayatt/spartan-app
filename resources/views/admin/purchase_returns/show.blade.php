@extends('adminlte::page')
@section('title', 'Detail Retur Pembelian')
@section('content_header')
    <h1>Detail Retur: {{ $purchaseReturn->nomor_retur }}</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Retur</h3>
        <div class="card-tools">
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>No. Retur:</strong> {{ $purchaseReturn->nomor_retur }}</p>
                <p><strong>Tgl. Retur:</strong> {{ $purchaseReturn->tanggal_retur->format('d F Y') }}</p>
                <p><strong>Dibuat oleh:</strong> {{ $purchaseReturn->createdBy->nama }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Supplier:</strong> {{ $purchaseReturn->supplier->nama_supplier }}</p>
                <p><strong>Referensi Penerimaan:</strong> <a href="{{ route('admin.receivings.show', $purchaseReturn->receiving_id) }}">{{ $purchaseReturn->receiving->nomor_penerimaan }}</a></p>
                <p><strong>Catatan:</strong> {{ $purchaseReturn->catatan ?? '-' }}</p>
            </div>
        </div>

        <h5 class="mt-4">Detail Item Diretur</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Part</th>
                    <th>Nama Part</th>
                    <th class="text-right">Qty Diretur</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseReturn->details as $detail)
                <tr>
                    <td>{{ $detail->part->kode_part }}</td>
                    <td>{{ $detail->part->nama_part }}</td>
                    <td class="text-right">{{ $detail->qty_retur }}</td>
                    <td>{{ $detail->alasan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
