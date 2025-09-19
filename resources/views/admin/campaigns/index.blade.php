@extends('adminlte::page')

@section('title', 'Manajemen Campaign')

@section('content_header')
    <h1>Manajemen Campaign</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Campaign Promosi</h3>
        <div class="card-tools">
            {{-- Tombol ini sekarang membuka modal baru kita --}}
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createCampaignModal">
                <i class="fas fa-plus"></i> Buat Campaign Baru
            </button>
        </div>
    </div>
    <div class="card-body">
        {{-- Notifikasi Error & Sukses --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Tabel Campaign (Struktur Baru) --}}
        <table id="campaigns-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Campaign</th>
                    <th>Tipe</th>
                    <th>Diskon (%)</th>
                    <th>Cakupan</th>
                    <th>Periode Aktif</th>
                    <th>Status</th>
                    <th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->nama_campaign }}</td>
                    <td><span class="badge badge-{{ $campaign->tipe == 'PENJUALAN' ? 'info' : 'warning' }}">{{ $campaign->tipe }}</span></td>
                    <td>{{ $campaign->discount_percentage }}%</td>
                    <td>
                        {{-- Logika untuk menampilkan cakupan --}}
                        @if($campaign->parts->isEmpty())
                            <span class="badge badge-light">Semua Part</span>
                        @else
                            <span class="badge badge-secondary">{{ $campaign->parts->count() }} Part</span>
                        @endif

                        @if($campaign->tipe == 'PEMBELIAN')
                            @if($campaign->suppliers->isEmpty())
                                <span class="badge badge-light">Semua Supplier</span>
                            @else
                                <span class="badge badge-dark">{{ $campaign->suppliers->count() }} Supplier</span>
                            @endif
                        @endif
                    </td>
                    <td>{{ $campaign->tanggal_mulai->format('d M Y') }} - {{ $campaign->tanggal_selesai->format('d M Y') }}</td>
                    <td>@if($campaign->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-danger">Non-Aktif</span>@endif</td>
                    <td>
                        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn btn-warning btn-xs">Edit</a>
                        <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">Belum ada campaign yang dibuat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= MODAL CREATE (Struktur Baru Total) ================= --}}
<div class="modal fade" id="createCampaignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Campaign Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="createForm" action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Baris 1: Info Dasar --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nama Campaign <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_campaign" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tipe Campaign <span class="text-danger">*</span></label>
                            <select class="form-control" name="tipe" id="campaignTypeSelector">
                                <option value="PENJUALAN">Penjualan</option>
                                <option value="PEMBELIAN">Pembelian</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Diskon Utama (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="discount_percentage" min="0" max="100" step="0.01" required>
                        </div>
                    </div>

                    {{-- Baris 2: Periode --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai" required>
                        </div>
                    </div>
                    <hr>

                    {{-- Baris 3: Cakupan Part --}}
                    <div class="form-group">
                        <label>Cakupan Part</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="applies_to_all_parts" id="allParts" value="1" checked>
                                <label class="form-check-label" for="allParts">Berlaku untuk Semua Part</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="applies_to_all_parts" id="specificParts" value="0">
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

                    {{-- ==================== BAGIAN KHUSUS PEMBELIAN ==================== --}}
                    <div id="purchaseFieldsContainer" style="display: none;">
                        <h5>Aturan Khusus Pembelian</h5>
                        <div class="form-group">
                        <label>Cakupan Supplier</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="allSuppliers" value="1" checked>
                                <label class="form-check-label" for="allSuppliers">Berlaku untuk Semua Supplier</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="specificSuppliers" value="0">
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

                    {{-- ==================== BAGIAN KHUSUS PENJUALAN ==================== --}}
                    <div id="salesFieldsContainer">
                         <h5>Aturan Khusus Penjualan (Kategori Diskon Tambahan)</h5>
                         <p class="text-muted small">
                            Buat kategori untuk memberikan diskon tambahan kepada grup konsumen tertentu. Diskon ini akan ditambahkan di atas diskon utama.
                         </p>
                         <div id="categoryRepeaterContainer">
                             {{-- Kategori dinamis akan ditambahkan di sini oleh JavaScript --}}
                         </div>
                         <button type="button" class="btn btn-sm btn-outline-success mt-2" id="addCategoryBtn">
                            <i class="fas fa-plus"></i> Tambah Kategori Diskon
                         </button>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template untuk Kategori Diskon (disembunyikan) --}}
<div id="categoryTemplate" style="display: none;">
    <div class="category-item border rounded p-3 mb-3">
        <button type="button" class="close" aria-label="Close" onclick="$(this).parent().remove();">
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Nama Kategori</label>
                <input type="text" class="form-control" name="categories[__INDEX__][nama]" placeholder="Cth: Bengkel Prioritas">
            </div>
            <div class="col-md-2 form-group">
                <label>Diskon (%)</label>
                <input type="number" class="form-control" name="categories[__INDEX__][diskon]" min="0" max="100" step="0.01">
            </div>
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
@section('plugins.Datatables', true)

@section('js')
<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    $('#campaigns-table').DataTable({ "responsive": true });

    // Inisialisasi Select2 pada form utama
    $('.select2').select2({
        dropdownParent: $('#createCampaignModal')
    });

    // --- LOGIKA FORM DINAMIS ---

    // 1. Mengatur tampilan berdasarkan TIPE campaign (Penjualan/Pembelian)
    function toggleCampaignTypeFields() {
        const selectedType = $('#campaignTypeSelector').val();
        if (selectedType === 'PEMBELIAN') {
            $('#purchaseFieldsContainer').slideDown();
            $('#salesFieldsContainer').slideUp();
        } else { // PENJUALAN
            $('#purchaseFieldsContainer').slideUp();
            $('#salesFieldsContainer').slideDown();
        }
    }

    // Panggil saat halaman dimuat & saat tipe diganti
    toggleCampaignTypeFields();
    $('#campaignTypeSelector').on('change', toggleCampaignTypeFields);

    // 2. Menampilkan/menyembunyikan pilihan PART
    $('input[name="applies_to_all_parts"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#partSelectionContainer').slideDown();
        } else {
            $('#partSelectionContainer').slideUp();
        }
    });

    // 3. Menampilkan/menyembunyikan pilihan SUPPLIER
    $('input[name="applies_to_all_suppliers"]').on('change', function() {
        if ($(this).val() === '0') {
            $('#supplierSelectionContainer').slideDown();
        } else {
            $('#supplierSelectionContainer').slideUp();
        }
    });

    // 4. Logika untuk menambah KATEGORI DISKON dinamis
    let categoryIndex = 0;
    $('#addCategoryBtn').on('click', function() {
        // Ambil template HTML
        let template = $('#categoryTemplate').html();
        // Ganti placeholder __INDEX__ dengan nomor unik
        template = template.replace(/__INDEX__/g, categoryIndex);

        // Tambahkan ke container
        $('#categoryRepeaterContainer').append(template);

        // Inisialisasi Select2 untuk elemen yang baru ditambahkan
        $('.select2-template').select2({
            placeholder: "Pilih konsumen...",
            dropdownParent: $('#createCampaignModal')
        }).removeClass('select2-template');

        categoryIndex++;
    });
});
</script>
@stop
