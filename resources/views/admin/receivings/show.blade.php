@extends('adminlte::page')

@section('title', 'Detail Penerimaan Barang')

@section('content_header')
    <h1>Detail Penerimaan: {{ $receiving->nomor_penerimaan }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Penerimaan</h3>
        <div class="card-tools">
            <a href="{{ route('admin.receivings.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>No. Penerimaan:</strong> {{ $receiving->nomor_penerimaan }}</p>
                <p><strong>No. PO Terkait:</strong> <a href="{{ route('admin.purchase-orders.show', $receiving->purchase_order_id) }}">{{ $receiving->purchaseOrder->nomor_po }}</a></p>
                <p><strong>Tanggal Diterima:</strong> {{ \Carbon\Carbon::parse($receiving->tanggal_terima)->format('d F Y') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> {{ $receiving->status }}</p>
                <p><strong>Gudang:</strong> {{ $receiving->gudang->nama_gudang }}</p>
                <p><strong>Diterima oleh:</strong> {{ $receiving->receivedBy->nama }}</p>
            </div>
        </div>

        <h5 class="mt-4">Detail Item Diterima</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Part</th>
                    <th>Nama Part</th>
                    <th class="text-right">Qty Diterima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiving->details as $detail)
                <tr>
                    <td>{{ $detail->part->kode_part }}</td>
                    <td>{{ $detail->part->nama_part }}</td>
                    <td class="text-right">{{ $detail->qty_terima }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
