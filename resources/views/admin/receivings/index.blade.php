@extends('adminlte::page')

@section('title', 'Penerimaan Barang')

@section('content_header')
    <h1>Penerimaan Barang</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Penerimaan</h3>
        <div class="card-tools">
            <a href="{{ route('admin.receivings.create') }}" class="btn btn-primary btn-sm">Catat Penerimaan Baru</a>
        </div>
    </div>
    <div class="card-body">
        <table id="receivings-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Penerimaan</th>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Gudang</th>
                    <th>Status</th>
                    <th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receivings as $receiving)
                <tr>
                    <td>{{ $receiving->nomor_penerimaan }}</td>
                    <td>{{ $receiving->purchaseOrder->nomor_po }}</td>
                    <td>{{ \Carbon\Carbon::parse($receiving->tanggal_terima)->format('d-m-Y') }}</td>
                    <td>{{ $receiving->gudang->nama_gudang }}</td>
                    <td>{{ $receiving->status }}</td>
                    <td>
                        <a href="{{ route('admin.receivings.show', $receiving->id) }}" class="btn btn-info btn-xs">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada data penerimaan.</td>
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
        $('#receivings-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
