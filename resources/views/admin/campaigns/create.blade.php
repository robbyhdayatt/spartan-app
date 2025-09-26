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

            {{-- Form di sini, tidak lagi di dalam modal --}}
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Nama Campaign <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_campaign" value="{{ old('nama_campaign') }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Tipe Campaign <span class="text-danger">*</span></label>
                    <select class="form-control" name="tipe" id="campaignTypeSelector">
                        <option value="PENJUALAN" {{ old('tipe') == 'PENJUALAN' ? 'selected' : '' }}>Penjualan</option>
                        <option value="PEMBELIAN" {{ old('tipe') == 'PEMBELIAN' ? 'selected' : '' }}>Pembelian</option>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Diskon Utama (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="discount_percentage" value="{{ old('discount_percentage') }}" min="0" max="100" step="0.01" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <label>Cakupan Part</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="allParts" value="1" {{ old('applies_to_all_parts', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allParts">Berlaku untuk Semua Part</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_parts" id="specificParts" value="0" {{ old('applies_to_all_parts') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="specificParts">Hanya untuk Part Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="partSelectionContainer" style="display: none;">
                <label>Pilih Part</label>
                <select name="part_ids[]" class="form-control select2" multiple="multiple" style="width: 100%;">
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">{{ $part->nama_part }} ({{$part->kode_part}})</option>
                    @endforeach
                </select>
            </div>
            <hr>
            <div id="purchaseFieldsContainer" style="display: none;">
                <h5>Aturan Khusus Pembelian</h5>
                <div class="form-group">
                <label>Cakupan Supplier</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="allSuppliers" value="1" {{ old('applies_to_all_suppliers', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allSuppliers">Berlaku untuk Semua Supplier</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="specificSuppliers" value="0" {{ old('applies_to_all_suppliers') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="specificSuppliers">Hanya untuk Supplier Tertentu</label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="supplierSelectionContainer" style="display: none;">
                <label>Pilih Supplier</label>
                <select name="supplier_ids[]" class="form-control select2" multiple="multiple" style="width: 100%;">
                     @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            </div>
            <div id="salesFieldsContainer">
                 <h5>Aturan Khusus Penjualan (Kategori Diskon Tambahan)</h5>
                 <p class="text-muted small">Buat kategori untuk memberikan diskon tambahan kepada grup konsumen tertentu.</p>
                 <div id="categoryRepeaterContainer"></div>
                 <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addCategoryBtn">
                    <i class="fas fa-plus"></i> Tambah Kategori Diskon
                 </button>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Campaign</button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

{{-- Template Kategori tetap sama, diletakkan di sini agar bisa diakses JS --}}
<div id="categoryTemplate" style="display: none;">
    <div class="category-item border rounded p-3 mb-3">
        <button type="button" class="close" aria-label="Close" onclick="$(this).parent().remove();"><span aria-hidden="true">&times;</span></button>
        <div class="row">
            <div class="col-md-4 form-group"><label>Nama Kategori</label><input type="text" class="form-control" name="categories[__INDEX__][nama]" placeholder="Cth: Bengkel Prioritas"></div>
            <div class="col-md-2 form-group"><label>Diskon (%)</label><input type="number" class="form-control" name="categories[__INDEX__][diskon]" min="0" max="100" step="0.01"></div>
            <div class="col-md-6 form-group">
                <label>Pilih Konsumen untuk Kategori ini</label>
                <select name="categories[__INDEX__][konsumen_ids][]" class="form-control select2-template" multiple="multiple" style="width: 100%;">
                    @foreach($konsumens as $konsumen)
                        <option value="{{ $konsumen->id }}">{{ $konsumen->nama_konsumen }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Select2', true)

@push('css')
<style>
    .select2-container .select2-selection--single { height: calc(2.25rem + 2px) !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 1.5 !important; padding-left: .75rem !important; padding-top: .375rem !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: calc(2.25rem + 2px) !important; }
</style>
@endpush

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi semua Select2 di halaman
    $('.select2').select2({ width: '100%' });

    let categoryIndex = 0;

    function toggleFields() {
        // Tipe Campaign
        if ($('#campaignTypeSelector').val() === 'PEMBELIAN') {
            $('#purchaseFieldsContainer').slideDown();
            $('#salesFieldsContainer').slideUp();
        } else {
            $('#purchaseFieldsContainer').slideUp();
            $('#salesFieldsContainer').slideDown();
        }
        // Cakupan Part
        if ($('input[name="applies_to_all_parts"]:checked').val() === '0') {
            $('#partSelectionContainer').slideDown();
        } else {
            $('#partSelectionContainer').slideUp();
        }
        // Cakupan Supplier
        if ($('input[name="applies_to_all_suppliers"]:checked').val() === '0') {
            $('#supplierSelectionContainer').slideDown();
        } else {
            $('#supplierSelectionContainer').slideUp();
        }
    }

    // Jalankan saat halaman dimuat
    toggleFields();

    // Jalankan saat ada perubahan
    $('#campaignTypeSelector, input[name="applies_to_all_parts"], input[name="applies_to_all_suppliers"]').on('change', toggleFields);

    // Event listener untuk tombol "Tambah Kategori Diskon"
    $('#addCategoryBtn').on('click', function() {
        categoryIndex = $('.category-item').length;
        let template = $('#categoryTemplate').html().replace(/__INDEX__/g, categoryIndex);
        let newCategory = $(template);

        $('#categoryRepeaterContainer').append(newCategory);

        newCategory.find('.select2-template').select2({
            placeholder: "Pilih konsumen...",
            width: '100%'
        }).removeClass('select2-template');
    });
});
</script>
@stop
