@extends('adminlte::page')
@section('title', 'Laporan Stok Keseluruhan')
@section('content_header')<h1>Laporan Stok Keseluruhan</h1>@stop
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Total Stok per Part</h3></div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead><tr><th>Kode Part</th><th>Nama Part</th><th class="text-right">Total Stok</th></tr></thead>
            <tbody>
                @foreach($stocks as $stock)
                <tr>
                    <td>{{ $stock->kode_part }}</td>
                    <td>{{ $stock->nama_part }}</td>
                    <td class="text-right font-weight-bold">{{ $stock->inventories_sum_quantity ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $stocks->links() }}</div>
</div>
@stop
