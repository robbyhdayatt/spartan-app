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
            {{-- BLOK UNTUK MENAMPILKAN SEMUA JENIS ERROR (Sesuai Style Putaway) --}}
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

            <p class="text-muted">Silakan input jumlah barang yang <b>Lolos</b> dan <b>Gagal</b>. Pastikan kolom <b>Sisa</b> bernilai 0.</p>
            
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
                                <input type="number" class="form-control text-center sisa font-weight-bold" value="{{ $detail->qty_terima }}" readonly tabindex="-1">
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
            <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                <i class="fas fa-save mr-1"></i> Simpan Hasil QC
            </button>
            <a href="{{ route('admin.qc.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .sisa-ok {
        background-color: #28a745 !important; /* Hijau */
        color: #fff !important;
    }
    .sisa-warning {
        background-color: #ffc107 !important; /* Kuning */
        color: #1f2d3d !important;
    }
    .sisa-error {
        background-color: #dc3545 !important; /* Merah */
        color: #fff !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    function validateRow(row) {
        let qtyDiterima = parseInt(row.find('.qty-diterima').val()) || 0;
        let qtyLolos = parseInt(row.find('.qty-lolos').val()) || 0;
        let qtyGagal = parseInt(row.find('.qty-gagal').val()) || 0;
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    function validateRow(row) {
        let qtyDiterima = parseInt(row.find('.qty-diterima').val()) || 0;
        let qtyLolos = parseInt(row.find('.qty-lolos').val()) || 0;
        let qtyGagal = parseInt(row.find('.qty-gagal').val()) || 0;

        let totalInput = qtyLolos + qtyGagal;
        let sisa = qtyDiterima - totalInput;

        let sisaInput = row.find('.sisa');
        sisaInput.val(sisa);

        sisaInput.removeClass('sisa-ok sisa-warning sisa-error');

        if (sisa === 0) {
            sisaInput.addClass('sisa-ok');
        } else if (sisa > 0) {
            sisaInput.addClass('sisa-warning');
        } else {
            sisaInput.addClass('sisa-error');
        }

        validateAllRows();
    }

    function validateAllRows() {
        let allValid = true;
        
        $('.qc-item-row').each(function() {
            let sisa = parseInt($(this).find('.sisa').val());
            if (sisa !== 0) {
                allValid = false;
                return false;
            }
        });

        let btn = $('#submit-btn');
        if (allValid) {
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-save mr-1"></i> Simpan Hasil QC');
            btn.removeClass('btn-danger').addClass('btn-primary');
        } else {
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-exclamation-circle mr-1"></i> Lengkapi Alokasi');
            btn.removeClass('btn-primary').addClass('btn-danger');
        }
    }

    $('#qc-items-table').on('input keyup change', '.qty-lolos, .qty-gagal', function() {
        if($(this).val() < 0) $(this).val(0);
        
        validateRow($(this).closest('tr'));
    });

    $('.qc-item-row').each(function() {
        validateRow($(this));
    });
});
</script>
@stop