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
    <form action="{{ route('admin.penjualans.store') }}" method="POST">
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
                    <select class="form-control select2" name="konsumen_id" required style="width: 100%;">
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
                        <th width="30%">Rak</th>
                        <th width="10%">Qty</th>
                        <th width="15%">Harga</th>
                        <th width="15%">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-table">
                    {{-- Rows added via JS --}}
                </tbody>
            </table>
            <button type="button" class="btn btn-success btn-sm" id="add-item-btn">+ Tambah Item</button>

            {{-- PERBAIKAN 1: Tambahkan Total Kalkulasi dan PPN --}}
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
                                    <label class="form-check-label" for="ppn-checkbox">
                                        PPN (11%)
                                    </label>
                                </div>
                            </th>
                            <td class="text-right" id="display-ppn">Rp 0</td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.2rem;">Grand Total</th>
                            <td class="text-right font-weight-bold" style="font-size: 1.2rem;" id="display-grand-total">Rp 0</td>
                        </tr>
                    </table>
                     {{-- Hidden inputs to store calculated values --}}
                     <input type="hidden" name="subtotal" id="input-subtotal" value="0">
                     <input type="hidden" name="ppn_jumlah" id="input-ppn" value="0">
                     <input type="hidden" name="total_harga" id="input-grand-total" value="0">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            <a href="{{ route('admin.penjualans.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<template id="item-template">
    <tr>
        <td>
            <select class="form-control item-part" name="items[__INDEX__][part_id]" required style="width: 100%;">
                <option value="" disabled selected>Pilih Gudang Dahulu</option>
            </select>
        </td>
        <td>
            <select class="form-control item-rak" name="items[__INDEX__][rak_id]" required style="width: 100%;">
                 <option value="" disabled selected>Pilih Part Dahulu</option>
            </select>
        </td>
        <td><input type="number" class="form-control item-qty" name="items[__INDEX__][qty]" min="1" value="1" required></td>
        <td><input type="number" class="form-control item-harga" name="items[__INDEX__][harga]" min="0" required></td>
        <td><input type="text" class="form-control item-subtotal" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">&times;</button></td>
    </tr>
