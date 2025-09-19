@extends('adminlte::page')

@section('title', 'Edit Campaign')

@section('content_header')
    <h1>Edit Campaign: {{ $campaign->nama_campaign }}</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.campaigns.update', $campaign) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')
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

            {{-- Baris 1: Info Dasar --}}
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Nama Campaign <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_campaign" value="{{ old('nama_campaign', $campaign->nama_campaign) }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Tipe Campaign</label>
                    <input type="text" class="form-control" value="{{ $campaign->tipe }}" readonly>
                </div>
                <div class="col-md-2 form-group">
                    <label>Diskon Utama (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="discount_percentage" value="{{ old('discount_percentage', $campaign->discount_percentage) }}" min="0" max="100" step="0.01" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Status <span class="text-danger">*</span></label>
                     <select name="is_active" class="form-control">
                        <option value="1" @if(old('is_active', $campaign->is_active) == 1) selected @endif>Aktif</option>
                        <option value="0" @if(old('is_active', $campaign->is_active) == 0) selected @endif>Non-Aktif</option>
                    </select>
                </div>
            </div>

            {{-- Baris 2: Periode --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="{{ old('tanggal_mulai', $campaign->tanggal_mulai->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="{{ old('tanggal_selesai', $campaign->tanggal_selesai->format('Y-m-d')) }}" required>
                </div>
            </div>
            <hr>

            {{-- Baris 3: Cakupan Part --}}
            @php
                $appliesToAllParts = old('applies_to_all_parts', $campaign->parts->isEmpty());
                $selectedPartIds = old('part_ids', $campaign->parts->pluck('id')->toArray());
            @endphp
            <div class="form-group">
                <label>Cakupan Part</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="allParts" value="1" @if($appliesToAllParts) checked @endif>
                        <label class="form-check-label" for="allParts">Berlaku untuk Semua Part</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="specificParts" value="0" @if(!$appliesToAllParts) checked @endif>
                        <label class="form-check-label" for="specificParts">Hanya untuk Part Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="partSelectionContainer" style="display: {{ $appliesToAllParts ? 'none' : 'block' }};">
                <label>Pilih Part</label>
                <select name="part_ids[]" class="form-control select2" multiple="multiple" style="width: 100%;">
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}" @if(in_array($part->id, $selectedPartIds)) selected @endif>{{ $part->nama_part }} ({{$part->kode_part}})</option>
                    @endforeach
                </select>
            </div>
            <hr>

            {{-- Bagian Khusus Pembelian atau Penjualan --}}
            @if($campaign->tipe === 'PEMBELIAN')
                 @include('admin.campaigns.partials.edit_purchase_fields')
            @else
                 @include('admin.campaigns.partials.edit_sales_fields')
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Campaign</button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2();

    // Menampilkan/menyembunyikan pilihan PART
    $('input[name="applies_to_all_parts"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#partSelectionContainer').slideDown();
        } else {
            $('#partSelectionContainer').slideUp();
        }
    });

    // Menampilkan/menyembunyikan pilihan SUPPLIER
    $('input[name="applies_to_all_suppliers"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#supplierSelectionContainer').slideDown();
        } else {
            $('#supplierSelectionContainer').slideUp();
        }
    });

    // Logika untuk menambah KATEGORI DISKON dinamis
    let categoryIndex = {{ $campaign->categories->count() }}; // Mulai dari jumlah kategori yang sudah ada
    $('#addCategoryBtn').on('click', function() {
        let template = $('#categoryTemplate').html().replace(/__INDEX__/g, categoryIndex);
        $('#categoryRepeaterContainer').append(template);
        $('.select2-template').select2({ placeholder: "Pilih konsumen..." }).removeClass('select2-template');
        categoryIndex++;
    });
});
</script>
@stop
