@extends('adminlte::page')
@section('title', 'Retur Penjualan')
@section('content_header')<h1>Retur Penjualan</h1>@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Retur dari Konsumen</h3>
        <div class="card-tools"><a href="{{ route('admin.sales-returns.create') }}" class="btn btn-primary btn-sm">Buat Retur Baru</a></div>
    </div>
    <div class="card-body">
        <table id="sales_returns-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Retur</th><th>Tgl. Retur</th><th>No. Faktur Asli</th><th>Konsumen</th><th>Total Retur</th><th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                <tr>
                    <td>{{ $return->nomor_retur_jual }}</td>
                    <td>{{ $return->tanggal_retur->format('d-m-Y') }}</td>
                    <td>{{ $return->penjualan->nomor_faktur }}</td>
                    <td>{{ $return->konsumen->nama_konsumen }}</td>
                    <td>Rp {{ number_format($return->total_retur, 0, ',', '.') }}</td>
                    <td><a href="{{ route('admin.sales-returns.show', $return->id) }}" class="btn btn-info btn-xs">Lihat</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">Belum ada data retur penjualan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#sales_returns-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
