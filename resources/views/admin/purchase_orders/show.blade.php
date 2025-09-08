@extends('adminlte::page')

@section('title', 'Detail Purchase Order')

@section('content_header')
    <h1>Detail Purchase Order: {{ $purchaseOrder->nomor_po }}</h1>
@stop

@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-globe"></i> SpartanApp
                <small class="float-right">Tanggal: {{ \Carbon\Carbon::parse($purchaseOrder->tanggal_po)->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari
            <address>
                {{-- GANTI DENGAN INFO PERUSAHAAN ANDA --}}
                <strong>PT. Lautan Teduh Interniaga</strong><br>
                Jl. Ikan Tenggiri, Pesawahan, Kec. Telukbetung Selatan<br>
                Bandar Lampung, Indonesia<br>
                Phone: 0812-2000-4367<br>
                Website: www.yamaha-lampung.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            Kepada
            <address>
                <strong>{{ $purchaseOrder->supplier->nama_supplier }}</strong><br>
                {{ $purchaseOrder->supplier->alamat }}<br>
                Phone: {{ $purchaseOrder->supplier->telepon }}<br>
                Email: {{ $purchaseOrder->supplier->email }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Nomor PO:</b> {{ $purchaseOrder->nomor_po }}<br>
            <b>Tujuan Gudang:</b> {{ $purchaseOrder->gudang->nama_gudang }}<br>
            <b>Status:</b> <span class="badge {{ $purchaseOrder->status_class }}">{{ $purchaseOrder->status_badge }}</span><br>
            {{-- PERBAIKAN FINAL DI SINI --}}
            <b>Dibuat Oleh:</b> {{ $purchaseOrder->createdBy->nama ?? 'Tidak Ditemukan' }}
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
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->details as $detail)
                    <tr>
                        <td>{{ $detail->qty_pesan }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td>{{ $detail->part->kode_part }}</td>
                        <td>{{ 'Rp ' . number_format($detail->harga_beli, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <p class="lead">Catatan:</p>
            <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                {{ $purchaseOrder->catatan ?? 'Tidak ada catatan.' }}
            </p>
        </div>
        <div class="col-6">
            <p class="lead">Detail Pembayaran</p>

            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Subtotal:</th>
                        <td>{{ 'Rp ' . number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>PPN (11%):</th>
                        <td>{{ 'Rp ' . number_format($purchaseOrder->pajak, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Grand Total:</th>
                        <td><strong>{{ 'Rp ' . number_format($purchaseOrder->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="row no-print">
        <div class="col-12">
            @if($purchaseOrder->status === 'PENDING_APPROVAL')
                 @can('approve-po', $purchaseOrder)
                <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success float-right"><i class="far fa-credit-card"></i> Setujui</button>
                </form>
                <form action="{{ route('admin.purchase-orders.reject', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger float-right" style="margin-right: 5px;">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                </form>
                 @endcan
            @endif
             <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@stop
