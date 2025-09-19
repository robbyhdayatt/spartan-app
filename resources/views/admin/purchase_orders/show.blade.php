@extends('adminlte::page')

@section('title', 'Detail Purchase Order')

@section('content_header')
    <h1>Detail Purchase Order: {{ $purchaseOrder->nomor_po }}</h1>
@stop

@section('content')
<div class="invoice p-3 mb-3">
    {{-- Baris Info Utama --}}
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-globe"></i> SpartanApp
                <small class="float-right">Tanggal: {{ $purchaseOrder->tanggal_po->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Dari
            <address>
                <strong>PT. Lautan Teduh Interniaga</strong><br>
                Jl. Ikan Tenggiri, Pesawahan<br>
                Bandar Lampung, Indonesia<br>
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
            <b>Dibuat Oleh:</b> {{ $purchaseOrder->createdBy->nama ?? 'Tidak Ditemukan' }}
        </div>
    </div>

    {{-- Tampilkan Alasan Penolakan JIKA ADA --}}
    @if($purchaseOrder->status === 'REJECTED' && $purchaseOrder->rejection_reason)
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Alasan Penolakan</h5>
                {{ $purchaseOrder->rejection_reason }}
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel Item --}}
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

    {{-- Baris Total dan Catatan --}}
    <div class="row">
        <div class="col-6">
            <p class="lead">Catatan Pembuat PO:</p>
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

    {{-- Baris Tombol Aksi --}}
    <div class="row no-print">
        <div class="col-12">
            {{-- Tombol Approve dan Reject --}}
            @if($purchaseOrder->status === 'PENDING_APPROVAL')
                @can('approve-po', $purchaseOrder)
                <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success float-right"><i class="far fa-credit-card"></i> Setujui</button>
                </form>
                <button type="button" class="btn btn-danger float-right" style="margin-right: 5px;" data-toggle="modal" data-target="#rejectModal">
                    <i class="fas fa-times"></i> Tolak
                </button>
                @endcan
            @endif

            {{-- !! TAMBAHKAN TOMBOL INI !! --}}
            <a href="{{ route('admin.purchase-orders.print', $purchaseOrder) }}" rel="noopener" target="_blank" class="btn btn-info"><i class="fas fa-print"></i> Cetak PO</a>

            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
</div>

{{-- MODAL UNTUK ALASAN PENOLAKAN --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.purchase-orders.reject', $purchaseOrder) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan PO</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="rejection_reason">Mohon berikan alasan mengapa PO ini ditolak:</label>
            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Submit Penolakan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop
