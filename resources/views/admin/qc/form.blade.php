@extends('adminlte::page')

@section('title', 'Proses Quality Control')

@section('content_header')
    <h1>Proses Quality Control</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.qc.store', $receiving->id) }}" method="POST" id="qc-form">
        @csrf
        <div class="card-header">
            <h3 class="card-title">No. Penerimaan: {{ $receiving->nomor_penerimaan }}</h3>
            <div class="card-tools">
                <span class="badge badge-secondary">Total Item: {{ $receiving->details->count() }}</span>
            </div>
        </div>
        <div class="card-body">
            {{-- Pesan Error --}}
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

            <p class="text-muted">
                Silakan input jumlah barang yang <b>Lolos</b> dan <b>Gagal</b>. <br>
                Tombol simpan hanya akan aktif jika kolom <b>Sisa Alokasi</b> pada semua baris bernilai <b>0</b> (Warna Hijau).
            </p>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="vertical-align: middle;">Part Info</th>
                            <th style="width: 100px; text-align: center; vertical-align: middle;">Qty<br>Diterima</th>
                            <th style="width: 120px; text-align: center; vertical-align: middle;" class="text-success">Qty<br>Lolos</th>
                            <th style="width: 120px; text-align: center; vertical-align: middle;" class="text-danger">Qty<br>Gagal</th>
                            <th style="width: 100px; text-align: center; vertical-align: middle;">Sisa<br>Alokasi</th>
                            <th style="vertical-align: middle;">Catatan QC</th>
                        </tr>
                    </thead>
                    <tbody id="qc-items-table">
                        @foreach($receiving->details as $detail)
                            {{-- PENTING: Jangan render baris jika qty 0 (Mencegah Error Ghost Data) --}}
                            @if($detail->qty_terima <= 0) 
                                @continue 
                            @endif

                            <tr class="qc-item-row">
                                <td style="vertical-align: middle;">
                                    <strong>{{ $detail->part->nama_part }}</strong>
                                    <br>
                                    <small class="text-muted">Kode: {{ $detail->part->kode_part }}</small>
                                </td>
                                <td style="vertical-align: middle;">
                                    <input type="number" class="form-control text-center qty-diterima" value="{{ $detail->qty_terima }}" readonly style="background-color: #e9ecef; font-weight: bold;">
                                </td>
                                <td style="vertical-align: middle;">
                                    <input type="number" name="items[{{ $detail->id }}][qty_lolos]" class="form-control text-center qty-lolos" min="0" value="{{ old('items.'.$detail->id.'.qty_lolos', 0) }}" required>
                                </td>
                                <td style="vertical-align: middle;">
                                    <input type="number" name="items[{{ $detail->id }}][qty_gagal]" class="form-control text-center qty-gagal" min="0" value="{{ old('items.'.$detail->id.'.qty_gagal', 0) }}" required>
                                </td>
                                <td style="vertical-align: middle;">
                                    {{-- Kolom Sisa untuk Validasi Visual --}}
                                    <input type="text" class="form-control text-center sisa font-weight-bold" value="{{ $detail->qty_terima }}" readonly tabindex="-1">
                                </td>
                                <td style="vertical-align: middle;">
                                    <input type="text" name="items[{{ $detail->id }}][catatan_qc]" class="form-control" placeholder="Keterangan (Opsional)" value="{{ old('items.'.$detail->id.'.catatan_qc') }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{-- Tombol Simpan dengan Status Dinamis --}}
            <button type="submit" id="submit-btn" class="btn btn-danger" disabled>
                <i class="fas fa-exclamation-circle mr-1"></i> Lengkapi Data Dulu
            </button>
            <a href="{{ route('admin.qc.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    /* Styling untuk status visual */
    .sisa-ok {
        background-color: #28a745 !important; /* Hijau */
        color: #fff !important;
        border-color: #28a745 !important;
    }
    .sisa-warning {
        background-color: #ffc107 !important; /* Kuning */
        color: #1f2d3d !important;
        border-color: #ffc107 !important;
    }
    .sisa-error {
        background-color: #dc3545 !important; /* Merah */
        color: #fff !important;
        border-color: #dc3545 !important;
    }
    /* Agar input number tidak punya panah up/down yang mengganggu */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    
    // Fungsi Validasi Per Baris
    function validateRow(row) {
        // Ambil nilai dan pastikan integer (default 0 jika kosong/NaN)
        let qtyDiterima = parseInt(row.find('.qty-diterima').val()) || 0;
        let qtyLolos = parseInt(row.find('.qty-lolos').val()) || 0;
        let qtyGagal = parseInt(row.find('.qty-gagal').val()) || 0;

        // Hitung sisa
        let totalInput = qtyLolos + qtyGagal;
        let sisa = qtyDiterima - totalInput;

        // Update tampilan kolom Sisa
        let sisaInput = row.find('.sisa');
        sisaInput.val(sisa);

        // Update warna kolom sisa
        sisaInput.removeClass('sisa-ok sisa-warning sisa-error');
        if (sisa === 0) {
            sisaInput.addClass('sisa-ok'); // Hijau (Benar)
        } else if (sisa > 0) {
            sisaInput.addClass('sisa-warning'); // Kuning (Kurang input)
        } else {
            sisaInput.addClass('sisa-error'); // Merah (Input kelebihan)
        }

        // Jalankan validasi global untuk cek tombol
        validateAllRows();
    }

    // Fungsi Validasi Seluruh Baris untuk Aktifkan Tombol
    function validateAllRows() {
        let allValid = true;
        let totalRows = 0;
        
        $('.qc-item-row').each(function() {
            totalRows++;
            // Baca nilai sisa langsung dari input yang sudah dihitung
            let sisa = parseInt($(this).find('.sisa').val());
            
            // Valid hanya jika sisa benar-benar 0 (bukan NaN, bukan string kosong)
            if (isNaN(sisa) || sisa !== 0) {
                allValid = false;
                return false; // Break loop jika ada 1 saja yang salah
            }
        });

        // Pastikan minimal ada 1 baris yang diproses
        if (totalRows === 0) allValid = false;

        // Update Tombol Simpan
        let btn = $('#submit-btn');
        if (allValid) {
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-save mr-1"></i> Simpan Hasil QC');
            btn.removeClass('btn-danger').addClass('btn-primary');
        } else {
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-exclamation-circle mr-1"></i> Cek Sisa Alokasi');
            btn.removeClass('btn-primary').addClass('btn-danger');
        }
    }

    // Event Listener: Jalankan validasi saat user mengetik
    $('#qc-items-table').on('input keyup change', '.qty-lolos, .qty-gagal', function() {
        // Cegah input negatif
        if($(this).val() < 0) $(this).val(0);
        
        validateRow($(this).closest('tr'));
    });

    // Inisialisasi validasi saat halaman pertama dimuat
    $('.qc-item-row').each(function() {
        validateRow($(this));
    });
});
</script>
@stop