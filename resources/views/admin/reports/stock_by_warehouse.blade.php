@extends('adminlte::page')

@section('title', 'Laporan Stok per Gudang')

@section('content_header')
    <h1>Laporan Stok per Gudang</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.stock-by-warehouse') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gudang_id">Pilih Gudang</label>
                        {{-- **LOGIKA BARU DI TAMPILAN** --}}
                        {{-- Jika hanya ada 1 gudang (untuk Kepala Gudang), tampilkan sebagai teks biasa --}}
                        @if(count($gudangs) === 1)
                            <input type="text" class="form-control" value="{{ $gudangs->first()->nama_gudang }}" readonly>
                            <input type="hidden" name="gudang_id" value="{{ $gudangs->first()->id }}">
                        @else
                        {{-- Jika lebih dari 1, tampilkan sebagai dropdown --}}
                        <select name="gudang_id" id="gudang_id" class="form-control select2" required>
                            <option value="">-- Semua Gudang --</option>
                            @foreach ($gudangs as $gudang)
                                <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                    {{ $gudang->nama_gudang }}
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">Tampilkan</button>
                    </div>
                </div>
                {{-- Tombol Export hanya muncul jika ada gudang yang dipilih --}}
                @if(request('gudang_id'))
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <a href="{{ route('admin.reports.stock-by-warehouse.export', ['gudang_id' => request('gudang_id')]) }}" class="btn btn-success form-control">
                            <i class="fa fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </form>

        @if($inventoryItems->isNotEmpty())
            <table id="stock-table" class="table table-bordered table-striped mt-4">
                <thead>
                    <tr>
                        <th>Kode Part</th>
                        <th>Nama Part</th>
                        <th>Brand</th>
                        <th>Kategori</th>
                        <th>Rak</th>
                        <th>Qty</th>
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
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->part->satuan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif(request('gudang_id'))
            <p class="text-center mt-4">Tidak ada data stok untuk gudang yang dipilih.</p>
        @endif
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('#stock-table').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@stop
