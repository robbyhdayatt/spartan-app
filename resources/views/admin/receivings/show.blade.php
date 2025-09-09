@extends('adminlte::page')

@section('title', 'Detail Penerimaan Barang')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Detail Penerimaan</h1>
            <small class="text-muted">Dokumen: {{ $receiving->nomor_penerimaan }}</small>
        </div>
        {{-- Tombol Aksi yang tidak relevan sudah dihapus --}}
        <div>
             <a href="{{ route('admin.receivings.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')

{{-- 1. Progress Timeline --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Step 1: Receiving --}}
                    <div class="col-4 text-center">
                        <div class="text-primary">
                            <i class="fas fa-box-open fa-2x"></i>
                            <h5 class="mt-2 mb-1">Diterima</h5>
                            <p class="mb-0 text-muted small">{{ optional($receiving->createdBy)->name ?? 'N/A' }}</p>
                            <p class="mb-0 text-muted small">{{ \Carbon\Carbon::parse($receiving->tanggal_terima)->isoFormat('D MMM Y, HH:mm') }}</p>
                        </div>
                    </div>
                    {{-- Step 2: Quality Control --}}
                    <div class="col-4 text-center">
                        @if(in_array($receiving->status, ['PENDING_PUTAWAY', 'COMPLETED']))
                             <div class="text-success">
                                <i class="fas fa-clipboard-check fa-2x"></i>
                                <h5 class="mt-2 mb-1">Lolos Quality Control</h5>
                                <p class="mb-0 text-muted small">Menunggu penyimpanan</p>
                             </div>
                        @else
                            <div class="text-muted">
                                <i class="fas fa-clipboard-check fa-2x"></i>
                                <h5 class="mt-2 mb-1">Quality Control</h5>
                                <p class="mb-0 text-muted small">Menunggu diproses</p>
                            </div>
                        @endif
                    </div>
                    {{-- Step 3: Putaway / Selesai --}}
                    <div class="col-4 text-center">
                         @if($receiving->status === 'COMPLETED')
                             <div class="text-success">
                                <i class="fas fa-warehouse fa-2x"></i>
                                <h5 class="mt-2 mb-1">Selesai Disimpan</h5>
                                <p class="mb-0 text-muted small">Stok sudah masuk inventaris</p>
                             </div>
                        @else
                            <div class="text-muted">
                                <i class="fas fa-warehouse fa-2x"></i>
                                <h5 class="mt-2 mb-1">Disimpan ke Rak</h5>
                                <p class="mb-0 text-muted small">Menunggu diproses</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    {{-- 2. Detail Dokumen --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Dokumen</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Purchase Order (PO)</dt>
                    <dd><a href="{{ route('admin.purchase-orders.show', $receiving->purchase_order_id) }}">{{ $receiving->purchaseOrder->nomor_po }}</a></dd>

                    <dt>Supplier</dt>
                    <dd>{{ $receiving->purchaseOrder->supplier->nama_supplier }}</dd>

                    <dt>Status Saat Ini</dt>
                    <dd>
                        @if($receiving->status === 'COMPLETED')
                            <span class="badge badge-success">Selesai</span>
                        @elseif($receiving->status === 'PENDING_QC')
                            <span class="badge badge-warning">Menunggu QC</span>
                        @elseif($receiving->status === 'PENDING_PUTAWAY')
                             <span class="badge badge-info">Menunggu Putaway</span>
                        @else
                            <span class="badge badge-secondary">{{ $receiving->status }}</span>
                        @endif
                    </dd>

                    @if($receiving->catatan)
                        <dt>Catatan</dt>
                        <dd>{{ $receiving->catatan }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- 3. Rincian Item --}}
    <div class="col-md-7">
         <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rincian Item</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th class="text-center">Diterima</th>
                            <th class="text-center">Lolos QC</th>
                            <th class="text-center">Gagal QC</th>
                            <th>Lokasi Simpan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receiving->details as $detail)
                            <tr>
                                <td>
                                    <strong>{{ $detail->part->nama_part }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $detail->part->kode_part }}</small>
                                </td>
                                <td class="text-center">{{ $detail->qty_terima }}</td>
                                <td class="text-center text-success font-weight-bold">{{ $detail->qty_lolos_qc ?? 0 }}</td>
                                <td class="text-center text-danger font-weight-bold">{{ $detail->qty_gagal_qc ?? 0 }}</td>
                                <td>
                                    @php
                                        $putawayInfo = $stockMovements
                                            ->where('part_id', $detail->part_id)
                                            ->where('quantity', '>', 0);
                                    @endphp

                                    @if($putawayInfo->isNotEmpty())
                                        @foreach($putawayInfo as $putaway)
                                            <span class="badge badge-primary">{{ optional($putaway->rak)->kode_rak ?? 'N/A' }}</span>
                                            <small>(Qty: {{ $putaway->quantity }})</small><br>
                                        @endforeach
                                    @else
                                        <span class="badge badge-secondary">Belum Disimpan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada item detail.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- Tambahkan penutup untuk @section('content') yang hilang --}}
@stop
