@extends('adminlte::page')

@section('title', 'Buat Penjualan Baru')

@section('content_header')
    <h1>Buat Penjualan Baru (Manual)</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.penjualans.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
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
                
                {{-- INPUT MANUAL NAMA KONSUMEN --}}
                <div class="col-md-4 form-group">
                    <label for="nama_konsumen">Nama Konsumen <span class="text-danger">*</span></label>
                    <input type="text" 
                        class="form-control" 
                        id="nama_konsumen" 
                        name="nama_konsumen" 
                        placeholder="Masukkan Nama Pembeli / Toko" 
                        value="{{ old('nama_konsumen') }}" 
                        required>
                </div>

                <div class="col-md-4 form-group">
                    <label for="tanggal_jual">Tanggal Jual <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_jual" name="tanggal_jual" value="{{ old('tanggal_jual', date('Y-m-d')) }}" required>
                </div>
            </div>

            <hr>
            {{-- (SISA KODE VIEW SAMA PERSIS DENGAN SEBELUMNYA: BAGIAN PILIH PART, TABEL, TOTALAN, DLL) --}}
            {{-- ... --}}
            
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
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Part</th>
                            <th style="width: 15%;">Rak (Otomatis FIFO)</th>
                            <th style="width: 10%;">Qty</th>
                            <th style="width: 15%;">Harga Jual (Satuan)</th>
                            <th style="width: 15%;">Diskon Manual (Rp/Item)</th>
                            <th style="width: 15%;">Subtotal</th>
                            <th style="width: 5%;">Aksi</th>
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
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ppn-checkbox" name="use_ppn" value="1" checked>
                                        <label class="form-check-label" for="ppn-checkbox">PPN (11%)</label>
                                    </div>
                                </th>
                                <td class="text-right" id="pajak-text">Rp 0</td>
                            </tr>
                            <tr><th>Total Keseluruhan:</th><td class="text-right h4" id="total-text">Rp 0</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Hidden Inputs untuk Backend --}}
            <input type="hidden" name="subtotal" id="subtotal-input" value="0">
            <input type="hidden" name="total_diskon" id="diskon-input" value="0">
            <input type="hidden" name="pajak" id="pajak-input" value="0">
            <input type="hidden" name="total_harga" id="total-harga-input" value="0">
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Penjualan</button>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    let partsData = [];
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
            let hargaJual = parseFloat($(this).find('.harga-jual-input').val()) || 0;
            let qty = parseInt($(this).find('.qty-input').val()) || 0;
            let diskonPerItem = parseFloat($(this).find('.input-diskon').val()) || 0;

            if(diskonPerItem > hargaJual) {
                diskonPerItem = hargaJual;
                $(this).find('.input-diskon').val(hargaJual);
            }

            let hargaNet = hargaJual - diskonPerItem;
            let subtotalRow = hargaNet * qty;
            let diskonRow = diskonPerItem * qty;

            $(this).find('.subtotal-row-text').text(formatRupiah(subtotalRow));
            
            subtotal += subtotalRow;
            totalDiskon += diskonRow;
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
                    partSelector.append(`<option value="${part.id}">${part.kode_part} - ${part.nama_part} (Stok: ${part.total_stock})</option>`);
                });
            },
            error: function() { partSelector.html('<option>Gagal memuat part</option>'); }
        });
    });

    // Tombol Tambah Part
    $('#add-part-btn').on('click', function() {
        let partId = $('#part-selector').val();
        let qtyJual = parseInt($('#qty-selector').val());
        let gudangId = $('#gudang_id').val();
        // Hapus validasi Konsumen ID
        let namaKonsumen = $('#nama_konsumen').val();

        if (!partId || !qtyJual || qtyJual <= 0 || !namaKonsumen) {
            alert('Silakan lengkapi Gudang, Nama Konsumen, Part, dan Jumlah.');
            return;
        }

        if ($(`.part-row[data-part-id="${partId}"]`).length > 0) {
            alert('Part ini sudah ada di daftar.');
            return;
        }

        let selectedPart = partsData.find(p => p.id == partId);
        if (!selectedPart) return;

        $.ajax({
            url: `{{ route('admin.api.get-fifo-batches') }}`,
            data: { part_id: partId, gudang_id: gudangId },
            success: function(batches) {
                let sisaQty = qtyJual;
                let hargaJual = parseFloat(selectedPart.harga_jual);

                for (const batch of batches) {
                    if (sisaQty <= 0) break;
                    let qtyAmbil = Math.min(sisaQty, batch.quantity);

                    let newRowHtml = `
                        <tr class="part-row" data-part-id="${partId}">
                            <td>
                                ${selectedPart.kode_part} - ${selectedPart.nama_part}
                                <input type="hidden" name="items[${itemIndex}][part_id]" value="${partId}">
                                <input type="hidden" name="items[${itemIndex}][batch_id]" value="${batch.id}">
                            </td>
                            <td>${batch.rak.kode_rak}</td>
                            <td>
                                <input type="number" class="form-control qty-input" name="items[${itemIndex}][qty_jual]" value="${qtyAmbil}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control harga-jual-input" value="${hargaJual}" readonly>
                                <small class="text-muted">Rp ${formatRupiah(hargaJual)}</small>
                            </td>
                            <td>
                                <input type="number" name="items[${itemIndex}][diskon]" class="form-control input-diskon" placeholder="0" min="0" value="0">
                            </td>
                            <td class="text-right subtotal-row-text">${formatRupiah(hargaJual * qtyAmbil)}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-part-btn"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    
                    $('#parts-container').append(newRowHtml);
                    sisaQty -= qtyAmbil;
                    itemIndex++;
                }

                if (sisaQty > 0) {
                    alert(`Peringatan: Stok tidak cukup. ${sisaQty} unit gagal ditambahkan.`);
                }

                calculateTotal();
                $('#part-selector').val('').trigger('change');
                $('#qty-selector').val('');
            }
        });
    });

    $('#parts-container').on('click', '.remove-part-btn', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    $('#ppn-checkbox').on('change', calculateTotal);
    $(document).on('keyup change', '.input-diskon', calculateTotal);
});
</script>
@stop