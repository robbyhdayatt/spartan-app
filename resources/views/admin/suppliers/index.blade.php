@extends('adminlte::page')

@section('title', 'Manajemen Supplier')

@section('content_header')
    <h1>Manajemen Supplier</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Supplier</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Supplier
                </button>
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

            <table id="suppliers-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Supplier</th>
                        <th>Kontak</th>
                        <th>PIC</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->kode_supplier }}</td>
                        <td>{{ $supplier->nama_supplier }}</td>
                        <td>{{ $supplier->telepon }} <br> {{ $supplier->email }}</td>
                        <td>{{ $supplier->pic_nama }}</td>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-xs edit-btn"
                                    data-id="{{ $supplier->id }}"
                                    data-kode_supplier="{{ $supplier->kode_supplier }}"
                                    data-nama_supplier="{{ $supplier->nama_supplier }}"
                                    data-alamat="{{ $supplier->alamat }}"
                                    data-telepon="{{ $supplier->telepon }}"
                                    data-email="{{ $supplier->email }}"
                                    data-pic_nama="{{ $supplier->pic_nama }}"
                                    data-is_active="{{ $supplier->is_active }}"
                                    data-toggle="modal"
                                    data-target="#editModal">
                                Edit
                            </button>
                            <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
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

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Supplier Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('admin.suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Supplier</label>
                                    <input type="text" class="form-control" name="kode_supplier" placeholder="Contoh: SUP001" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Supplier</label>
                                    <input type="text" class="form-control" name="nama_supplier" placeholder="Contoh: PT. Suku Cadang Sejahtera" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telepon</label>
                                    <input type="text" class="form-control" name="telepon" placeholder="Contoh: +628123456789" oninput="this.value = this.value.replace(/[^0-9\-+]/g, '');">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="Contoh: info@supplier.com">
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <label>Nama PIC</label>
                            <input type="text" class="form-control" name="pic_nama" placeholder="Contoh: Budi Santoso">
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Supplier</label>
                                    <input type="text" class="form-control" id="edit_kode_supplier" name="kode_supplier" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Supplier</label>
                                    <input type="text" class="form-control" id="edit_nama_supplier" name="nama_supplier" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telepon</label>
                                    <input type="text" class="form-control" id="edit_telepon" name="telepon" oninput="this.value = this.value.replace(/[^0-9\-+]/g, '');">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email">
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <label>Nama PIC</label>
                            <input type="text" class="form-control" id="edit_pic_nama" name="pic_nama">
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

@section('js')
<script>
    $(document).ready(function() {
        // Event listener for edit button
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var kode_supplier = $(this).data('kode_supplier');
            var nama_supplier = $(this).data('nama_supplier');
            var alamat = $(this).data('alamat');
            var telepon = $(this).data('telepon');
            var email = $(this).data('email');
            var pic_nama = $(this).data('pic_nama');
            var is_active = $(this).data('is_active');

            var url = "{{ url('admin/suppliers') }}/" + id;
            $('#editForm').attr('action', url);

            $('#edit_kode_supplier').val(kode_supplier);
            $('#edit_nama_supplier').val(nama_supplier);
            $('#edit_alamat').val(alamat);
            $('#edit_telepon').val(telepon);
            $('#edit_email').val(email);
            $('#edit_pic_nama').val(pic_nama);
            $('#edit_is_active').val(is_active);
        });

        $('#suppliers-table').DataTable({
            "responsive": true,
        });

        // Show the correct modal if there are validation errors
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
