@extends('adminlte::page')
@section('title', 'Buat Retur Penjualan')
@section('content_header')<h1>Buat Retur Penjualan</h1>@stop
@section('content')
<div class="card">
    <form action="{{ route('admin.sales-returns.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Pilih Faktur Penjualan Asli</label>
                    <select id="penjualan-select" name="penjualan_id" class="form-control" required>
                        <option value="" disabled selected>--- Pilih Faktur ---</option>
                        @foreach($penjualans as $penjualan)
                        <option value="{{ $penjualan->id }}">{{ $penjualan->nomor_faktur }} - {{ $penjualan->konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Tanggal Retur</label>
                    <input type="date" class="form-control" name="tanggal_retur" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
             <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan untuk retur"></textarea>
            </div>
            <h5 class="mt-4">Item untuk Diretur</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th><th style="width: 200px">Qty Tersedia u/ Diretur</th><th style="width: 200px">Qty Diretur</th>
                    </tr>
                </thead>
                <tbody id="return-items-table"></tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Retur Penjualan</button>
            <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)
@section('js')
<script>
$(document).ready(function() {
    $('#penjualan-select').select2();

    $('#penjualan-select').on('change', function() {
        let penjualanId = $(this).val();
        let tableBody = $('#return-items-table');
        tableBody.html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

        if (penjualanId) {
            let url = `/admin/api/penjualans/${penjualanId}/returnable-items`;
            $.getJSON(url, function(data) {
                tableBody.empty();
                if(data.length > 0) {
                    data.forEach(function(item) {
                        let availableToReturn = item.qty_jual - item.qty_diretur;
                        let row = `
                            <tr>
                                <td>${item.part.nama_part}</td>
                                <td><input type="text" class="form-control" value="${availableToReturn}" readonly></td>
                                <td><input type="number" name="items[${item.id}][qty_retur]" class="form-control" min="1" max="${availableToReturn}" value="${availableToReturn}" required></td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.html('<tr><td colspan="3" class="text-center">Tidak ada item yang bisa diretur dari faktur ini.</td></tr>');
                }
            });
        } else {
            tableBody.empty();
        }
    });
});
</script>
@stop
