@extends('adminlte::page')

@section('title', 'Buat Penjualan Baru (FIFO)')

@section('content_header')
    <h1>Buat Penjualan Baru (FIFO)</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.penjualans.store') }}" method="POST">
        @csrf
        <div class="card-body">
            {{-- Blok Error --}}
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

            {{-- Header Form --}}
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="gudang_id">Gudang <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4" id="gudang_id" name="gudang_id" required>
                        <option value="">Pilih Gudang</option>
                        @foreach($gudangs as $gudang)
                            <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>{{ $gudang->nama_gudang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="konsumen_id">Konsumen <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4" id="konsumen_id" name="konsumen_id" required>
                        <option value="">Pilih Konsumen</option>
                        @foreach($konsumens as $konsumen)
                            <option value="{{ $konsumen->id }}" {{ old('konsumen_id') == $konsumen->id ? 'selected' : '' }}>{{ $konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="tanggal_jual">Tanggal Jual <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_jual" name="tanggal_jual" value="{{ old('tanggal_jual', date('Y-m-d')) }}" required>
                </div>
            </div>

            <hr>

            {{-- Input Part --}}
            <div class="row align-items-end">
                <div class="col-md-5 form-group">
                    <label for="part-selector">Pilih Part</label>
                    <select id="part-selector" class="form-control select2bs4" disabled>
                        <option>Pilih Gudang Terlebih Dahulu</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label for="qty-selector">Jumlah</label>
                    <input type="number" id="qty-selector" class="form-control" min="1">
                </div>
                <div class="col-md-2 form-group">
                    <button type="button" class="btn btn-primary" id="add-part-btn">Tambahkan</button>
                </div>
            </div>

            <hr>

            <h5>Detail Part yang Akan Dijual</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Part</th>
                            <th style="width: 20%;">Rak (Otomatis FIFO)</th>
                            <th style="width: 10%;">Qty</th>
                            <th>Harga Jual</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="parts-container">
                        {{-- Baris Part akan ditambahkan di sini oleh JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan Total --}}
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="table-responsive">
                        <table class="table">
                            <tr><th style="width:50%">Subtotal:</th><td class="text-right" id="subtotal-text">Rp 0</td></tr>
                            <tr><th>Total Diskon:</th><td class="text-right text-success" id="diskon-text">Rp 0</td></tr>
                            <tr><th><div class="form-check"><input class="form-check-input" type="checkbox" id="ppn-checkbox" name="use_ppn" value="1" checked><label class="form-check-label" for="ppn-checkbox">PPN (11%)</label></div></th><td class="text-right" id="pajak-text">Rp 0</td></tr>
                            <tr><th>Total Keseluruhan:</th><td class="text-right h4" id="total-text">Rp 0</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" name="subtotal" id="subtotal-input" value="0">
            <input type="hidden" name="total_diskon" id="diskon-input" value="0">
            <input type="hidden" name="pajak" id="pajak-input" value="0">
            <input type="hidden" name="total_harga" id="total-harga-input" value="0">
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    let partsData = {};
    let itemIndex = 0;
    $('.select2bs4').select2({ theme: 'bootstrap4' });

    function formatRupiah(angka) {
        let number_string = Math.round(angka).toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return 'Rp ' + (rupiah ? rupiah : '0');
    }

    function calculateTotal() {
        let subtotal = 0;
        let totalDiskon = 0;
        $('.part-row').each(function() {
            let hargaFinal = parseFloat($(this).find('.harga-final-input').val()) || 0;
            let hargaOriginal = parseFloat($(this).find('.harga-original-input').val()) || hargaFinal;
            let qty = parseInt($(this).find('.qty-input').val()) || 0;
            totalDiskon += (hargaOriginal - hargaFinal) * qty;
            let subtotalRow = hargaFinal * qty;
            $(this).find('.subtotal-row-text').text(formatRupiah(subtotalRow));
            subtotal += subtotalRow;
        });
        let pajak = 0;
        if ($('#ppn-checkbox').is(':checked')) {
            pajak = subtotal * 0.11;
        }
        let total = subtotal + pajak;
        $('#subtotal-text').text(formatRupiah(subtotal));
        $('#diskon-text').text(formatRupiah(totalDiskon));
        $('#pajak-text').text(formatRupiah(pajak));
        $('#total-text').text(formatRupiah(total));
        $('#subtotal-input').val(subtotal);
        $('#diskon-input').val(totalDiskon);
        $('#pajak-input').val(pajak);
        $('#total-harga-input').val(total);
    }

    $('#gudang_id').on('change', function() {
        let gudangId = $(this).val();
        let partSelector = $('#part-selector');
        partSelector.prop('disabled', true).html('<option>Memuat...</option>');
        if (!gudangId) {
            partSelector.html('<option>Pilih Gudang Terlebih Dahulu</option>');
            return;
        }
        $.ajax({
            url: `{{ url('admin/api/gudangs') }}/${gudangId}/parts`,
            success: function(parts) {
                partsData = parts;
                partSelector.prop('disabled', false).html('<option value="">Pilih Part</option>');
                parts.forEach(part => {
                    partSelector.append(`<option value="${part.id}" data-total-stock="${part.total_stock}">${part.kode_part} - ${part.nama_part} (Stok: ${part.total_stock})</option>`);
                });
            },
            error: function() { partSelector.html('<option>Gagal memuat part</option>'); }
        });
    });

    $('#add-part-btn').on('click', function() {
        let partId = $('#part-selector').val();
        let qtyJual = parseInt($('#qty-selector').val());
        let gudangId = $('#gudang_id').val();
        let konsumenId = $('#konsumen_id').val();

        if (!partId || !qtyJual || qtyJual <= 0 || !konsumenId) {
            alert('Silakan pilih Gudang, Konsumen, Part, dan isi jumlah yang valid.');
            return;
        }

        // Cek duplikat
        if ($(`.part-row[data-part-id="${partId}"]`).length > 0) {
            alert('Part ini sudah ditambahkan. Hapus terlebih dahulu jika ingin mengubah jumlah.');
            return;
        }

        // Ambil data batch
        $.ajax({
            url: `{{ route('admin.api.get-fifo-batches') }}`,
            data: { part_id: partId, gudang_id: gudangId },
            success: function(batches) {
                // Ambil harga diskon
                $.ajax({
                    url: '{{ route("admin.api.calculate-discount") }}',
                    data: { part_id: partId, konsumen_id: konsumenId },
                    success: function(response) {
                        if (!response.success) {
                            alert('Gagal menghitung harga diskon.');
                            return;
                        }

                        let sisaQty = qtyJual;
                        let hargaFinal = response.data.final_price;
                        let hargaOriginal = response.data.original_price;

                        for (const batch of batches) {
                            if (sisaQty <= 0) break;
                            let qtyAmbil = Math.min(sisaQty, batch.quantity);

                            let newRowHtml = `
                                <tr class="part-row" data-part-id="${partId}">
                                    <td>
                                        ${partsData.find(p => p.id == partId).nama_part}
                                        <input type="hidden" name="items[${itemIndex}][part_id]" value="${partId}">
                                        <input type="hidden" name="items[${itemIndex}][batch_id]" value="${batch.id}">
                                        <input type="hidden" class="harga-original-input" value="${hargaOriginal}">
                                        <input type="hidden" class="harga-final-input" value="${hargaFinal}">
                                    </td>
                                    <td>${batch.rak.kode_rak}</td>
                                    <td><input type="number" class="form-control qty-input" name="items[${itemIndex}][qty_jual]" value="${qtyAmbil}" readonly></td>
                                    <td class="text-right">${formatRupiah(hargaFinal)}</td>
                                    <td class="text-right subtotal-row-text">${formatRupiah(hargaFinal * qtyAmbil)}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-part-btn"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            `;
                            $('#parts-container').append(newRowHtml);
                            sisaQty -= qtyAmbil;
                            itemIndex++;
                        }

                        if (sisaQty > 0) {
                            alert(`Stok tidak mencukupi. Hanya tersedia ${qtyJual - sisaQty} unit.`);
                        }

                        calculateTotal();
                        $('#part-selector').val('').trigger('change');
                        $('#qty-selector').val('');
                    }
                });
            }
        });
    });

    $('#parts-container').on('click', '.remove-part-btn', function() {
        let partId = $(this).closest('tr').data('part-id');
        $(`tr[data-part-id="${partId}"]`).remove();
        calculateTotal();
    });

    $('#ppn-checkbox').on('change', calculateTotal);
});
</script>
@stop
