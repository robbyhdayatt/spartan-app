@extends('adminlte::page')

@section('title', 'Buat Permintaan Mutasi')

@section('content_header')
    <h1>Buat Permintaan Mutasi Gudang</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.stock-mutations.store') }}" method="POST">
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
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                <div class="form-group">
                    <label>Part</label>
                    <select name="part_id" class="form-control" required>
                        <option value="" disabled selected>Pilih Part</option>
                        @foreach ($parts as $part)
                            <option value="{{ $part->id }}">{{ $part->nama_part }} ({{ $part->kode_part }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                    <label>Gudang Asal</label>
                        @if(count($gudangsAsal) === 1)
                            <input type="text" class="form-control" value="{{ $gudangsAsal->first()->nama_gudang }}" readonly>
                            <input type="hidden" id="gudang-asal" name="gudang_asal_id" value="{{ $gudangsAsal->first()->id }}">
                        @else
                            <select id="gudang-asal" name="gudang_asal_id" class="form-control" required>
                                <option value="" disabled selected>Pilih Gudang Asal</option>
                                @foreach($gudangsAsal as $gudang)
                                    <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Rak Asal</label>
                        <select id="rak-asal" name="rak_asal_id" class="form-control" required>
                            <option value="" disabled selected>Pilih Gudang Asal Dahulu</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Gudang Tujuan</label>
                        <select name="gudang_tujuan_id" class="form-control" required>
                            <option value="" disabled selected>Pilih Gudang Tujuan</option>
                            @foreach ($gudangs as $gudang)
                                <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Pemenuhan stok gudang tujuan"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Ajukan Permintaan</for>
                    <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Function to load raks
    function loadRaks(gudangId) {
        let rakSelect = $('#rak-asal');
        rakSelect.html('<option value="" disabled selected>Loading...</option>');

        if (gudangId) {
            let url = `/admin/api/gudangs/${gudangId}/raks`;
            $.getJSON(url, function(data) {
                rakSelect.empty().html('<option value="" disabled selected>Pilih Rak Asal</option>');
                if(data.length > 0) {
                    data.forEach(function(rak) {
                        rakSelect.append(new Option(rak.nama_rak + ' (' + rak.kode_rak + ')', rak.id));
                    });
                } else {
                     rakSelect.html('<option value="" disabled selected>Tidak ada rak di gudang ini</option>');
                }
            });
        } else {
            rakSelect.html('<option value="" disabled selected>Pilih Gudang Asal Dahulu</option>');
        }
    }

    // Event listener for gudang change
    $('#gudang-asal').on('change', function() {
        loadRaks($(this).val());
    });

    // Auto-load raks if gudang is pre-filled/locked
    if ($('#gudang-asal').val()) {
        loadRaks($('#gudang-asal').val());
    }
});
</script>
@stop
