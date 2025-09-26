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
                        <select class="form-control select2" id="supplier-select" name="supplier_id" required style="width: 100%;">
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
                                <th style="width: 200px">Harga Beli</th>
                                <th style="width: 200px">Subtotal</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody id="po-items-table">
                            {{-- Items will be added here by JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-success btn-sm" id="add-item-btn" disabled>+ Tambah Item</button>

                {{-- Total Calculation --}}
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
                                        <input class="form-check-input" type="checkbox" id="ppn-checkbox" name="use_ppn" value="1">
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
                <button type="submit" class="btn btn-primary">Simpan Purchase Order</button>
                 <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    {{-- Hidden template for a new row --}}
    <template id="po-item-template">
        <tr>
            <td>
                <select class="form-control item-part" name="items[__INDEX__][part_id]" required style="width: 100%;">
                    <option value="" disabled selected>Pilih Part</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->nama_part }} ({{ $part->kode_part }})</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" class="form-control item-qty" name="items[__INDEX__][qty]" min="1" value="1" required></td>
            <td>
                <input type="text" class="form-control item-harga text-right" name="items[__INDEX__][harga_display]" readonly>
                <small class="form-text text-muted price-info"></small>
            </td>
            <td><input type="text" class="form-control item-subtotal text-right" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">&times;</button></td>
        </tr>
    </template>
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
            // Inisialisasi Select2 untuk header
            $('.select2').select2({ placeholder: "Pilih Opsi" });

            let itemIndex = 0;
            const addItemBtn = $('#add-item-btn');
            const supplierSelect = $('#supplier-select');
            const itemsTable = $('#po-items-table');

            const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);

            // Aktifkan tombol "Tambah Item" hanya jika Supplier sudah dipilih
            supplierSelect.on('change', function() {
                if ($(this).val()) {
                    addItemBtn.prop('disabled', false);
                    itemsTable.empty(); // Reset item jika supplier diganti
                    calculateAll();
                } else {
                    addItemBtn.prop('disabled', true);
                }
            });

            // Tambah baris item baru
            addItemBtn.on('click', function() {
                let template = $('#po-item-template').html().replace(/__INDEX__/g, itemIndex);
                itemsTable.append(template);
                itemsTable.find('tr').last().find('.item-part').select2({ placeholder: "Pilih Part" });
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
                let supplierId = supplierSelect.val();
                let hargaDisplay = row.find('.item-harga');
                let priceInfo = row.find('.price-info');

                priceInfo.text('Loading harga...');
                hargaDisplay.val('');

                if (!partId || !supplierId) return;

                let url = "{{ route('admin.api.part.purchase-details', ['part' => ':partId']) }}"
                    .replace(':partId', partId) + `?supplier_id=${supplierId}`;

                $.getJSON(url, function(response) {
                    const discount = response.discount_result;
                    hargaDisplay.val(formatRupiah(discount.final_price));

                    if (discount.applied_discounts.length > 0) {
                        priceInfo.html(`Asli: <del>${formatRupiah(discount.original_price)}</del> <br> <span class="text-success">${discount.applied_discounts.join(', ')}</span>`);
                    } else {
                        priceInfo.text('');
                    }
                    updateSubtotal(row);
                }).fail(() => {
                    alert('Gagal memuat detail harga part.');
                    priceInfo.text('Error');
                });
            });

            // Update subtotal saat qty berubah
            itemsTable.on('keyup change', '.item-qty', function() {
                updateSubtotal($(this).closest('tr'));
            });

            function updateSubtotal(row) {
                let hargaText = row.find('.item-harga').val().replace(/[^0-9,-]+/g,"").replace(',','.');
                let harga = parseFloat(hargaText) || 0;
                let qty = parseInt(row.find('.item-qty').val()) || 0;
                row.find('.item-subtotal').val(formatRupiah(qty * harga));
                calculateAll();
            }

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
        });
    </script>
@stop
