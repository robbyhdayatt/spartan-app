@extends('adminlte::page')

@section('title', 'Buat Penjualan Baru')

@section('content_header')
    <h1>Buat Penjualan Baru</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.penjualans.store') }}" method="POST">
        @csrf
        <div class="card-body">
            {{-- BLOK UNTUK MENAMPILKAN SEMUA JENIS ERROR --}}
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
                 <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Sisa dari HTML form tidak berubah --}}
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="gudang_id">Gudang <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4" id="gudang_id" name="gudang_id" required>
                        <option value="">Pilih Gudang</option>
                        @foreach($gudangs as $gudang)
                            <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>{{ $gudang->nama_gudang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="konsumen_id">Konsumen <span class="text-danger">*</span></label>
                    <select class="form-control select2bs4" id="konsumen_id" name="konsumen_id" required>
                        <option value="">Pilih Konsumen</option>
                        @foreach($konsumens as $konsumen)
                            <option value="{{ $konsumen->id }}" {{ old('konsumen_id') == $konsumen->id ? 'selected' : '' }}>{{ $konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="tanggal_jual">Tanggal Jual <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_jual" name="tanggal_jual" value="{{ old('tanggal_jual', date('Y-m-d')) }}" required>
                </div>
            </div>

            <hr>

            <h5>Detail Part</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Part</th>
                            <th style="width: 20%;">Rak</th>
                            <th style="width: 10%;">Qty</th>
                            <th>Harga Jual</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="parts-container">
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-success btn-sm" id="add-part-btn"><i class="fas fa-plus"></i> Tambah Part</button>

            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td class="text-right" id="subtotal-text">Rp 0</td>
                            </tr>
                            <tr>
                                <th>Total Diskon:</th>
                                <td class="text-right text-success" id="diskon-text">Rp 0</td>
                            </tr>
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ppn-checkbox" checked>
                                        <label class="form-check-label" for="ppn-checkbox">PPN (11%)</label>
                                    </div>
                                </th>
                                <td class="text-right" id="pajak-text">Rp 0</td>
                            </tr>
                            <tr>
                                <th>Total Keseluruhan:</th>
                                <td class="text-right h4" id="total-text">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" name="subtotal" id="subtotal-input" value="0">
            <input type="hidden" name="total_diskon" id="diskon-input" value="0">
            <input type="hidden" name="pajak" id="pajak-input" value="0">
            <input type="hidden" name="total_harga" id="total-input" value="0">

        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
            <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<template id="part-row-template">
    <tr class="part-row">
        <td>
            <select class="form-control part-select select2bs4-template" name="items[__INDEX__][part_id]" required>
                <option value="">Pilih Part</option>
            </select>
        </td>
        <td>
            <select class="form-control rak-select select2bs4-template" name="items[__INDEX__][rak_id]" required>
                <option value="">Pilih Part terlebih dahulu</option>
            </select>
        </td>
        <td>
            {{-- PERBAIKI NAMA INPUT DI SINI --}}
            <input type="number" class="form-control qty-input" name="items[__INDEX__][qty_jual]" min="1" required>
        </td>
        <td>
            <input type="text" class="form-control harga-input" name="items[__INDEX__][harga_jual]" readonly>
            <input type="hidden" class="harga-original-input">
            <div class="discount-info text-muted small mt-1" style="font-style: italic;"></div>
        </td>
        <td>
            <input type="text" class="form-control subtotal-row" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-part-btn"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</template>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
    // Seluruh kode JavaScript di bawah ini tidak perlu diubah
$(document).ready(function() {
    let partIndex = 0;
    let partsData = {};

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
            let harga = parseFloat($(this).find('.harga-input').val().replace(/[^0-9]/g, '')) || 0;
            let hargaOriginal = parseFloat($(this).find('.harga-original-input').val()) || harga;
            let qty = parseInt($(this).find('.qty-input').val()) || 0;

            let diskon_row = (hargaOriginal - harga) * qty;
            totalDiskon += diskon_row;

            let subtotal_row = harga * qty;
            $(this).find('.subtotal-row').val(formatRupiah(subtotal_row));
            subtotal += subtotal_row;
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
        $('#total-input').val(total);
    }

    function fetchParts(gudangId) {
        if (!gudangId) return;
        if (partsData[gudangId]) {
            updateAllPartSelects(partsData[gudangId]);
            return;
        }
        $.ajax({
            url: `{{ url('admin/api/gudangs') }}/${gudangId}/parts`,
            type: 'GET',
            success: function(data) {
                partsData[gudangId] = data;
                updateAllPartSelects(data);
            }
        });
    }

    function updateAllPartSelects(parts) {
         $('.part-select').each(function() {
            let currentVal = $(this).val();
            let select = $(this);
            select.html('<option value="">Pilih Part</option>');
            $.each(parts, function(key, part) {
                select.append(`<option value="${part.id}">${part.kode_part} - ${part.nama_part}</option>`);
            });
            select.val(currentVal).trigger('change.select2');
        });
    }

    $('#gudang_id').on('change', function() {
        let gudangId = $(this).val();
        $('#parts-container').html('');
        partIndex = 0;
        calculateTotal();
        fetchParts(gudangId);
    });

    $('#add-part-btn').on('click', function() {
        let gudangId = $('#gudang_id').val();
        if (!gudangId) {
            alert('Silakan pilih gudang terlebih dahulu.');
            return;
        }
        let template = $('#part-row-template').html().replace(/__INDEX__/g, partIndex);
        $('#parts-container').append(template);
        let newRow = $('#parts-container').find('.part-row').last();

        newRow.find('.select2bs4-template').select2({
            theme: 'bootstrap4',
            placeholder: "Pilih...",
            width: '100%'
        }).removeClass('select2bs4-template');

        if (partsData[gudangId]) {
            let partSelect = newRow.find('.part-select');
            partSelect.html('<option value="">Pilih Part</option>');
            $.each(partsData[gudangId], function(key, part) {
                partSelect.append(`<option value="${part.id}">${part.kode_part} - ${part.nama_part}</option>`);
            });
        }
        partIndex++;
    });

    $('#parts-container').on('click', '.remove-part-btn', function() {
        $(this).closest('.part-row').remove();
        calculateTotal();
    });

    $('#parts-container').on('change', '.part-select', function() {
        let row = $(this).closest('.part-row');
        let partId = $(this).val();
        let konsumenId = $('#konsumen_id').val();
        let rakSelect = row.find('.rak-select');
        let hargaInput = row.find('.harga-input');
        let hargaOriginalInput = row.find('.harga-original-input');
        let discountInfo = row.find('.discount-info');

        rakSelect.html('<option value="">Memuat...</option>').prop('disabled', true);
        hargaInput.val('');
        hargaOriginalInput.val('');
        discountInfo.html('');
        calculateTotal();

        if (!partId || !konsumenId) {
            rakSelect.html('<option value="">Pilih Part & Konsumen</option>');
            return;
        }

        $.ajax({
            url: `{{ url('admin/api/parts') }}/${partId}/stock`,
            type: 'GET',
            data: { gudang_id: $('#gudang_id').val() },
            success: function(stockData) {
                rakSelect.html('<option value="">Pilih Rak</option>');
                $.each(stockData, function(key, item) {
                    rakSelect.append(`<option value="${item.rak_id}" data-max="${item.quantity}">[${item.kode_rak}] - Stok: ${item.quantity}</option>`);
                });
                rakSelect.prop('disabled', false);
                row.find('.qty-input').attr('max', 0);
            }
        });

        $.ajax({
            url: '{{ route("admin.api.calculate-discount") }}',
            type: 'GET',
            data: { part_id: partId, konsumen_id: konsumenId },
            success: function(response) {
                if (response.success) {
                    hargaInput.val(formatRupiah(response.data.final_price));
                    hargaOriginalInput.val(response.data.original_price);

                    if(response.data.applied_discounts.length > 0) {
                        let originalPriceText = formatRupiah(response.data.original_price);
                        let infoHtml = `Harga asli: <del>${originalPriceText}</del><br/>`;

                        response.data.applied_discounts.forEach(function(discountName) {
                            infoHtml += `<i class="fas fa-tag text-success"></i> <span class="text-success">${discountName}</span><br/>`;
                        });

                        discountInfo.html(infoHtml);
                    }
                }
                calculateTotal();
            }
        });
    });

    $('#parts-container').on('change', '.rak-select', function() {
        let selectedOption = $(this).find('option:selected');
        let maxQty = selectedOption.data('max') || 0;
        let currentRow = $(this).closest('.part-row');
        currentRow.find('.qty-input').attr('max', maxQty).val(1).trigger('change');

        let partId = currentRow.find('.part-select').val();
        let rakId = $(this).val();

        if (!rakId) return;

        $('.part-row').not(currentRow).each(function() {
            let otherPartId = $(this).find('.part-select').val();
            let otherRakId = $(this).find('.rak-select').val();
            if (partId === otherPartId && rakId === otherRakId) {
                alert('Part dari rak yang sama sudah dipilih. Silakan pilih dari rak lain atau ubah kuantitas pada baris yang sudah ada.');
                currentRow.find('.rak-select').val('').trigger('change.select2');
                return false;
            }
        });
    });

    $('#ppn-checkbox').on('change', function() {
        calculateTotal();
    });

    $('#parts-container').on('change keyup', '.qty-input, .harga-input', function() {
        calculateTotal();
    });
});
</script>
@stop
