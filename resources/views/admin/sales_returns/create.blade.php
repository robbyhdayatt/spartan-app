@extends('adminlte::page')

@section('title', 'Buat Retur Penjualan')

@section('content_header')
    <h1>Buat Retur Penjualan</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.sales-returns.store') }}" method="POST">
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
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="penjualan-select">Pilih Faktur Penjualan Asli</label>
                    <select id="penjualan-select" name="penjualan_id" class="form-control" required>
                        <option value="" selected>--- Pilih Faktur ---</option>
                        @foreach($penjualans as $penjualan)
                            <option value="{{ $penjualan->id }}">{{ $penjualan->nomor_faktur }} - {{ $penjualan->konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="tanggal-retur">Tanggal Retur</label>
                    <input type="date" id="tanggal-retur" class="form-control" name="tanggal_retur" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>

             <div class="form-group">
                <label for="catatan">Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Catatan tambahan untuk retur">{{ old('catatan') }}</textarea>
            </div>

            <h5 class="mt-4">Item untuk Diretur</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th style="width: 200px">Qty Tersedia u/ Diretur</th>
                            <th style="width: 200px">Qty Diretur</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-table">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Pilih faktur untuk menampilkan item.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Retur Penjualan</button>
            <a href="{{ route('admin.sales-returns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

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
    // Inisialisasi Select2
    $('#penjualan-select').select2({
        placeholder: "--- Pilih Faktur ---",
        allowClear: true
    });

    // Event handler ketika faktur dipilih
    $('#penjualan-select').on('change', function() {
        let penjualanId = $(this).val();
        let tableBody = $('#return-items-table');

        // Kosongkan tabel jika tidak ada faktur yang dipilih
        if (!penjualanId) {
            tableBody.html('<tr><td colspan="3" class="text-center text-muted">Pilih faktur untuk menampilkan item.</td></tr>');
            return;
        }

        // Tampilkan status loading
        tableBody.html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat item...</td></tr>');

        // Buat URL yang benar menggunakan route helper Laravel
        let url = "{{ route('admin.penjualans.returnable-items', ['penjualan' => ':id']) }}".replace(':id', penjualanId);

        // Request ke API untuk mengambil data item
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(items) {
                tableBody.empty();
                if (items.length > 0) {
                    items.forEach(function(item) {
                        let availableToReturn = item.qty_jual - item.qty_diretur;
                        let row = `
                            <tr>
                                <td>
                                    ${item.part.nama_part}
                                    <input type="hidden" name="items[${item.id}][penjualan_detail_id]" value="${item.id}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" value="${availableToReturn}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="items[${item.id}][qty_retur]" class="form-control" value="1" min="1" max="${availableToReturn}" required>
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.html('<tr><td colspan="3" class="text-center">Tidak ada item yang bisa diretur dari faktur ini.</td></tr>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Tampilkan pesan error yang lebih informatif
                let errorMsg = 'Gagal memuat item. Silakan coba lagi.';
                if (jqXHR.status === 404) {
                    errorMsg = 'Gagal memuat item. Endpoint tidak ditemukan (Error 404).';
                } else if (jqXHR.status === 500) {
                    errorMsg = 'Gagal memuat item. Terjadi kesalahan pada server (Error 500).';
                }
                tableBody.html('<tr><td colspan="3" class="text-center text-danger">' + errorMsg + '</td></tr>');
                console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });
});
</script>
@stop
