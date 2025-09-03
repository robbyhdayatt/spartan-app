@extends('adminlte::page')

@section('title', 'Laporan Stok Keseluruhan')

{{-- 1. Aktifkan plugin DataTables --}}
@section('plugins.Datatables', true)

@section('content_header')
    <h1>Laporan Stok Keseluruhan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Total Stok per Part di Semua Gudang</h3>
    </div>
    <div class="card-body">
        {{-- 2. Beri ID pada tabel --}}
        <table id="stock-report-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode Part</th>
                    <th>Nama Part</th>
                    <th class="text-right">Total Stok</th>
                </tr>
            </thead>
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
    {{-- Hapus card-footer yang berisi ->links() --}}
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // 3. Inisialisasi DataTables
        $('#stock-report-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        });
    });
</script>
@stop
