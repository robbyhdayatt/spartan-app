@extends('adminlte::page')

@section('title', 'Laporan Stok Rinci')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Laporan Stok Rinci</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rincian Stok Part di Semua Gudang dan Rak</h3>
    </div>
    <div class="card-body">
        <table id="stock-report-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode Part</th>
                    <th>Nama Part</th>
                    <th>Gudang</th>
                    <th>Rak</th>
                    <th class="text-right">Stok Tersedia</th>
                </tr>
            </thead>
            <tbody>
                {{-- Kita ganti variabelnya menjadi $inventoryDetails --}}
                @foreach($inventoryDetails as $item)
                <tr>
                    <td>{{ $item->part->kode_part ?? 'N/A' }}</td>
                    <td>{{ $item->part->nama_part ?? 'N/A' }}</td>
                    <td>{{ $item->gudang->nama_gudang ?? 'N/A' }}</td>
                    <td>{{ $item->rak->kode_rak ?? 'N/A' }}</td>
                    <td class="text-right font-weight-bold">{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#stock-report-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        });
    });
</script>
@stop
