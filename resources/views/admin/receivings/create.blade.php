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
                    {{-- ID Select diganti menjadi po-select agar sesuai dengan JS Anda --}}
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
            <p class="text-muted">Masukkan jumlah barang yang diterima secara fisik. Sistem akan otomatis menampilkan item yang kuantitasnya belum terpenuhi.</p>

            {{-- PERUBAHAN 1: Menyesuaikan Header Tabel --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th style="width: 15%">Qty Pesan</th>
                        <th style="width: 15%">Sudah Diterima</th>
                        <th style="width: 15%">Qty Sisa</th>
                        <th style="width: 15%">Qty Diterima Saat Ini</th>
                    </tr>
                </thead>
                {{-- ID Tbody disesuaikan --}}
                <tbody id="receiving-items-table">
                    {{-- Baris akan diisi oleh JavaScript --}}
                    <tr>
                        <td colspan="5" class="text-center text-muted">Pilih PO untuk menampilkan item.</td>
                    </tr>
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
{{-- PERUBAHAN 2: Menyesuaikan Blok JavaScript --}}
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        placeholder: "--- Pilih PO yang sudah disetujui ---",
        allowClear: true
    });

    $('#po-select').on('change', function() {
        let poId = $(this).val();
        let tableBody = $('#receiving-items-table');

        // Menggunakan route yang telah kita buat sebelumnya
        let url = "{{ route('admin.purchase_orders.details_api', ['purchaseOrder' => ':poId']) }}";
        url = url.replace(':poId', poId);

        tableBody.html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat detail item...</td></tr>');

        if (poId) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(details) {
                    tableBody.empty();
                    if(details && details.length > 0) {
                        let hasItemsToShow = false;
                        details.forEach(function(item) {
                            // HANYA TAMPILKAN JIKA MASIH ADA SISA BARANG
                            if (item.qty_sisa > 0) {
                                hasItemsToShow = true;
                                let row = `
                                    <tr>
                                        <td>
                                            ${item.nama_part} (${item.kode_part})
                                            {{-- Nama input disesuaikan dengan logika store Anda: items[part_id][...] --}}
                                            <input type="hidden" name="items[${item.part_id}][po_detail_id]" value="${item.po_detail_id}">
                                            <input type="hidden" name="items[${item.part_id}][part_id]" value="${item.part_id}">
                                        </td>
                                        <td><input type="text" class="form-control" value="${item.qty_pesan}" readonly></td>
                                        <td><input type="text" class="form-control" value="${item.qty_sudah_diterima}" readonly></td>
                                        <td><input type="text" class="form-control" value="${item.qty_sisa}" readonly></td>
                                        <td>
                                            <input type="number" name="items[${item.part_id}][qty_terima]"
                                                   class="form-control"
                                                   min="0"
                                                   max="${item.qty_sisa}"  {{-- Atribut max untuk validasi --}}
                                                   value="${item.qty_sisa}" {{-- Default value adalah sisa barang --}}
                                                   required>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            }
                        });

                        if (!hasItemsToShow) {
                           tableBody.html('<tr><td colspan="5" class="text-center text-success">Semua item dari PO ini sudah diterima.</td></tr>');
                        }

                    } else {
                        tableBody.html('<tr><td colspan="5" class="text-center text-danger">Tidak ada detail item pada PO ini.</td></tr>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    tableBody.html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data. Periksa console browser.</td></tr>');
                }
            });
        } else {
            tableBody.html('<tr><td colspan="5" class="text-center text-muted">Pilih PO untuk menampilkan item.</td></tr>');
        }
    });
});
</script>
@stop
