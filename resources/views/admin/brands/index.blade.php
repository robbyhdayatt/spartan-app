@extends('adminlte::page')

@section('title', 'Manajemen Brand')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Manajemen Brand</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Brand</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createBrandModal">
                    Tambah Brand
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <table id="brands-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Nama Brand</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brands as $brand)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $brand->nama_brand }}</td>
                        <td>
                            @if($brand->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-xs edit-btn"
                                    data-id="{{ $brand->id }}"
                                    data-nama_brand="{{ $brand->nama_brand }}"
                                    data-is_active="{{ $brand->is_active }}"
                                    data-toggle="modal"
                                    data-target="#editBrandModal">
                                Edit
                            </button>
                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus brand ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="createBrandModal" tabindex="-1" role="dialog" aria-labelledby="createBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBrandModalLabel">Tambah Brand Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_brand">Nama Brand</label>
                            <input type="text" class="form-control" id="nama_brand" name="nama_brand" placeholder="Contoh: Yamaha Genuine Part" required>
                            <small class="form-text text-muted">Masukkan nama lengkap dari brand sparepart.</small>
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

    <div class="modal fade" id="editBrandModal" tabindex="-1" role="dialog" aria-labelledby="editBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBrandModalLabel">Edit Brand</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editBrandForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_nama_brand">Nama Brand</label>
                            <input type="text" class="form-control" id="edit_nama_brand" name="nama_brand" required>
                             <small class="form-text text-muted">Masukkan nama lengkap dari brand sparepart.</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_is_active">Status</label>
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

@section('js')
<script>
    $(document).ready(function() {
        // Event listener untuk tombol edit
        $('.edit-btn').on('click', function() {
            // Ambil data dari tombol yang diklik
            var id = $(this).data('id');
            var nama_brand = $(this).data('nama_brand');
            var is_active = $(this).data('is_active');

            // Set action URL untuk form edit
            var url = "{{ url('admin/brands') }}/" + id;
            $('#editBrandForm').attr('action', url);

            // Isi nilai-nilai form di dalam modal
            $('#edit_nama_brand').val(nama_brand);
            $('#edit_is_active').val(is_active);
        });

        $('#brands-table').DataTable({
            "responsive": true,
        });

        // Jika ada error validasi, otomatis buka kembali modal yang sesuai
        @if($errors->has('nama_brand') && old('id')) // Cek jika ini error update
            $('#editBrandModal').modal('show');
        @elseif($errors->any()) // Error untuk create
            $('#createBrandModal').modal('show');
        @endif
    });
</script>
@stop
