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
                {{-- Hanya tampilkan tombol untuk Super Admin --}}
                @can('is-super-admin')
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Part
                </button>
                @endcan
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
                        {{-- Hanya tampilkan kolom Aksi untuk Super Admin --}}
                        @can('is-super-admin')
                        <th style="width: 150px">Aksi</th>
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
                        <td>Rp {{ number_format($part->harga_jual_default, 0, ',', '.') }}</td>
                        <td>
                            @if($part->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        {{-- Hanya tampilkan tombol aksi untuk Super Admin --}}
                        @can('is-super-admin')
                        <td>
                            <a href="{{ route('admin.reports.stock-card', ['part_id' => $part->id]) }}" class="btn btn-info btn-xs" title="Lihat Kartu Stok"><i class="fas fa-file-alt"></i></a>
                            <button class="btn btn-warning btn-xs edit-btn" data-id="{{ $part->id }}" data-part='@json($part)' data-toggle="modal" data-target="#editModal" title="Edit Part"><i class="fas fa-edit"></i></button>
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

{{-- Modal hanya perlu di-include jika user adalah Super Admin --}}
@can('is-super-admin')
    @include('admin.parts.modal_import')
    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        {{-- ... (Isi modal create tidak berubah) ... --}}
    </div>
    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        {{-- ... (Isi modal edit tidak berubah) ... --}}
    </div>
@endcan
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#parts-table').DataTable({
            "responsive": true,
        });

        {{-- Hanya jalankan script untuk modal jika user adalah Super Admin --}}
        @can('is-super-admin')
            // Initialize Select2 for all modals
            $('#createModal .select2').select2({ dropdownParent: $('#createModal') });
            $('#editModal .select2').select2({ dropdownParent: $('#editModal') });

            $('.edit-btn').on('click', function() {
                var part = $(this).data('part');
                var id = part.id;
                var url = "{{ url('admin/parts') }}/" + id;
                $('#editForm').attr('action', url);

                // Populate all fields
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

            // Show correct modal on validation error
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
