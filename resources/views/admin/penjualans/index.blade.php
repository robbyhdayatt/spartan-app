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
            <a href="{{ route('admin.penjualans.create') }}" class="btn btn-primary btn-sm">Buat Penjualan Baru</a>
        </div>
    </div>
    <div class="card-body">
         @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        <table class="table table-bordered">
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
    <div class="card-footer clearfix">
        {{ $penjualans->links() }}
    </div>
</div>
@stop