</template>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Pilih Opsi",
    });

    let itemIndex = 0;
    let partsCache = {};
    const addItemBtn = $('#add-item-btn');

    // ... (fungsi refreshPartOptions dan loadParts tetap sama) ...
    function refreshPartOptions() {
        let selectedParts = [];
        $('.item-part').each(function() {
            let val = $(this).val();
            if (val) selectedParts.push(val);
        });
        $('.item-part option').prop('disabled', false);
        $('.item-part').each(function() {
            let currentVal = $(this).val();
            $(this).find('option').each(function() {
                if (selectedParts.includes($(this).val()) && $(this).val() !== currentVal) {
                    $(this).prop('disabled', true);
                }
            });
        });
        $('.item-part').trigger('change.select2');
    }

    function loadParts(gudangId) {
        partsCache = {};
        $('#items-table').empty();
        addItemBtn.prop('disabled', true).text('Loading Parts...');
        if (gudangId) {
            let url = "{{ route('admin.api.gudang.parts', ['gudang' => ':gudangId']) }}";
            url = url.replace(':gudangId', gudangId);
            $.getJSON(url, function(data) {
                partsCache = data;
                addItemBtn.prop('disabled', false).text('+ Tambah Item');
                calculateAll(); // Kalkulasi ulang jika daftar part berubah
            }).fail(function() {
                alert("Gagal memuat data part.");
                addItemBtn.prop('disabled', false).text('+ Tambah Item');
            });
        }
    }


    @if($isSales)
        loadParts({{ $user->gudang_id }});
    @endif

    $('#gudang-select').on('change', function() {
        loadParts($(this).val());
    });

    addItemBtn.on('click', function() {
        if (!$('#gudang-select').val()) {
            alert('Pilih gudang terlebih dahulu!');
            return;
        }
        let template = $('#item-template').html().replace(/__INDEX__/g, itemIndex);
        $('#items-table').append(template);
        let newRow = $(`#items-table tr`).last();
        let partSelect = newRow.find('.item-part');
        let rakSelect = newRow.find('.item-rak');
        partSelect.html('<option value="" disabled selected>Pilih Part</option>');
        if(Object.keys(partsCache).length > 0) {
             partsCache.forEach(function(part) {
                let option = new Option(`${part.nama_part} (${part.kode_part})`, part.id);
                $(option).attr('data-harga', part.effective_price);
                partSelect.append(option);
            });
        } else {
            partSelect.html('<option value="" disabled selected>Tidak ada stok part di gudang ini</option>');
        }
        partSelect.select2({ placeholder: "Pilih Part" });
        rakSelect.select2({ placeholder: "Pilih Part Dahulu" });
        itemIndex++;
        refreshPartOptions();
    });

    $('#items-table').on('click', '.remove-item-btn', function() {
        $(this).closest('tr').remove();
        refreshPartOptions();
        calculateAll(); // Kalkulasi ulang setelah item dihapus
    });

    $('#items-table').on('change', '.item-part', function() {
        let row = $(this).closest('tr');
        let partId = $(this).val();
        let rakSelect = row.find('.item-rak');
        let gudangId = $('#gudang-select').val();
        let hargaInput = row.find('.item-harga');
        let effectivePrice = $(this).find('option:selected').data('harga') || 0;
        hargaInput.val(effectivePrice);
        updateSubtotal(row);
        rakSelect.html('<option value="" disabled selected>Loading...</option>');
        if (partId && gudangId) {
            let url = "{{ route('admin.api.part.stock', ['part' => ':partId']) }}";
            url = url.replace(':partId', partId) + `?gudang_id=${gudangId}`;
            $.getJSON(url, function(data) {
                 rakSelect.empty().html('<option value="" disabled selected>Pilih Rak (Stok)</option>');
                 data.forEach(function(stock) {
                     rakSelect.append(new Option(`${stock.rak.kode_rak} (Stok: ${stock.quantity})`, stock.rak_id));
                 });
                 rakSelect.select2({ placeholder: "Pilih Rak (Stok)" });
            }).fail(function() {
                alert('Gagal memuat detail stok rak.');
                rakSelect.empty().html('<option value="" disabled selected>Error, coba lagi</option>');
            });
        }
        refreshPartOptions();
    });

    function updateSubtotal(row) {
        let harga = parseFloat(row.find('.item-harga').val()) || 0;
        let qty = parseInt(row.find('.item-qty').val()) || 0;
        let subtotal = qty * harga;
        row.find('.item-subtotal').val(subtotal.toLocaleString('id-ID'));
        calculateAll(); // Panggil kalkulasi total setiap subtotal berubah
    }

    // PERBAIKAN 2: Tambahkan fungsi kalkulasi PPN dan Grand Total
    function calculateAll() {
        let subtotalTotal = 0;
        $('#items-table tr').each(function() {
            let harga = parseFloat($(this).find('.item-harga').val()) || 0;
            let qty = parseInt($(this).find('.item-qty').val()) || 0;
            subtotalTotal += harga * qty;
        });

        let ppnAmount = 0;
        if ($('#ppn-checkbox').is(':checked')) {
            ppnAmount = subtotalTotal * 0.11;
        }

        let grandTotal = subtotalTotal + ppnAmount;

        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

        // Update display
        $('#display-subtotal').text(formatter.format(subtotalTotal));
        $('#display-ppn').text(formatter.format(ppnAmount));
        $('#display-grand-total').text(formatter.format(grandTotal));

        // Update hidden inputs for submission
        $('#input-subtotal').val(subtotalTotal);
        $('#input-ppn').val(ppnAmount);
        $('#input-grand-total').val(grandTotal);
    }

    // Panggil kalkulasi saat kuantitas atau harga diubah
     $('#items-table').on('keyup change', '.item-qty, .item-harga', function() {
        updateSubtotal($(this).closest('tr'));
     });

     // Panggil kalkulasi saat checkbox PPN diubah
     $('#ppn-checkbox').on('change', function() {
        calculateAll();
     });
});
</script>
@stop
