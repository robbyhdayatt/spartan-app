@extends('adminlte::page')

@section('title', 'Buat Adjusment Stok')

@section('content_header')
    <h1>Buat Permintaan Adjusment Stok</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.stock-adjustments.store') }}" method="POST">
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
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Gudang</label>
                    @if(count($gudangs) === 1)
                        <input type="text" class="form-control" value="{{ $gudangs->first()->nama_gudang }}" readonly>
                        <input type="hidden" id="gudang_id" name="gudang_id" value="{{ $gudangs->first()->id }}">
                    @else
                        <select name="gudang_id" id="gudang_id" class="form-control" required>
                            <option value="" disabled selected>Pilih Gudang</option>
                            @foreach($gudangs as $gudang)
                                <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-md-6 form-group">
                    <label>Rak</label>
                    <select name="rak_id" id="rak_id" class="form-control" required>
                        <option value="">-- Pilih Gudang Terlebih Dahulu --</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Part</label>
                <select name="part_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Part</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->nama_part }} ({{$part->kode_part}})</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tipe Adjusment</label>
                    <select name="tipe" class="form-control" required>
                        <option value="TAMBAH">Penambahan (+)</option>
                        <option value="KURANG">Pengurangan (-)</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Alasan</label>
                <textarea name="alasan" class="form-control" rows="3" required placeholder="Contoh: Hasil stock opname, barang rusak, dll."></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Ajukan Permintaan</button>
            <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    function fetchRaks(gudangId) {
        if (gudangId) {
            $.ajax({
                url: `/spartan/admin/api/gudangs/${gudangId}/raks`, // Sesuaikan URL jika perlu
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#rak_id').empty().append('<option value="">-- Pilih Rak --</option>');
                    $.each(data, function(key, value) {
                        $('#rak_id').append('<option value="' + value.id + '">' + value.nama_rak + '</option>');
                    });
                }
            });
        } else {
            $('#rak_id').empty().append('<option value="">-- Pilih Gudang Terlebih Dahulu --</option>');
        }
    }

    // Event listener untuk dropdown gudang
    $('#gudang_id').on('change', function() {
        fetchRaks($(this).val());
    });

    // Panggil saat halaman dimuat jika gudang sudah terpilih (untuk user non-admin)
    if ($('#gudang_id').val()) {
        fetchRaks($('#gudang_id').val());
    }
});
</script>
@stop
