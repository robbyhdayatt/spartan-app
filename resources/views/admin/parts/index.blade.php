@extends('adminlte::page')

@section('title', 'Manajemen Part')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Manajemen Part</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Part</h3>
            <div class="card-tools">
                @if(Auth::user()->jabatan->nama_jabatan == 'Super Admin' || Auth::user()->jabatan->nama_jabatan == 'Manajer Area')
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Part
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(session('import_errors'))
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Impor Gagal! Ada beberapa error:</h5>
                    <ul>
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <table id="parts-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode Part</th><th>Nama Part</th><th>Brand</th><th>Kategori</th><th>Harga Jual</th><th>Status</th>
                        @if(Auth::user()->jabatan->nama_jabatan == 'Super Admin' || Auth::user()->jabatan->nama_jabatan == 'Manajer Area')
                        <th style="width: 150px">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                    <tr>
                        <td>{{ $part->kode_part }}</td>
                        <td>{{ $part->nama_part }}</td>
                        <td>{{ $part->brand->nama_brand ?? 'N/A' }}</td>
                        <td>{{ $part->category->nama_kategori ?? 'N/A' }}</td>
                        <td>Rp {{ number_format($part->harga_jual_default, 0, ',', '.') }}</td>
                        <td>
                            @if($part->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        @if(Auth::user()->jabatan->nama_jabatan == 'Super Admin' || Auth::user()->jabatan->nama_jabatan == 'Manajer Area')
                        <td>
                            <a href="{{ route('admin.reports.stock-card', ['part_id' => $part->id]) }}" class="btn btn-info btn-xs">Kartu Stok</a>
                            <button class="btn btn-warning btn-xs edit-btn" data-id="{{ $part->id }}" data-part='@json($part)' data-toggle="modal" data-target="#editModal">Edit</button>
                            <form action="{{ route('admin.parts.destroy', $part->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.parts.modal_import')

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Part Baru</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <form action="{{ route('admin.parts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row"><div class="col-md-6 form-group"><label>Kode Part</label><input type="text" class="form-control" name="kode_part" placeholder="Contoh: YGP-OLI-0001" required><small class="form-text text-muted">Format: [KODE BRAND]-[KODE KATEGORI]-[NOMOR URUT]</small></div><div class="col-md-6 form-group"><label>Nama Part</label><input type="text" class="form-control" name="nama_part" placeholder="Contoh: Yamalube Matic 800ML" required></div></div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Brand</label><select class="form-control select2" name="brand_id" required style="width: 100%;"><option value="" disabled selected>Pilih Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>@endforeach</select></div>
                            <div class="col-md-6 form-group"><label>Kategori</label><select class="form-control select2" name="category_id" required style="width: 100%;"><option value="" disabled selected>Pilih Kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>@endforeach</select></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Satuan</label><select class="form-control select2" name="satuan" required style="width: 100%;"><option value="Pcs">Pcs</option><option value="Set">Set</option><option value="Liter">Liter</option></select></div>
                            <div class="col-md-6 form-group"><label>Stok Minimum</label><input type="number" class="form-control" name="stok_minimum" value="0"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Harga Beli Default (Rp)</label><input type="number" class="form-control" name="harga_beli_default" required></div>
                            <div class="col-md-6 form-group"><label>Harga Jual Default (Rp)</label><input type="number" class="form-control" name="harga_jual_default" required></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Part</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Kode Part</label><input type="text" class="form-control" id="edit_kode_part" name="kode_part" required><small class="form-text text-muted">Format: [KODE BRAND]-[KODE KATEGORI]-[NOMOR URUT]</small></div>
                            <div class="col-md-6 form-group"><label>Nama Part</label><input type="text" class="form-control" id="edit_nama_part" name="nama_part" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Brand</label><select class="form-control select2" id="edit_brand_id" name="brand_id" required style="width: 100%;">@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>@endforeach</select></div>
                            <div class="col-md-6 form-group"><label>Kategori</label><select class="form-control select2" id="edit_category_id" name="category_id" required style="width: 100%;">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>@endforeach</select></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Satuan</label><select class="form-control select2" id="edit_satuan" name="satuan" required style="width: 100%;"><option value="Pcs">Pcs</option><option value="Set">Set</option><option value="Liter">Liter</option></select></div>
                            <div class="col-md-6 form-group"><label>Stok Minimum</label><input type="number" class="form-control" id="edit_stok_minimum" name="stok_minimum" value="0"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Harga Beli Default (Rp)</label><input type="number" class="form-control" id="edit_harga_beli_default" name="harga_beli_default" required></div>
                            <div class="col-md-6 form-group"><label>Harga Jual Default (Rp)</label><input type="number" class="form-control" id="edit_harga_jual_default" name="harga_jual_default" required></div>
                        </div>
                        <div class="form-group"><label>Status</label><select class="form-control" id="edit_is_active" name="is_active"><option value="1">Aktif</option><option value="0">Non-Aktif</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize Select2 for all modals
        $('#createModal .select2').select2({ dropdownParent: $('#createModal') });
        $('#editModal .select2').select2({ dropdownParent: $('#editModal') });

        $('.edit-btn').on('click', function() {
            var part = $(this).data('part');
            var id = part.id;
            var url = "{{ url('admin/parts') }}/" + id;
            $('#editForm').attr('action', url);

            // Populate all fields including select2 fields
            $('#edit_kode_part').val(part.kode_part);
            $('#edit_nama_part').val(part.nama_part);
            $('#edit_brand_id').val(part.brand_id).trigger('change');
            $('#edit_category_id').val(part.category_id).trigger('change');
            $('#edit_satuan').val(part.satuan).trigger('change');
            $('#edit_stok_minimum').val(part.stok_minimum);
            $('#edit_harga_beli_default').val(part.harga_beli_default);
            $('#edit_harga_jual_default').val(part.harga_jual_default);
            $('#edit_is_active').val(part.is_active);
        });

        $('#parts-table').DataTable({
            "responsive": true,
        });

        // Show correct modal on validation error
        @if ($errors->any())
            @if (old('id'))
                $('#editModal').modal('show');
            @else
                $('#createModal').modal('show');
            @endif
        @endif
    });
</script>
@stop
