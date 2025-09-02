@extends('adminlte::page')

@section('title', 'Quality Control')

@section('content_header')
    <h1>Daftar Tunggu Quality Control (QC)</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Penerimaan yang Membutuhkan Pengecekan</h3>
    </div>
    <div class="card-body">
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
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Penerimaan</th>
                    <th>No. PO</th>
                    <th>Supplier</th>
                    <th>Tanggal Terima</th>
                    <th style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receivings as $receiving)
                <tr>
                    <td>{{ $receiving->nomor_penerimaan }}</td>
                    <td>{{ $receiving->purchaseOrder->nomor_po }}</td>
                    <td>{{ $receiving->purchaseOrder->supplier->nama_supplier }}</td>
                    <td>{{ \Carbon\Carbon::parse($receiving->tanggal_terima)->format('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('admin.qc.form', $receiving->id) }}" class="btn btn-primary btn-xs">Proses QC</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada barang yang menunggu proses QC.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $receivings->links() }}
    </div>
</div>
@stop
