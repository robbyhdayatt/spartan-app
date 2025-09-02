@extends('adminlte::page')

@section('title', 'Laporan Jurnal Penjualan')

@section('content_header')
    <h1>Laporan Jurnal Penjualan</h1>
@stop

@section('content')
    {{-- Form Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Berdasarkan Tanggal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.sales-journal') }}" method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                         <div class="form-group">
                            <label for="end_date">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                         <div class="form-group" style="width: 100%;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Tampilkan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Hasil --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Laporan</h3>
            <div class="card-tools">
                @if(!$salesDetails->isEmpty())
                    <a href="{{ route('admin.reports.sales-journal.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Faktur</th>
                        <th>Konsumen</th>
                        <th>Part</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesDetails as $detail)
                    <tr>
                        <td>{{ $detail->penjualan->tanggal_jual->format('d-m-Y') }}</td>
                        <td>{{ $detail->penjualan->nomor_faktur }}</td>
                        <td>{{ $detail->penjualan->konsumen->nama_konsumen }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td class="text-right">{{ $detail->qty_jual }}</td>
                        <td class="text-right">{{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data penjualan pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
