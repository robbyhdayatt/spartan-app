@extends('adminlte::page')
@section('title', 'Daftar Penjualan')
@section('content_header')
    <h1>Daftar Penjualan</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Transaksi Penjualan</h3>
        <div class="card-tools">
            @can('manage-sales')
            <a href="{{ route('admin.penjualans.create') }}" class="btn btn-primary btn-sm">Buat Penjualan Baru</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="penjualans-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Tanggal</th>
                    <th>Konsumen</th>
                    <th>Sales</th>
                    <th>Total</th>
                    <th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penjualans as $penjualan)
                <tr>
                    <td>{{ $penjualan->nomor_faktur }}</td>
                    <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_jual)->format('d-m-Y') }}</td>
                    <td>{{ $penjualan->konsumen->nama_konsumen }}</td>
                    <td>{{ $penjualan->sales->nama ?? 'Tanpa Sales' }}</td>
                    <td>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('admin.penjualans.show', $penjualan->id) }}" class="btn btn-info btn-xs">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada transaksi penjualan.</td>
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
        $('#penjualans-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
