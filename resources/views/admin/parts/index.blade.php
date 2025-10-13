@extends('adminlte::page')

@section('title', 'Manajemen Part')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
{{-- SweetAlert bisa tetap ada untuk notifikasi sukses, tapi tidak untuk konfirmasi hapus --}}
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1>Manajemen Part</h1>
@stop

@push('css')
<style>
    /* Style untuk merapikan Select2 agar sejajar dengan field lain */
    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: .75rem;
        padding-top: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Part</h3>
            <div class="card-tools">
                @can('is-super-admin')
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    <i class="fas fa-plus"></i> Tambah Part
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            {{-- Menggunakan alert biasa untuk notifikasi agar konsisten --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
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
                        <th>Kode Part</th>
                        <th>Nama Part</th>
                        <th>Brand</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok Min</th>
                        <th>Status</th>
                        @can('is-super-admin')
                        <th>Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                    <tr>
                        <td>{{ $part->kode_part }}</td>
                        <td>{{ $part->nama_part }}</td>
                        <td>{{ $part->brand->nama_brand ?? 'N/A' }}</td>
                        <td>{{ $part->category->nama_kategori ?? 'N/A' }}</td>
                        <td>Rp {{ number_format($part->harga_beli_default, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($part->harga_jual_default, 0, ',', '.') }}</td>
                        <td>{{ $part->stok_minimum }}</td>
                        <td>
                            @if($part->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        @can('is-super-admin')
                        <td>
                            <a href="{{ route('admin.reports.stock-card', ['part_id' => $part->id]) }}" class="btn btn-info btn-xs" title="Lihat Kartu Stok"><i class="fas fa-file-alt"></i></a>
                            <button class="btn btn-warning btn-xs edit-btn" data-id="{{ $part->id }}" data-part='@json($part)' data-toggle="modal" data-target="#editModal" title="Edit Part"><i class="fas fa-edit"></i></button>

                            {{-- MENGEMBALIKAN FUNGSI HAPUS KE KONFIRMASI BAWAAN BROWSER --}}
                            <form action="{{ route('admin.parts.destroy', $part->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus part ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" title="Hapus Part"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@can('is-super-admin')
    @include('admin.parts.modal_import')

    {{-- Modal Create --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Part Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.parts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        {{-- Isi Modal Create --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Brand</label>
                                    <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;" required>
                                        <option value="">Pilih Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Kategori</label>
                                    <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="kode_part">Kode Part</label>
                            <input type="text" class="form-control" id="kode_part" name="kode_part" placeholder="Contoh: ASP-AK-001" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_part">Nama Part</label>
                            <input type="text" class="form-control" id="nama_part" name="nama_part" required>
                        </div>
                         <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="satuan">Satuan</label>
                                    <input type="text" class="form-control" id="satuan" name="satuan" required value="Pcs">
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stok_minimum">Stok Minimum</label>
                                    <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_beli_default">Harga Beli Default</label>
                                    <input type="number" class="form-control" id="harga_beli_default" name="harga_beli_default" required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_jual_default">Harga Jual Default</label>
                                    <input type="number" class="form-control" id="harga_jual_default" name="harga_jual_default" required>
                                </div>
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

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Part</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        {{-- Isi Modal Edit --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_brand_id">Brand</label>
                                    <select class="form-control select2" id="edit_brand_id" name="brand_id" style="width: 100%;" required>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_category_id">Kategori</label>
                                    <select class="form-control select2" id="edit_category_id" name="category_id" style="width: 100%;" required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_part">Kode Part</label>
                            <input type="text" class="form-control" id="edit_kode_part" name="kode_part" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_part">Nama Part</label>
                            <input type="text" class="form-control" id="edit_nama_part" name="nama_part" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_satuan">Satuan</label>
                                    <input type="text" class="form-control" id="edit_satuan" name="satuan" required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_stok_minimum">Stok Minimum</label>
                                    <input type="number" class="form-control" id="edit_stok_minimum" name="stok_minimum">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_beli_default">Harga Beli Default</label>
                                    <input type="number" class="form-control" id="edit_harga_beli_default" name="harga_beli_default" required>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_harga_jual_default">Harga Jual Default</label>
                                    <input type="number" class="form-control" id="edit_harga_jual_default" name="harga_jual_default" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_is_active">Status</label>
                            <select class="form-control" id="edit_is_active" name="is_active" required>
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
@endcan
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#parts-table').DataTable({
            "responsive": true,
        });

        @can('is-super-admin')
            // Inisialisasi Select2
            $('#createModal .select2').select2({ theme: 'bootstrap4', dropdownParent: $('#createModal') });
            $('#editModal .select2').select2({ theme: 'bootstrap4', dropdownParent: $('#editModal') });

            // Delegasi event untuk tombol Edit
            $('#parts-table tbody').on('click', '.edit-btn', function() {
                var part = $(this).data('part');
                var url = "{{ url('admin/parts') }}/" + part.id;
                $('#editForm').attr('action', url);

                $('#edit_id').val(part.id);
                $('#edit_kode_part').val(part.kode_part);
                $('#edit_nama_part').val(part.nama_part);
                $('#edit_brand_id').val(part.brand_id).trigger('change');
                $('#edit_category_id').val(part.category_id).trigger('change');
                $('#edit_satuan').val(part.satuan);
                $('#edit_stok_minimum').val(part.stok_minimum);
                $('#edit_harga_beli_default').val(part.harga_beli_default);
                $('#edit_harga_jual_default').val(part.harga_jual_default);
                $('#edit_is_active').val(part.is_active ? '1' : '0');
            });

            // Logika untuk menampilkan modal jika ada error validasi
            @if ($errors->any())
                @if (old('id'))
                    $('#editModal').modal('show');
                @else
                    $('#createModal').modal('show');
                @endif
            @endif
        @endcan
    });
</script>
@endpush
