@extends('adminlte::page')

@section('title', 'Buat Campaign Baru')

@section('content_header')
    <h1>Buat Campaign Baru</h1>
@stop

@section('content')
<div class="card">
    <form id="createForm" action="{{ route('admin.campaigns.store') }}" method="POST">
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

            {{-- Baris 1: Info Dasar --}}
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Nama Campaign <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_campaign" value="{{ old('nama_campaign') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tipe Campaign <span class="text-danger">*</span></label>
                    <select class="form-control" name="tipe">
                        <option value="PENJUALAN" {{ old('tipe') == 'PENJUALAN' ? 'selected' : '' }}>Penjualan</option>
                        <option value="PEMBELIAN" {{ old('tipe') == 'PEMBELIAN' ? 'selected' : '' }}>Pembelian</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Diskon (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" required min="0" max="100" step="0.01">
                </div>
            </div>

            {{-- Baris 2: Periode --}}
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                </div>
            </div>

            <hr>
            <h5><strong>Aturan Cakupan Campaign</strong></h5>

            {{-- Cakupan Supplier --}}
            <div class="form-group">
                <label>Cakupan Supplier</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="allSuppliers" value="1" checked>
                        <label class="form-check-label" for="allSuppliers">Berlaku untuk Semua Supplier</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="selectSuppliers" value="0">
                        <label class="form-check-label" for="selectSuppliers">Pilih Supplier Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="supplierSelectionContainer" style="display: none;">
                <label for="supplier_ids">Pilih Supplier</label>
                <select name="supplier_ids[]" id="supplier_ids" class="form-control select2" multiple="multiple">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cakupan Part --}}
            <div class="form-group">
                <label>Cakupan Part</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="allParts" value="1" checked>
                        <label class="form-check-label" for="allParts">Berlaku untuk Semua Part</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="selectParts" value="0">
                        <label class="form-check-label" for="selectParts">Pilih Part Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="partSelectionContainer" style="display: none;">
                <label for="part_ids">Pilih Part</label>
                <select name="part_ids[]" id="part_ids" class="form-control select2" multiple="multiple">
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->kode_part }} - {{ $part->nama_part }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Campaign</button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi Select2 dengan lebar 100%
    $('.select2').select2({
        placeholder: "Pilih item...",
        allowClear: true,
        width: '100%' // <-- TAMBAHKAN BARIS INI
    });

    $('input[name="applies_to_all_suppliers"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#supplierSelectionContainer').slideDown();
        } else {
            $('#supplierSelectionContainer').slideUp();
            $('#supplier_ids').val(null).trigger('change');
        }
    });

    $('input[name="applies_to_all_parts"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#partSelectionContainer').slideDown();
        } else {
            $('#partSelectionContainer').slideUp();
            $('#part_ids').val(null).trigger('change');
        }
    });
});
</script>
@stop