@extends('adminlte::page')

@section('title', 'Buat Permintaan Mutasi')

@section('plugins.Select2', true)

@section('content_header')
    <h1>Buat Permintaan Mutasi Stok</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.stock-mutations.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Gudang Asal</label>
                    @php
                        $user = Auth::user();
                        $isGudangSpecific = !empty($user->gudang_id) && $gudangsAsal->count() === 1;
                    @endphp

                    @if ($isGudangSpecific)
                        <input type="text" class="form-control" value="{{ $user->gudang->nama_gudang }}" readonly>
                        <input type="hidden" id="gudang_asal_id" name="gudang_asal_id" value="{{ $user->gudang_id }}">
                    @else
                        <select name="gudang_asal_id" id="gudang_asal_id" class="form-control select2" required>
                            <option value="" selected>Pilih Gudang Asal</option>
                            @foreach ($gudangsAsal as $gudang)
                                <option value="{{ $gudang->id }}" {{ old('gudang_asal_id') == $gudang->id ? 'selected' : '' }}>{{ $gudang->nama_gudang }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-md-4 form-group">
                    <label>Part yang akan dimutasi</label>
                    <select name="part_id" id="part_id" class="form-control select2" required disabled>
                        <option value="" selected>Pilih Gudang Asal Dahulu</option>
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label>Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" class="form-control" placeholder="Jumlah Mutasi" required min="1" value="{{ old('jumlah') }}">
                    <small id="stock-info" class="form-text text-muted"></small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Gudang Tujuan</label>
                    <select name="gudang_tujuan_id" id="gudang_tujuan_id" class="form-control select2" required>
                        <option value="" disabled selected>Pilih Gudang Tujuan</option>
                        @foreach ($gudangsTujuan as $gudang)
                            <option value="{{ $gudang->id }}" {{ old('gudang_tujuan_id') == $gudang->id ? 'selected' : '' }}>{{ $gudang->nama_gudang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Keterangan (opsional)" value="{{ old('keterangan') }}">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Buat Permintaan</button>
            <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@push('js')
<script>
$(document).ready(function() {
    $('.select2').select2();

    const gudangAsalSelect = $('#gudang_asal_id');
    const partSelect = $('#part_id');
    const jumlahInput = $('#jumlah');
    const stockInfo = $('#stock-info');

    function fetchParts() {
        const gudangId = gudangAsalSelect.val();
        partSelect.prop('disabled', true).html('<option value="">Loading...</option>');
        stockInfo.text('');
        jumlahInput.removeAttr('max');

        if (!gudangId) {
            partSelect.html('<option value="">Pilih Gudang Asal Dahulu</option>');
            return;
        }

        const url = "{{ url('admin/api/gudangs') }}/" + gudangId + "/parts-with-stock";
        $.getJSON(url, function(parts) {
            partSelect.prop('disabled', false).html('<option value="">Pilih Part</option>');
            if (parts.length > 0) {
                parts.forEach(function(part) {
                    partSelect.append(new Option(`${part.nama_part} (${part.kode_part})`, part.id));
                });
            } else {
                partSelect.html('<option value="">Tidak ada part tersedia</option>');
            }
        }).fail(function() {
            alert('Gagal memuat data part.');
            partSelect.html('<option value="">Error memuat part</option>');
        });
    }

    function fetchStock() {
        const partId = partSelect.val();
        const gudangId = gudangAsalSelect.val();
        stockInfo.text('');
        jumlahInput.removeAttr('max');

        if (!partId || !gudangId) return;

        const url = `{{ route('admin.api.part.stock-details') }}?gudang_id=${gudangId}&part_id=${partId}`;
        $.getJSON(url, function(data) {
            if (data.total_stock) {
                stockInfo.text(`Stok tersedia: ${data.total_stock}`);
                jumlahInput.attr('max', data.total_stock);
            } else {
                stockInfo.text('Stok tidak ditemukan.');
            }
        }).fail(function() {
            stockInfo.text('Gagal memuat info stok.');
        });
    }

    gudangAsalSelect.on('change', fetchParts);
    partSelect.on('change', fetchStock);

    // Jika ada gudang asal yang sudah terpilih saat halaman dimuat
    if (gudangAsalSelect.val()) {
        fetchParts();
    }
});
</script>
@endpush
