@extends('adminlte::page')

@section('title', 'Detail Penerimaan')

@section('content_header')
    <h1>Detail Penerimaan: {{ $receiving->nomor_penerimaan }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Dokumen</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.receivings.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nomor PO:</strong><br>
                            <a
                                href="{{ route('admin.purchase-orders.show', $receiving->purchase_order_id) }}">{{ $receiving->purchaseOrder->nomor_po }}</a>
                        </div>
                        <div class="col-md-4">
                            <strong>Supplier:</strong><br>
                            {{ $receiving->purchaseOrder->supplier->nama_supplier }}
                        </div>
                        <div class="col-md-4">
                            <strong>Gudang:</strong><br>
                            {{ $receiving->gudang->nama_gudang }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Catatan Penerimaan:</strong><br>
                            {{ $receiving->catatan ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Penyimpanan (Putaway)</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Part</th>
                                    <th>Jumlah Disimpan</th>
                                    <th>Rak Tujuan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockMovements as $movement)
                                    <tr>
                                        <td>{{ $movement->part->nama_part }}</td>
                                        <td>{{ $movement->jumlah }}</td>
                                        <td>{{ $movement->rak->kode_rak ?? 'N/A' }}</td>
                                        <td>{{ $movement->user->nama ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada barang yang disimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Proses</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Diterima oleh:</strong><br>
                                <small>{{ $receiving->receivedBy->nama ?? 'N/A' }}</small>
                            </div>
                            <span class="badge badge-primary">{{ $receiving->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Diperiksa oleh (QC):</strong><br>
                                <small>{{ $receiving->qcBy->nama ?? 'Menunggu Proses' }}</small>
                            </div>
                            @if ($receiving->qc_at)
                                <span class="badge badge-info">{{ $receiving->qc_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Disimpan oleh (Putaway):</strong><br>
                                <small>{{ $receiving->putawayBy->nama ?? 'Menunggu Proses' }}</small>
                            </div>
                            @if ($receiving->putaway_at)
                                <span class="badge badge-success">{{ $receiving->putaway_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
