@extends('adminlte::page')

@section('title', 'Buat Permintaan Mutasi')

@section('content_header')
    <h1>Buat Permintaan Mutasi Stok</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.stock-mutations.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Gudang Asal</label>
                    <select name="gudang_asal_id" id="gudang_asal_id" class="form-control select2" required>
                        @if(count($gudangsAsal) > 1)
                            <option value="" selected>Pilih Gudang Asal</option>
                        @endif
                        @foreach($gudangsAsal as $gudang)
                            <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Part yang akan dimutasi</label>
                    <select name="part_id" id="part_id" class="form-control select2" required disabled>
                        <option value="" selected>Pilih Gudang Asal Dahulu</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Rak Asal</label>
                    <select name="rak_asal_id" id="rak_asal_id" class="form-control select2" required disabled>
                        <option value="" selected>Pilih Part Dahulu</option>
                    </select>
                </div>
                 <div class="col-md-3 form-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" placeholder="Jumlah Mutasi" required min="1">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Gudang Tujuan</label>
                    <select name="gudang_tujuan_id" id="gudang_tujuan_id" class="form-control select2" required>
                        <option value="" disabled selected>Pilih Gudang Tujuan</option>
                        @foreach($gudangsTujuan as $gudang)
                            <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9 form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Keterangan (opsional)">
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

@section('plugins.Select2', true)

{{-- CSS Kustom untuk Select2 --}}
@push('css')
<style>
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
    $('.select2').select2();

    const gudangAsalSelect = $('#gudang_asal_id');
    const partSelect = $('#part_id');
    const rakAsalSelect = $('#rak_asal_id');

    gudangAsalSelect.on('change', function() {
        const gudangId = $(this).val();
        partSelect.prop('disabled', true).html('<option value="">Loading...</option>');
        rakAsalSelect.prop('disabled', true).html('<option value="">Pilih Part Dahulu</option>');

        if (!gudangId) {
            partSelect.html('<option value="">Pilih Gudang Asal Dahulu</option>');
            return;
        }

        const url = "{{ route('admin.api.gudang.parts-with-stock', ['gudang' => ':id']) }}".replace(':id', gudangId);
        $.getJSON(url, function(parts) {
            partSelect.prop('disabled', false).html('<option value="">Pilih Part</option>');
            if (parts.length > 0) {
                parts.forEach(function(part) {
                    partSelect.append(new Option(`${part.nama_part} (${part.kode_part})`, part.id));
                });
            } else {
                partSelect.html('<option value="">Tidak ada part bersedia</option>');
            }
        }).fail(function() {
            alert('Gagal memuat data part.');
            partSelect.html('<option value="">Error memuat part</option>');
        });
    });

    partSelect.on('change', function() {
        const partId = $(this).val();
        const gudangId = gudangAsalSelect.val();
        rakAsalSelect.prop('disabled', true).html('<option value="">Loading...</option>');

        if (!partId || !gudangId) {
            rakAsalSelect.html('<option value="">Pilih Part Dahulu</option>');
            return;
        }

        const url = "{{ route('admin.api.part.stock', ['part' => ':partId']) }}".replace(':partId', partId) + `?gudang_id=${gudangId}`;
        $.getJSON(url, function(response) {
            rakAsalSelect.prop('disabled', false).html('<option value="">Pilih Rak Asal</option>');
            if (response.stock_details && response.stock_details.length > 0) {
                response.stock_details.forEach(function(stock) {
                    let optionText = `${stock.rak.kode_rak} (Stok: ${stock.quantity})`;
                    let option = new Option(optionText, stock.rak.id);
                    $(option).data('max-qty', stock.quantity);
                    rakAsalSelect.append(option);
                });
            } else {
                 rakAsalSelect.html('<option value="">Tidak ada stok di rak</option>');
            }
        }).fail(function() {
            alert('Gagal memuat detail stok rak.');
            rakAsalSelect.html('<option value="">Error memuat rak</option>');
        });
    });

    rakAsalSelect.on('change', function(){
        const maxQty = $(this).find('option:selected').data('max-qty');
        if(maxQty) {
            $('input[name="jumlah"]').attr('max', maxQty);
        }
    });

    if (gudangAsalSelect.val()) {
        gudangAsalSelect.trigger('change');
    }
});
</script>
@stop
