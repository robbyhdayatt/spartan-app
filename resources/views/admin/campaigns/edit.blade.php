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

            {{-- Info Dasar --}}
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Nama Campaign <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_campaign" value="{{ old('nama_campaign', $campaign->nama_campaign) }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tipe Campaign</label>
                    <input type="text" class="form-control" value="{{ $campaign->tipe }}" readonly>
                </div>
                <div class="col-md-2 form-group">
                    <label>Diskon (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="discount_percentage" value="{{ old('discount_percentage', $campaign->discount_percentage) }}" required min="0" max="100" step="0.01">
                </div>
            </div>

            {{-- Periode --}}
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="{{ old('tanggal_mulai', $campaign->tanggal_mulai->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="{{ old('tanggal_selesai', $campaign->tanggal_selesai->format('Y-m-d')) }}" required>
                </div>
            </div>

            <hr>
            <h5><strong>Aturan Cakupan Campaign</strong></h5>

            {{-- Cakupan Supplier (Hanya untuk Tipe Pembelian) --}}
            <div id="purchaseFieldsContainer" style="{{ $campaign->tipe !== 'PEMBELIAN' ? 'display: none;' : '' }}">
                <div class="form-group">
                    <label>Cakupan Supplier</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="allSuppliers" value="1" {{ $campaign->suppliers->isEmpty() ? 'checked' : '' }}>
                            <label class="form-check-label" for="allSuppliers">Berlaku untuk Semua Supplier</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="selectSuppliers" value="0" {{ !$campaign->suppliers->isEmpty() ? 'checked' : '' }}>
                            <label class="form-check-label" for="selectSuppliers">Pilih Supplier Tertentu</label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="supplierSelectionContainer" style="{{ $campaign->suppliers->isEmpty() ? 'display: none;' : '' }}">
                    <label for="supplier_ids">Pilih Supplier</label>
                    <select name="supplier_ids[]" id="supplier_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $campaign->suppliers->contains($supplier->id) ? 'selected' : '' }}>{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Cakupan Konsumen (Hanya untuk Tipe Penjualan) --}}
            <div id="salesFieldsContainer" style="{{ $campaign->tipe !== 'PENJUALAN' ? 'display: none;' : '' }}">
                 <div class="form-group">
                    <label>Cakupan Konsumen</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applies_to_all_konsumens" id="allKonsumens" value="1" {{ $campaign->konsumens->isEmpty() ? 'checked' : '' }}>
                            <label class="form-check-label" for="allKonsumens">Berlaku untuk Semua Konsumen</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="applies_to_all_konsumens" id="selectKonsumens" value="0" {{ !$campaign->konsumens->isEmpty() ? 'checked' : '' }}>
                            <label class="form-check-label" for="selectKonsumens">Pilih Konsumen Tertentu</label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="konsumenSelectionContainer" style="{{ $campaign->konsumens->isEmpty() ? 'display: none;' : '' }}">
                    <label for="konsumen_ids">Pilih Konsumen</label>
                    <select name="konsumen_ids[]" id="konsumen_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                        @foreach($konsumens as $konsumen)
                            <option value="{{ $konsumen->id }}" {{ $campaign->konsumens->contains($konsumen->id) ? 'selected' : '' }}>{{ $konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Cakupan Part (Berlaku untuk semua tipe) --}}
            <div class="form-group">
                <label>Cakupan Part</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="allParts" value="1" {{ $campaign->parts->isEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="allParts">Berlaku untuk Semua Part</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="selectParts" value="0" {{ !$campaign->parts->isEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label" for="selectParts">Pilih Part Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="partSelectionContainer" style="{{ $campaign->parts->isEmpty() ? 'display: none;' : '' }}">
                <label for="part_ids">Pilih Part</label>
                <select name="part_ids[]" id="part_ids" class="form-control select2" multiple="multiple" style="width: 100%;">
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}" {{ $campaign->parts->contains($part->id) ? 'selected' : '' }}>{{ $part->kode_part }} - {{ $part->nama_part }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer text-right">
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
    $('.select2').select2({
        placeholder: "Pilih item...",
        allowClear: true,
        width: '100%'
    });

    $('input[name="applies_to_all_suppliers"]').on('change', function() {
        $('#supplierSelectionContainer').slideToggle($(this).val() === '0');
    });

    $('input[name="applies_to_all_konsumens"]').on('change', function() {
        $('#konsumenSelectionContainer').slideToggle($(this).val() === '0');
    });

    $('input[name="applies_to_all_parts"]').on('change', function() {
        $('#partSelectionContainer').slideToggle($(this).val() === '0');
    });
});
</script>
@stop
