@extends('adminlte::page')

@section('title', 'Catat Penerimaan Barang')

@section('content_header')
    <h1>Catat Penerimaan Barang</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.receivings.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Pilih Purchase Order (PO)</label>
                    <select id="po-select" name="purchase_order_id" class="form-control" required>
                        <option value="" disabled selected>--- Pilih PO yang sudah disetujui ---</option>
                        @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}">{{ $po->nomor_po }} - {{ $po->supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Tanggal Terima</label>
                    <input type="date" class="form-control" name="tanggal_terima" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
             <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control" rows="2"></textarea>
            </div>

            <h5 class="mt-4">Item Diterima</h5>
            <p class="text-muted">Masukkan jumlah barang yang diterima secara fisik.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th style="width: 150px">Qty Dipesan</th>
                        <th style="width: 150px">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody id="receiving-items-table">
                    {{-- Items will be loaded here by JavaScript --}}
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Data Penerimaan</button>
            <a href="{{ route('admin.receivings.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#po-select').on('change', function() {
        let poId = $(this).val();
        let url = `/admin/api/purchase-orders/${poId}`;
        let tableBody = $('#receiving-items-table');

        tableBody.html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

        if (poId) {
            $.getJSON(url, function(data) {
                tableBody.empty();
                if(data && data.details) {
                    data.details.forEach(function(item) {
                        let row = `
                            <tr>
                                <td>
                                    ${item.part.nama_part} (${item.part.kode_part})
                                    <input type="hidden" name="items[${item.part.id}][part_id]" value="${item.part.id}">
                                </td>
                                <td><input type="text" class="form-control" value="${item.qty_pesan}" readonly></td>
                                <td><input type="number" name="items[${item.part.id}][qty_terima]" class="form-control" min="0" value="${item.qty_pesan}" required></td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                }
            });
        } else {
            tableBody.empty();
        }
    });
});
</script>
@stop
