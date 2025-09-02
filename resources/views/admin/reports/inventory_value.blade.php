@extends('adminlte::page')

@section('title', 'Laporan Nilai Persediaan')

@section('content_header')
    <h1>Laporan Nilai Persediaan</h1>
@stop

@section('content')
    {{-- Info Box untuk Total Nilai --}}
    <div class="row">
        <div class="col-12">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Nilai Persediaan Saat Ini</span>
                    <span class="info-box-number"><h2>Rp {{ number_format($totalValue, 0, ',', '.') }}</h2></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Rincian --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rincian Nilai Persediaan</h3>
            <div class="card-tools">
                <a href="{{ route('admin.reports.inventory-value.export') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Gudang</th>
                        <th>Part</th>
                        <th>Rak</th>
                        <th class="text-right">Stok</th>
                        <th class="text-right">Harga Beli Satuan</th>
                        <th class="text-right">Subtotal Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryDetails as $item)
                    <tr>
                        <td>{{ $item->gudang->nama_gudang }}</td>
                        <td>{{ $item->part->nama_part }} ({{ $item->part->kode_part }})</td>
                        <td>{{ $item->rak->nama_rak }} ({{ $item->rak->kode_rak }})</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->part->harga_beli_default, 0, ',', '.') }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($item->quantity * $item->part->harga_beli_default, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada stok di dalam inventaris.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
