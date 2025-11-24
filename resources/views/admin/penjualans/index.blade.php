@extends('adminlte::page')
@section('title', 'Daftar Penjualan')
@section('content_header')
    <h1>Daftar Penjualan</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.penjualans.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Penjualan Baru</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="table-penjualan">
            <thead>
                <tr>
                    <th>No Faktur</th>
                    <th>Tanggal</th>
                    <th>Konsumen</th> {{-- Judul kolom tetap Konsumen --}}
                    <th>Total Harga</th>
                    <th>Sales/Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualans as $p)
                <tr>
                    <td>{{ $p->nomor_faktur }}</td>
                    <td>{{ $p->tanggal_jual->format('d-m-Y') }}</td>
                    
                    {{-- MODIFIKASI: Panggil kolom string langsung --}}
                    <td>{{ $p->nama_konsumen }}</td>
                    
                    <td>Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                    <td>{{ $p->sales->nama ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.penjualans.show', $p->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop