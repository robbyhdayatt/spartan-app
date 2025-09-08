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
                    <select id="po-select" name="purchase_order_id" class="form-control select2" required>
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
                    {{-- Baris akan diisi oleh JavaScript --}}
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
    // Inisialisasi Select2
    $('.select2').select2();

    $('#po-select').on('change', function() {
        let poId = $(this).val();
        let tableBody = $('#receiving-items-table');

        // Ganti URL menjadi dinamis menggunakan route() Laravel
        // Ini adalah cara yang paling aman
        let url = "{{ route('admin.api.po.details', ['purchaseOrder' => ':poId']) }}";
        url = url.replace(':poId', poId);

        tableBody.html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

        if (poId) {
            $.getJSON(url, function(data) {
                tableBody.empty();
                if(data && data.details) {
                    data.details.forEach(function(item) {
                        let row = `
                            <tr>
                                <td>
                                    ${item.part.nama_part} (${item.part.kode_part})
                                    <input type="hidden" name="items[${item.part_id}][part_id]" value="${item.part_id}">
                                </td>
                                <td><input type="text" class="form-control" value="${item.qty_pesan}" readonly></td>
                                <td><input type="number" name="items[${item.part_id}][qty_terima]" class="form-control" min="0" value="${item.qty_pesan - item.qty_diterima}" required></td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data detail PO.</td></tr>');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                // Tambahkan ini untuk melihat error jika terjadi
                console.error("AJAX Error:", textStatus, errorThrown);
                console.error("Response Text:", jqXHR.responseText);
                tableBody.html('<tr><td colspan="3" class="text-center text-danger">Terjadi kesalahan saat mengambil data. Periksa console browser untuk detail.</td></tr>');
            });
        } else {
            tableBody.empty();
        }
    });
});
</script>
@stop
