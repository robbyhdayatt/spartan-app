@extends('adminlte::page')
@section('title', 'Buat Penjualan Baru')
@section('content_header')
    <h1>Buat Penjualan Baru</h1>
@stop
@section('content')
@php
    $user = Auth::user();
    $isSales = $user->jabatan->nama_jabatan === 'Sales';
@endphp
<div class="card">
    <form action="{{ route('admin.penjualans.store') }}" method="POST" id="penjualan-form">
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

            {{-- Header --}}
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Tanggal</label>
                    <input type="date" class="form-control" name="tanggal_jual" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                 <div class="col-md-3 form-group">
                    <label>Gudang</label>
                    @if($isSales)
                        <input type="text" class="form-control" value="{{ $user->gudang->nama_gudang }}" readonly>
                        <input type="hidden" id="gudang-select" name="gudang_id" value="{{ $user->gudang_id }}">
                    @else
                        <select id="gudang-select" class="form-control select2" name="gudang_id" required style="width: 100%;">
                            <option value="" disabled selected>Pilih Gudang</option>
                            @foreach($gudangs as $gudang)
                            <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label>Konsumen</label>
                    <select class="form-control select2" id="konsumen-select" name="konsumen_id" required style="width: 100%;">
                        <option value="" disabled selected>Pilih Konsumen</option>
                        @foreach($konsumens as $konsumen)
                        <option value="{{ $konsumen->id }}">{{ $konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
                 <div class="col-md-3 form-group">
                    <label>Sales</label>
                    @if($isSales)
                        <input type="text" class="form-control" value="{{ $user->nama }}" readonly>
                        <input type="hidden" name="sales_id" value="{{ $user->id }}">
                    @else
                        <select class="form-control select2" name="sales_id" style="width: 100%;">
                            <option value="">Transaksi Tanpa Sales</option>
                             @foreach($salesUsers as $sales)
                            <option value="{{ $sales->id }}">{{ $sales->nama }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            {{-- Items Table --}}
            <h5 class="mt-4">Item Penjualan</h5>
            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="30%">Part</th>
                        <th width="25%">Rak</th>
                        <th width="10%">Qty</th>
                        <th width="20%">Harga</th>
                        <th width="15%">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-table">
                    {{-- Rows added via JS --}}
                </tbody>
            </table>
            <button type="button" class="btn btn-success btn-sm" id="add-item-btn" disabled>+ Tambah Item</button>

            {{-- Total Kalkulasi dan PPN --}}
            <div class="row justify-content-end mt-4">
                <div class="col-md-5">
                    <table class="table table-sm">
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-right" id="display-subtotal">Rp 0</td>
                        </tr>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ppn-checkbox" name="kena_ppn" value="1">
                                    <label class="form-check-label" for="ppn-checkbox">PPN (11%)</label>
                                </div>
                            </th>
                            <td class="text-right" id="display-ppn">Rp 0</td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.2rem;">Grand Total</th>
                            <td class="text-right font-weight-bold" style="font-size: 1.2rem;" id="display-grand-total">Rp 0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

{{-- TEMPLATE UNTUK BARIS ITEM BARU --}}
<template id="item-template">
    <tr>
        <td>
            <select class="form-control item-part" name="items[__INDEX__][part_id]" required style="width: 100%;">
                <option value="" disabled selected>Pilih Part</option>
            </select>
        </td>
        <td>
            <select class="form-control item-rak" name="items[__INDEX__][rak_id]" required style="width: 100%;">
                 <option value="" disabled selected>Pilih Part Dahulu</option>
            </select>
        </td>
        <td><input type="number" class="form-control item-qty" name="items[__INDEX__][qty]" min="1" value="1" required></td>
        <td>
            {{-- Harga akan diisi oleh JS, dibuat readonly agar tidak bisa diubah manual --}}
            <input type="text" class="form-control item-harga text-right" name="items[__INDEX__][harga_display]" readonly>
            {{-- Info diskon akan ditampilkan di sini --}}
            <small class="form-text text-muted price-info"></small>
        </td>
        <td><input type="text" class="form-control item-subtotal text-right" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">&times;</button></td>
    </tr>
</template>
@stop

@section('plugins.Select2', true)
@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({ placeholder: "Pilih Opsi" });

    let itemIndex = 0;
    let partsCache = {}; // Cache untuk daftar part per gudang
    const addItemBtn = $('#add-item-btn');
    const gudangSelect = $('#gudang-select');
    const konsumenSelect = $('#konsumen-select');
    const itemsTable = $('#items-table');

    // Fungsi untuk format Rupiah
    const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);

    // Aktifkan tombol "Tambah Item" hanya jika Gudang dan Konsumen sudah dipilih
    function toggleAddItemButton() {
        if (gudangSelect.val() && konsumenSelect.val()) {
            addItemBtn.prop('disabled', false);
        } else {
            addItemBtn.prop('disabled', true);
        }
    }

    // Muat daftar part saat gudang dipilih
    function loadParts(gudangId) {
        partsCache = {};
        itemsTable.empty();
        addItemBtn.text('Loading Parts...').prop('disabled', true);

        let url = "{{ route('admin.api.gudang.parts', ['gudang' => ':gudangId']) }}".replace(':gudangId', gudangId);

        $.getJSON(url, function(data) {
            partsCache = data;
            addItemBtn.text('+ Tambah Item');
            toggleAddItemButton();
            calculateAll();
        }).fail(() => alert("Gagal memuat data part."));
    }

    // --- EVENT LISTENERS ---

    // Jika Gudang atau Konsumen berubah
    gudangSelect.add(konsumenSelect).on('change', function() {
        toggleAddItemButton();
        const currentGudang = gudangSelect.val();
        // Jika gudang berubah, reset tabel dan muat ulang parts
        if ($(this).is('#gudang-select')) {
             loadParts(currentGudang);
        }
    });

    // Tambah baris item baru
    addItemBtn.on('click', function() {
        let template = $('#item-template').html().replace(/__INDEX__/g, itemIndex);
        itemsTable.append(template);

        let newRow = itemsTable.find('tr').last();
        let partSelect = newRow.find('.item-part');

        partSelect.append(new Option('Pilih Part', '', true, true));
        partsCache.forEach(part => {
            partSelect.append(new Option(`${part.nama_part} (${part.kode_part})`, part.id));
        });

        newRow.find('.select2, .item-part, .item-rak').select2({ placeholder: "Pilih Opsi" });
        itemIndex++;
    });

    // Hapus baris item
    itemsTable.on('click', '.remove-item-btn', function() {
        $(this).closest('tr').remove();
        calculateAll();
    });

    // Event utama: Saat Part dipilih
    itemsTable.on('change', '.item-part', function() {
        let row = $(this).closest('tr');
        let partId = $(this).val();
        let gudangId = gudangSelect.val();
        let konsumenId = konsumenSelect.val();
        let rakSelect = row.find('.item-rak');
        let hargaDisplay = row.find('.item-harga');
        let priceInfo = row.find('.price-info');

        // Reset tampilan
        rakSelect.empty().prop('disabled', true);
        hargaDisplay.val('');
        priceInfo.text('Loading harga...');

        if (!partId || !gudangId || !konsumenId) return;

        let url = "{{ route('admin.api.part.stock', ['part' => ':partId']) }}"
            .replace(':partId', partId) + `?gudang_id=${gudangId}&konsumen_id=${konsumenId}`;

        $.getJSON(url, function(response) {
            // Isi pilihan RAK
            rakSelect.append(new Option('Pilih Rak (Stok)', '', true, true)).prop('disabled', false);
            response.stock_details.forEach(stock => {
                rakSelect.append(new Option(`${stock.nama_rak} (Stok: ${stock.quantity})`, stock.rak_id));
            });
            rakSelect.trigger('change');

            // Proses hasil diskon dan tampilkan
            const discount = response.discount_result;
            hargaDisplay.val(formatRupiah(discount.final_price));

            if (discount.applied_discounts.length > 0) {
                 priceInfo.html(`Asli: <del>${formatRupiah(discount.original_price)}</del> <br> <span class="text-success">${discount.applied_discounts.join(', ')}</span>`);
            } else {
                 priceInfo.text('');
            }
            updateSubtotal(row);

        }).fail(() => {
            alert('Gagal memuat detail stok & harga.');
            priceInfo.text('Error');
        });
    });

    // Update subtotal saat qty berubah
    itemsTable.on('keyup change', '.item-qty', function() {
        updateSubtotal($(this).closest('tr'));
    });

    // Fungsi untuk update subtotal per baris
    function updateSubtotal(row) {
        // Ambil harga dari text display, hilangkan format Rupiah, lalu parse
        let hargaText = row.find('.item-harga').val().replace(/[^0-9,-]+/g,"").replace(',','.');
        let harga = parseFloat(hargaText) || 0;
        let qty = parseInt(row.find('.item-qty').val()) || 0;
        row.find('.item-subtotal').val(formatRupiah(qty * harga));
        calculateAll();
    }

    // Fungsi untuk kalkulasi total keseluruhan
    function calculateAll() {
        let subtotalTotal = 0;
        itemsTable.find('tr').each(function() {
            let subtotalText = $(this).find('.item-subtotal').val().replace(/[^0-9,-]+/g,"").replace(',','.');
            subtotalTotal += parseFloat(subtotalText) || 0;
        });

        let ppnAmount = $('#ppn-checkbox').is(':checked') ? subtotalTotal * 0.11 : 0;
        let grandTotal = subtotalTotal + ppnAmount;

        $('#display-subtotal').text(formatRupiah(subtotalTotal));
        $('#display-ppn').text(formatRupiah(ppnAmount));
        $('#display-grand-total').text(formatRupiah(grandTotal));
    }

    $('#ppn-checkbox').on('change', calculateAll);

    // Inisialisasi jika gudang sudah terpilih (untuk role Sales)
    if(gudangSelect.val()){
        loadParts(gudangSelect.val());
    }
});
</script>
@stop
