@extends('adminlte::page')

@section('title', 'Detail Purchase Order')

@section('content_header')
    <h1>Detail Purchase Order: {{ $purchaseOrder->nomor_po }}</h1>
@stop

@section('content')
    {{-- Kotak Aksi Approval --}}
    {{-- DITAMBAHKAN: @can untuk memeriksa hak akses --}}
    @can('perform-approval', $purchaseOrder)
        @if($purchaseOrder->status === 'PENDING_APPROVAL')
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tindakan Persetujuan</h5>
                <p class="card-text">Purchase Order ini sedang menunggu persetujuan Anda.</p>
                <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <form action="{{ route('admin.purchase-orders.reject', $purchaseOrder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
            </div>
        </div>
        @endif
    @endcan
    {{-- AKHIR DARI BLOK @can --}}


     @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
     @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi PO</h3>
            <div class="card-tools">
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar PO</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nomor PO:</strong> {{ $purchaseOrder->nomor_po }}</p>
                    <p><strong>Tanggal:</strong> {{ $purchaseOrder->tanggal_po->format('d F Y') }}</p>
                    <p><strong>Status:</strong> <span class="badge badge-info">{{ str_replace('_', ' ', $purchaseOrder->status) }}</span></p>
                    <p><strong>Dibuat oleh:</strong> {{ $purchaseOrder->createdBy->nama }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Supplier:</strong> {{ $purchaseOrder->supplier->nama_supplier }}</p>
                    <p><strong>Gudang Tujuan:</strong> {{ $purchaseOrder->gudang->nama_gudang }}</p>
                    <p><strong>Disetujui oleh:</strong> {{ $purchaseOrder->approvedBy->nama ?? '-' }}</p>
                    <p><strong>Catatan:</strong> {{ $purchaseOrder->catatan ?? '-' }}</p>
                </div>
            </div>

            <h5 class="mt-4">Detail Item</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode Part</th>
                        <th>Nama Part</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga Beli</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->details as $detail)
                    <tr>
                        <td>{{ $detail->part->kode_part }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td class="text-right">{{ $detail->qty_pesan }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total Keseluruhan:</th>
                        <th class="text-right">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@stop
