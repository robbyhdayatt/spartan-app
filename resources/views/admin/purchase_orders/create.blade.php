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
                        {{-- Logika baru: Cek jumlah gudang yang tersedia --}}
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
                <button type="button" class="btn btn-success btn-sm" id="add-item-btn">+ Tambah Item</button>
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
                <select class="form-control item-part select2 select2-part" name="items[__INDEX__][part_id]" required style="width: 100%;">
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

@section('js')
    <script>
        $(document).ready(function() {
            $('#createModal .select2').select2({ dropdownParent: $('#createModal') });
            $('#editModal .select2').select2({ dropdownParent: $('#editModal') });
            $('.select2').select2(); // inisialisasi awal

            let itemIndex = 0;

            // Fungsi untuk refresh semua dropdown part (disable part yg sudah dipilih)
            function refreshPartOptions() {
                // ambil semua part_id yang sudah dipilih
                let selectedParts = [];
                $('.item-part').each(function() {
                    let val = $(this).val();
                    if (val) selectedParts.push(val);
                });

                // reset semua option dulu (enable)
                $('.item-part option').prop('disabled', false);

                // disable option yang sudah dipilih di dropdown lain
                $('.item-part').each(function() {
                    let currentVal = $(this).val(); // biarkan tetap aktif di dropdown yg sedang dipakai
                    $(this).find('option').each(function() {
                        if (selectedParts.includes($(this).val()) && $(this).val() !== currentVal) {
                            $(this).prop('disabled', true);
                        }
                    });
                });

                // Refresh Select2 biar langsung kelihatan disabled
                $('.item-part').trigger('change.select2');
            }

            // Add a new item row
            $('#add-item-btn').on('click', function() {
                let template = $('#po-item-template').html().replace(/__INDEX__/g, itemIndex);
                let $row = $(template);

                // append row ke tabel
                $('#po-items-table').append($row);

                // aktifkan select2 pada dropdown part yang baru ditambahkan
                $row.find('.select2-part').select2({
                    width: '100%',
                    placeholder: "Pilih Part",
                    allowClear: true
                });

                itemIndex++;

                // refresh setelah tambah row
                refreshPartOptions();
            });

            // Remove an item row
            $('#po-items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
                refreshPartOptions();
            });

            // Update harga dan subtotal ketika part/qty/harga berubah
            $('#po-items-table').on('change keyup', '.item-part, .item-qty, .item-harga', function() {
                let row = $(this).closest('tr');
                let selectedPart = row.find('.item-part option:selected');
                let harga = parseFloat(row.find('.item-harga').val()) || 0;
                let qty = parseInt(row.find('.item-qty').val()) || 0;

                // Auto-fill harga beli jika kosong
                if ($(this).hasClass('item-part')) {
                    let defaultHarga = selectedPart.data('harga') || 0;
                    row.find('.item-harga').val(defaultHarga);
                    harga = defaultHarga;

                    // refresh opsi supaya part yg dipilih tidak bisa dipakai lagi di row lain
                    refreshPartOptions();
                }

                let subtotal = qty * harga;
                row.find('.item-subtotal').val(subtotal.toLocaleString('id-ID'));
            });
        });
    </script>
@stop
