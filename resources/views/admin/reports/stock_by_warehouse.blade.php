@extends('adminlte::page')

@section('title', 'Laporan Stok Gudang')

@section('plugins.Datatables', true)

@section('content_header')
    {{-- Judul sekarang dinamis menampilkan nama gudang --}}
    <h1>Laporan Stok: {{ $gudang->nama_gudang }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rincian Stok Part Tersedia</h3>
        <div class="card-tools">
            {{-- Tombol Export sekarang lebih sederhana --}}
            <a href="{{ route('admin.reports.stock-by-warehouse.export', ['gudang_id' => $gudang->id]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($inventoryItems->isNotEmpty())
            <table id="stock-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode Part</th>
                        <th>Nama Part</th>
                        <th>Brand</th>
                        <th>Kategori</th>
                        <th>Rak</th>
                        <th class="text-right">Qty</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventoryItems as $item)
                        <tr>
                            <td>{{ $item->part->kode_part }}</td>
                            <td>{{ $item->part->nama_part }}</td>
                            <td>{{ $item->part->brand->nama_brand }}</td>
                            <td>{{ $item->part->category->nama_kategori }}</td>
                            <td>{{ $item->rak->kode_rak }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td>{{ $item->part->satuan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center mt-4">Tidak ada data stok untuk gudang Anda saat ini.</p>
        @endif
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#stock-table').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
        });
    });
</script>
@stop
