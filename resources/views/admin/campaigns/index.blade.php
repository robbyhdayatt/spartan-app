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
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">Buat Campaign Baru</button>
        </div>
    </div>
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
         @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table id="campaigns-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Campaign</th>
                    <th>Tipe</th>
                    <th>Part</th>
                    <th>Harga Promo</th>
                    <th>Periode Aktif</th>
                    <th>Status</th>
                    <th style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->nama_campaign }}</td>
                    <td>{{ $campaign->tipe }}</td>
                    <td>{{ $campaign->part->nama_part ?? 'N/A' }}</td>
                    <td>Rp {{ number_format($campaign->harga_promo, 0, ',', '.') }}</td>
                    <td>{{ $campaign->tanggal_mulai->format('d M Y') }} - {{ $campaign->tanggal_selesai->format('d M Y') }}</td>
                    <td>@if($campaign->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-danger">Non-Aktif</span>@endif</td>
                    <td>
                        <button class="btn btn-warning btn-xs edit-btn" data-campaign='@json($campaign)' data-toggle="modal" data-target="#editModal">Edit</button>
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

{{-- ================= MODAL CREATE ================= --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Campaign Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="createForm" action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Campaign</label>
                        <input type="text" class="form-control" name="nama_campaign" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Campaign</label>
                        <select class="form-control campaign-type-select" name="tipe">
                            <option value="PENJUALAN">Penjualan</option>
                            <option value="PEMBELIAN">Pembelian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Part</label>
                        <select name="part_id" class="form-control part-select" required style="width: 100%;">
                            <option></option> {{-- Placeholder for Select2 --}}
                            @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-default-sell-price="{{ $part->harga_jual_default }}" data-default-buy-price="{{ $part->harga_beli_default }}">{{ $part->nama_part }} ({{$part->kode_part}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Promo (Rp)</label>
                        <input type="number" class="form-control" name="harga_promo" min="0" required>
                        <small class="form-text text-muted default-price-info"></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Campaign</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                     <div class="form-group">
                        <label>Nama Campaign</label>
                        <input type="text" class="form-control" id="edit_nama_campaign" name="nama_campaign" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Campaign</label>
                        <select class="form-control campaign-type-select" id="edit_tipe" name="tipe">
                            <option value="PENJUALAN">Penjualan</option>
                            <option value="PEMBELIAN">Pembelian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Part</label>
                        <select name="part_id" id="edit_part_id" class="form-control part-select" required style="width: 100%;">
                            @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-default-sell-price="{{ $part->harga_jual_default }}" data-default-buy-price="{{ $part->harga_beli_default }}">{{ $part->nama_part }} ({{$part->kode_part}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Promo (Rp)</label>
                        <input type="number" class="form-control" id="edit_harga_promo" name="harga_promo" min="0" required>
                        <small class="form-text text-muted default-price-info"></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" class="form-control" id="edit_tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_is_active" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('plugins.Select2', true)
@section('plugins.Datatables', true)

@section('js')
<script>
$(document).ready(function() {
    // --- FUNGSI UTAMA ---
    function showDefaultPrice(selectElement, priceType) {
        const selectedOption = $(selectElement).find('option:selected');
        const priceInfo = $(selectElement).closest('.modal-body').find('.default-price-info');

        if (!selectedOption.val()) {
            priceInfo.text('');
            return;
        }

        const defaultPrice = priceType === 'PEMBELIAN' ? selectedOption.data('default-buy-price') : selectedOption.data('default-sell-price');
        const priceLabel = priceType === 'PEMBELIAN' ? 'Harga beli default: Rp ' : 'Harga jual default: Rp ';

        if (defaultPrice !== undefined) {
            priceInfo.text(priceLabel + new Intl.NumberFormat('id-ID').format(defaultPrice));
        } else {
            priceInfo.text('');
        }
    }

    // --- PERBAIKAN DATATABLE ---
    // Inisialisasi DataTable dengan error handling
    try {
        // Cek struktur tabel sebelum inisialisasi DataTable
        const $table = $('#campaigns-table');
        const headerCols = $table.find('thead tr th').length;
        
        // Validasi semua row memiliki jumlah kolom yang sama
        let isTableValid = true;
        $table.find('tbody tr').each(function() {
            const bodyCols = $(this).find('td').length;
            if (bodyCols !== headerCols) {
                console.warn('Row memiliki jumlah kolom berbeda:', bodyCols, 'vs', headerCols);
                isTableValid = false;
            }
        });
        
        if (isTableValid) {
            $table.DataTable({ 
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": -1 } // Disable sorting pada kolom aksi
                ]
            });
        } else {
            console.warn('Tabel tidak valid untuk DataTable, skip inisialisasi');
        }
    } catch (e) {
        console.error('Error inisialisasi DataTable:', e);
    }

    // --- INISIALISASI SELECT2 ---
    
    // **SOLUSI UTAMA: Inisialisasi Select2 langsung tanpa kompleksitas berlebih**
    
    // Tunggu sebentar untuk memastikan DOM siap
    setTimeout(function() {
        // Inisialisasi Edit Modal Select2
        $('#editModal .part-select').select2({
            placeholder: "Pilih Part",
            allowClear: true,
            dropdownParent: $('#editModal'),
            width: '100%'
        });
        
        // **INISIALISASI CREATE MODAL SELECT2 LANGSUNG**
        $('#createModal .part-select').select2({
            placeholder: "Pilih Part", 
            allowClear: true,
            dropdownParent: $('#createModal'),
            width: '100%'
        });
        
        console.log('Kedua Select2 sudah diinisialisasi');
        
        // Test trigger untuk create modal
        const createSelect = $('#createModal .part-select');
        const createType = $('#createModal .campaign-type-select');
        if (createSelect.val()) {
            showDefaultPrice(createSelect[0], createType.val());
        }
        
    }, 300);

    // --- EVENT LISTENERS ---
    
    // Event untuk perubahan tipe campaign
    $(document).on('change', '.campaign-type-select', function() {
        const partSelect = $(this).closest('.modal-body').find('.part-select');
        const priceType = $(this).val();
        showDefaultPrice(partSelect[0], priceType);
        console.log('Tipe campaign berubah ke:', priceType);
    });

    // Event untuk perubahan part  
    $(document).on('change', '.part-select', function() {
        const priceType = $(this).closest('.modal-body').find('.campaign-type-select').val();
        showDefaultPrice(this, priceType);
        console.log('Part berubah, tipe:', priceType);
    });

    // --- MODAL CREATE EVENTS ---
    
    $(document).on('show.bs.modal', '#createModal', function() {
        console.log('Create modal akan dibuka...');
        
        // Reset form
        const form = $(this).find('form')[0];
        form.reset();
        $(this).find('.default-price-info').text('');
        
        // Pastikan Select2 ada dan reset
        const partSelect = $(this).find('.part-select');
        if (partSelect.data('select2')) {
            partSelect.val(null).trigger('change');
        }
    });

    $(document).on('shown.bs.modal', '#createModal', function() {
        console.log('Create modal sudah terbuka');
        
        const partSelect = $(this).find('.part-select');
        
        // Cek apakah Select2 sudah aktif
        if (!partSelect.data('select2')) {
            console.log('Select2 belum aktif, inisialisasi ulang...');
            partSelect.select2({
                placeholder: "Pilih Part",
                allowClear: true, 
                dropdownParent: $(this),
                width: '100%'
            });
        }
        
        // Double check dengan melihat class
        if (!partSelect.hasClass('select2-hidden-accessible')) {
            console.log('Select2 tidak terdeteksi di DOM, paksa inisialisasi...');
            
            // Destroy dan buat ulang
            if (partSelect.data('select2')) {
                partSelect.select2('destroy');
            }
            
            partSelect.select2({
                placeholder: "Pilih Part",
                allowClear: true,
                dropdownParent: $(this),
                width: '100%'
            });
        }
        
        console.log('Select2 status:', partSelect.data('select2') ? 'Aktif' : 'Tidak aktif');
        console.log('Select2 class:', partSelect.hasClass('select2-hidden-accessible') ? 'Ada' : 'Tidak ada');
    });

    // --- EDIT BUTTON EVENT ---
    $(document).on('click', '.edit-btn', function() {
        const campaign = $(this).data('campaign');
        $('#editForm').attr('action', `{{ url('admin/campaigns') }}/${campaign.id}`);
        $('#edit_nama_campaign').val(campaign.nama_campaign);
        $('#edit_tipe').val(campaign.tipe);  
        $('#edit_harga_promo').val(campaign.harga_promo);
        $('#edit_tanggal_mulai').val(campaign.tanggal_mulai.substring(0, 10));
        $('#edit_tanggal_selesai').val(campaign.tanggal_selesai.substring(0, 10)); 
        $('#edit_is_active').val(campaign.is_active);

        // Set nilai Select2 dan panggil 'change' agar tampilan update
        $('#edit_part_id').val(campaign.part_id).trigger('change');
    });

    // Jika ada error validasi saat membuat, buka kembali modal
    @if ($errors->any() && !old('id'))
        setTimeout(function() {
            $('#createModal').modal('show');
        }, 500);
    @endif
});
</script>
@stop