@extends('adminlte::page')

@section('title', 'Putaway / Penyimpanan')

@section('content_header')
    <h1>Daftar Tunggu Penyimpanan (Putaway)</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Barang Lolos QC yang Siap Disimpan</h3>
    </div>
    <div class="card-body">
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        <table id="putaway-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Penerimaan</th>
                    <th>No. PO</th>
                    <th>Supplier</th>
                    <th>Gudang Tujuan</th>
                    <th style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receivings as $receiving)
                <tr>
                    <td>{{ $receiving->nomor_penerimaan }}</td>
                    <td>{{ $receiving->purchaseOrder->nomor_po }}</td>
                    <td>{{ $receiving->purchaseOrder->supplier->nama_supplier }}</td>
                    <td>{{ $receiving->gudang->nama_gudang }}</td>
                    <td>
                        <a href="{{ route('admin.putaway.form', $receiving->id) }}" class="btn btn-primary btn-xs">Proses Penyimpanan</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada barang yang menunggu untuk disimpan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#putaway-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
