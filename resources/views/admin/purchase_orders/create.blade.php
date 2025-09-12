@extends('adminlte::page')

@section('title', 'Buat Purchase Order Baru')

@section('content_header')
    <h1>Buat Purchase Order Baru</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="po-form">
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
                {{-- PO Header --}}
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tanggal PO</label>
                        <input type="date" class="form-control" name="tanggal_po" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Supplier</label>
                        <select class="form-control select2" name="supplier_id" required style="width: 100%;">
                            <option value="" disabled selected>Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Tujuan Gudang</label>
                        @if(count($gudangs) === 1)
                            <input type="text" class="form-control" value="{{ $gudangs->first()->nama_gudang }}" readonly>
                            <input type="hidden" name="gudang_id" value="{{ $gudangs->first()->id }}">
                        @else
                            <select class="form-control select2" name="gudang_id" required style="width: 100%;">
                                <option value="" disabled selected>Pilih Gudang</option>
                                @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
                 <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2"></textarea>
                </div>

                {{-- PO Items Table --}}
                <h5 class="mt-4">Item Sparepart</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th style="width: 120px">Qty</th>
                                <th style="width: 200px">Harga Beli (Rp)</th>
                                <th style="width: 200px">Subtotal (Rp)</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody id="po-items-table">
                            {{-- Items will be added here by JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-success btn-sm" id="add-item-btn">+ Tambah Item</button>

                {{-- Total Kalkulasi dan PPN (Struktur disamakan dengan Penjualan) --}}
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
                                        {{-- ID dan name disesuaikan dengan logika JS & controller yang ada --}}
                                        <input class="form-check-input" type="checkbox" id="ppn-checkbox" name="use_ppn" value="1">
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
                        <input type="hidden" name="total" id="input-grand-total" value="0">
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Purchase Order</button>
                 <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    {{-- Hidden template for a new row --}}
    <template id="po-item-template">
        <tr>
            <td>
                <select class="form-control item-part select2-part" name="items[__INDEX__][part_id]" required style="width: 100%;">
                    <option value="" disabled selected>Pilih Part</option>
                    @foreach($parts as $part)
                    <option value="{{ $part->id }}" data-harga="{{ $part->effective_price }}">{{ $part->nama_part }} ({{ $part->kode_part }})</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" class="form-control item-qty" name="items[__INDEX__][qty]" min="1" value="1" required></td>
            <td><input type="number" class="form-control item-harga" name="items[__INDEX__][harga]" min="0" required></td>
            <td><input type="text" class="form-control item-subtotal" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">&times;</button></td>
        </tr>
    </template>
@stop

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
            // Inisialisasi Select2 untuk header
            $('.select2').select2();

            let itemIndex = 0;

            // Fungsi untuk menghitung total keseluruhan (disesuaikan dengan tampilan baru)
            function calculateGrandTotal() {
                let subtotalTotal = 0;
                $('#po-items-table tr').each(function() {
                    const itemSubtotal = parseFloat($(this).find('.item-subtotal').attr('data-value')) || 0;
                    subtotalTotal += itemSubtotal;
                });

                let ppnAmount = 0;
                if ($('#ppn-checkbox').is(':checked')) {
                    ppnAmount = subtotalTotal * 0.11;
                }

                const grandTotal = subtotalTotal + ppnAmount;

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

            // Fungsi untuk menghitung subtotal per baris
            function calculateRowSubtotal(row) {
                let harga = parseFloat(row.find('.item-harga').val()) || 0;
                let qty = parseInt(row.find('.item-qty').val()) || 0;
                let subtotal = qty * harga;

                row.find('.item-subtotal').val(subtotal.toLocaleString('id-ID')).attr('data-value', subtotal);

                calculateGrandTotal();
            }

            // Fungsi untuk menonaktifkan part yang sudah dipilih
            function refreshPartOptions() {
                let selectedParts = [];
                $('.item-part').each(function() {
                    let val = $(this).val();
                    if (val) {
                        selectedParts.push(val);
                    }
                });

                $('.item-part').each(function() {
                    let currentDropdown = $(this);
                    let currentValue = currentDropdown.val();

                    currentDropdown.find('option').each(function() {
                        let option = $(this);
                        let optionValue = option.val();

                        if(optionValue) {
                           option.prop('disabled', false);
                        }

                        if (selectedParts.includes(optionValue) && optionValue !== currentValue) {
                           option.prop('disabled', true);
                        }
                    });
                });

                $('.select2-part').select2({
                    placeholder: "Pilih Part",
                    width: '100%'
                });
            }

            // Fungsi untuk menambah baris item baru
            $('#add-item-btn').on('click', function() {
                let template = $('#po-item-template').html().replace(/__INDEX__/g, itemIndex);
                let newRow = $(template);
                $('#po-items-table').append(newRow);

                newRow.find('.select2-part').select2({
                    placeholder: "Pilih Part",
                    width: '100%'
                });

                itemIndex++;
                refreshPartOptions();
            });

            // Event delegation untuk menghapus baris
            $('#po-items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
                calculateGrandTotal();
                refreshPartOptions();
            });

            // Event delegation untuk perubahan pada part, qty, atau harga
            $('#po-items-table').on('change input', '.item-part, .item-qty, .item-harga', function() {
                let row = $(this).closest('tr');

                if ($(this).hasClass('item-part')) {
                    let selectedPart = row.find('.item-part option:selected');
                    let defaultHarga = selectedPart.data('harga') || 0;
                    row.find('.item-harga').val(defaultHarga);
                    refreshPartOptions();
                }

                calculateRowSubtotal(row);
            });

            // Event listener untuk checkbox PPN
            $('#ppn-checkbox').on('change', calculateGrandTotal);

            // Tambahkan baris pertama secara otomatis
            $('#add-item-btn').click();
        });
    </script>
@stop
