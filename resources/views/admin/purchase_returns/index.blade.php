@extends('adminlte::page')
@section('title', 'Retur Pembelian')
@section('content_header')
    <h1>Retur Pembelian</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Retur ke Supplier</h3>
        <div class="card-tools">
            <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-primary btn-sm">Buat Retur Baru</a>
        </div>
    </div>
    <div class="card-body">
        <table id="purchase_returns-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Retur</th>
                    <th>Tgl. Retur</th>
                    <th>No. Penerimaan</th>
                    <th>Supplier</th>
                    <th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                <tr>
                    <td>{{ $return->nomor_retur }}</td>
                    <td>{{ $return->tanggal_retur->format('d-m-Y') }}</td>
                    <td>{{ $return->receiving->nomor_penerimaan }}</td>
                    <td>{{ $return->supplier->nama_supplier }}</td>
                    <td>
                        <a href="{{ route('admin.purchase-returns.show', $return->id) }}" class="btn btn-info btn-xs">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data retur pembelian.</td>
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
        $('#purchase_returns-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
