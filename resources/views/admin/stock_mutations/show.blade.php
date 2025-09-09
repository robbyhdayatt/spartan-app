@extends('adminlte::page')

@section('title', 'Detail Permintaan Mutasi')

@section('content_header')
    <h1>Detail Permintaan Mutasi #{{ $stockMutation->nomor_mutasi }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Mutasi</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Nomor Mutasi:</strong> {{ $stockMutation->nomor_mutasi }}</li>
                    <li class="list-group-item"><strong>Status:</strong>
                        @if($stockMutation->status == 'PENDING_APPROVAL') <span class="badge badge-warning">Pending Approval</span>
                        @elseif($stockMutation->status == 'APPROVED') <span class="badge badge-success">Approved</span>
                        @elseif($stockMutation->status == 'REJECTED') <span class="badge badge-danger">Rejected</span>
                        @endif
                    </li>
                    <li class="list-group-item"><strong>Part:</strong> {{ $stockMutation->part->kode_part }} - {{ $stockMutation->part->nama_part }}</li>
                    <li class="list-group-item"><strong>Jumlah:</strong> {{ $stockMutation->jumlah }} {{ $stockMutation->part->satuan }}</li>
                </ul>
            </div>
            <div class="col-md-6">
                 <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Gudang Asal:</strong> {{ $stockMutation->gudangAsal->nama_gudang }}</li>
                    <li class="list-group-item"><strong>Rak Asal:</strong> {{ $stockMutation->rakAsal->kode_rak }}</li>
                    <li class="list-group-item"><strong>Gudang Tujuan:</strong> {{ $stockMutation->gudangTujuan->nama_gudang }}</li>
                    <li class="list-group-item"><strong>Keterangan:</strong> {{ $stockMutation->keterangan ?? '-' }}</li>
                </ul>
            </div>
        </div>
         <hr>
        <div class="row">
             <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Dibuat oleh:</strong> {{ $stockMutation->createdBy->nama }}</li>
                    <li class="list-group-item"><strong>Tanggal Dibuat:</strong> {{ $stockMutation->created_at->format('d M Y, H:i') }}</li>
                </ul>
            </div>
             <div class="col-md-6">
                <ul class="list-group list-group-flush">
                     <li class="list-group-item"><strong>Disetujui/Ditolak oleh:</strong> {{ $stockMutation->approvedBy->nama ?? '-' }}</li>
                    <li class="list-group-item"><strong>Tanggal Aksi:</strong> {{ $stockMutation->approved_at ? $stockMutation->approved_at->format('d M Y, H:i') : '-' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {{-- Tombol Aksi HANYA untuk Kepala Gudang dan statusnya PENDING --}}
        @can('is-kepala-gudang')
            @if($stockMutation->status == 'PENDING_APPROVAL')
                <form action="{{ route('admin.stock-mutations.approve', $stockMutation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <form action="{{ route('admin.stock-mutations.reject', $stockMutation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
            @endif
        @endcan
        <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>
@stop
