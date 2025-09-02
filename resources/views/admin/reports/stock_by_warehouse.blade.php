@extends('adminlte::page')
@section('title', 'Laporan Stok per Gudang')
@section('content_header')<h1>Laporan Stok per Gudang</h1>@stop
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Pilih Gudang</h3></div>
    <div class="card-body">
        <form action="{{ route('admin.reports.stock-by-warehouse') }}" method="GET">
            <div class="form-group row">
                <label for="gudang_id" class="col-sm-2 col-form-label">Gudang</label>
                <div class="col-sm-8">
                    <select name="gudang_id" id="gudang_id" class="form-control select2" required style="width: 100%;">
                        <option></option>
                        @foreach($gudangs as $gudang)
                            <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                {{ $gudang->nama_gudang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
            </div>
        </form>
    </div>
</div>
@if(request()->filled('gudang_id'))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Stok untuk Gudang: <strong>{{ $gudangs->find(request('gudang_id'))->nama_gudang }}</strong></h3>
        <div class="card-tools">
            <a href="{{ route('admin.reports.stock-by-warehouse.export', ['gudang_id' => request('gudang_id')]) }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Export to Excel</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode Part</th><th>Nama Part</th><th>Kode Rak</th><th>Nama Rak</th><th class="text-right">Jumlah Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryItems as $item)
                <tr>
                    <td>{{ $item->part->kode_part }}</td><td>{{ $item->part->nama_part }}</td><td>{{ $item->rak->kode_rak }}</td><td>{{ $item->rak->nama_rak }}</td><td class="text-right font-weight-bold">{{ $item->quantity }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Tidak ada stok yang tercatat di gudang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@stop

@section('js')
<script> $(document).ready(function() { $('#gudang_id').select2({ placeholder: "--- Pilih Gudang ---" }); }); </script>
@stop
