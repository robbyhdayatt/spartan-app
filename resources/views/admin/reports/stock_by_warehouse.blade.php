@extends('adminlte::page')

@section('title', 'Laporan Stok per Gudang')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Laporan Stok per Gudang</h1>
@stop

@section('content')
@php
    $isKepalaGudang = Auth::user()->jabatan->nama_jabatan === 'Kepala Gudang';
@endphp

<div class="card">
    {{-- Form Filter hanya ditampilkan jika BUKAN Kepala Gudang --}}
    @unless($isKepalaGudang)
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.stock-by-warehouse') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gudang_id">Pilih Gudang</label>
                        <select name="gudang_id" id="gudang_id" class="form-control select2" required>
                            <option value="">-- Pilih Gudang --</option>
                            @foreach ($gudangs as $gudang)
                                <option value="{{ $gudang->id }}" {{ optional($selectedGudang)->id == $gudang->id ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endunless
</div>

{{-- Tabel Hasil akan ditampilkan jika ada data atau jika login sebagai Kepala Gudang --}}
@if($inventoryItems->isNotEmpty() || $isKepalaGudang)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Rincian Stok Part Tersedia
            @if($selectedGudang)
                di {{ $selectedGudang->nama_gudang }}
            @endif
        </h3>
        @if($selectedGudang)
        <div class="card-tools">
            <a href="{{ route('admin.reports.stock-by-warehouse.export', ['gudang_id' => $selectedGudang->id]) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
        @endif
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
            <p class="text-center mt-4">Tidak ada data stok untuk gudang ini.</p>
        @endif
    </div>
</div>
@endif

@stop

@push('css')
<style>
    /* Menyesuaikan tinggi Select2 agar sama dengan input form lainnya */
    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding-left: .75rem !important;
        padding-top: .375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@endpush

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('#stock-table').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
        });
    });
</script>
@stop
