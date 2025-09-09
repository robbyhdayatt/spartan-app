@extends('adminlte::page')

@section('title', 'Proses Quality Control')

@section('content_header')
    <h1>Proses Quality Control</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.qc.store', $receiving->id) }}" method="POST">
        @csrf
        <div class="card-header">
            <h3 class="card-title">No. Penerimaan: {{ $receiving->nomor_penerimaan }}</h3>
        </div>
        <div class="card-body">
            {{-- PERBAIKAN: Tambahkan blok ini untuk menampilkan notifikasi --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Blok untuk error validasi (ini sudah benar) --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p>Periksa setiap item dan masukkan jumlah yang lolos dan gagal pengecekan kualitas.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th style="width: 120px">Qty Diterima</th>
                        <th style="width: 120px">Qty Lolos QC</th>
                        <th style="width: 120px">Qty Gagal QC</th>
                        <th style="width: 120px">Sisa</th>
                        <th>Catatan QC</th>
                    </tr>
                </thead>
                <tbody id="qc-items-table">
                    @foreach($receiving->details as $detail)
                    <tr class="qc-item-row">
                        <td>{{ $detail->part->nama_part }} ({{ $detail->part->kode_part }})</td>
                        <td>
                            <input type="number" class="form-control qty-diterima" value="{{ $detail->qty_terima }}" readonly>
                        </td>
                        <td>
                             <input type="number" name="items[{{ $detail->id }}][qty_lolos]" class="form-control qty-lolos" min="0" max="{{ $detail->qty_terima }}" value="{{ old('items.'.$detail->id.'.qty_lolos', $detail->qty_terima) }}" required>
                        </td>
                        <td>
                             <input type="number" name="items[{{ $detail->id }}][qty_gagal]" class="form-control qty-gagal" min="0" max="{{ $detail->qty_terima }}" value="{{ old('items.'.$detail->id.'.qty_gagal', 0) }}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control sisa" value="0" readonly>
                        </td>
                        <td>
                            <input type="text" name="items[{{ $detail->id }}][catatan_qc]" class="form-control" placeholder="Opsional">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" id="submit-btn" class="btn btn-primary">Simpan Hasil QC</button>
            <a href="{{ route('admin.qc.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Fungsi ini untuk memvalidasi satu baris saat diubah
    function validateRow(row) {
        let qtyDiterima = parseInt(row.find('.qty-diterima').val()) || 0;
        let qtyLolos = parseInt(row.find('.qty-lolos').val()) || 0;
        let qtyGagal = parseInt(row.find('.qty-gagal').val()) || 0;

        let totalInput = qtyLolos + qtyGagal;
        let sisa = qtyDiterima - totalInput;

        let sisaInput = row.find('.sisa');
        sisaInput.val(sisa);

        // Beri warna merah jika input berlebih (sisa negatif)
        if (sisa < 0) {
            sisaInput.addClass('is-invalid');
        } else {
            sisaInput.removeClass('is-invalid');
        }

        // Panggil fungsi validasi global setiap kali ada perubahan
        validateAllRows();
    }

    // Fungsi ini untuk memeriksa semua baris dan mengatur tombol Simpan
    function validateAllRows() {
        let isFormValid = true;
        $('.qc-item-row').each(function() {
            // Ambil nilai sisa dari setiap baris
            let sisa = parseInt($(this).find('.sisa').val());

            // PERBAIKAN: Form dianggap tidak valid HANYA JIKA sisa negatif (input berlebih)
            if (sisa < 0) {
                isFormValid = false;
                return false; // Hentikan loop jika sudah ketemu satu yang tidak valid
            }
        });

        // Aktifkan tombol jika form valid, nonaktifkan jika tidak
        $('#submit-btn').prop('disabled', !isFormValid);
    }

    // Event listener untuk setiap perubahan pada input lolos atau gagal
    $('#qc-items-table').on('input', '.qty-lolos, .qty-gagal', function() {
        validateRow($(this).closest('tr'));
    });

    // Jalankan validasi untuk semua baris saat halaman pertama kali dimuat
    $('.qc-item-row').each(function() {
        validateRow($(this));
    });
});
</script>
@stop

{{-- Tambahkan CSS untuk menyorot input yang salah --}}
@section('css')
<style>
    .is-invalid {
        background-color: #f8d7da !important;
        border-color: #f5c6cb !important;
    }
</style>
@stop
