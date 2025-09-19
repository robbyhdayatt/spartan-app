@extends('adminlte::page')

@section('title', 'Manajemen Konsumen')

@section('content_header')
    <h1>Manajemen Konsumen</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Konsumen</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Konsumen
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

            <table id="parts-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Konsumen</th>
                        <th>Tipe</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($konsumens as $konsumen)
                        <tr>
                            <td>{{ $konsumen->kode_konsumen }}</td>
                            <td>{{ $konsumen->nama_konsumen }}</td>
                            <td>{{ $konsumen->tipe_konsumen }}</td>
                            <td>{{ $konsumen->telepon }}</td>
                            <td>
                                @if ($konsumen->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-xs edit-btn" data-id="{{ $konsumen->id }}"
                                    data-kode_konsumen="{{ $konsumen->kode_konsumen }}"
                                    data-nama_konsumen="{{ $konsumen->nama_konsumen }}"
                                    data-tipe_konsumen="{{ $konsumen->tipe_konsumen }}"
                                    data-alamat="{{ $konsumen->alamat }}" data-telepon="{{ $konsumen->telepon }}"
                                    data-is_active="{{ $konsumen->is_active }}" data-toggle="modal"
                                    data-target="#editModal">
                                    Edit
                                </button>
                                <form action="{{ route('admin.konsumens.destroy', $konsumen->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
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
                    <h5 class="modal-title">Tambah Konsumen Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('admin.konsumens.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Konsumen</label>
                                    <input type="text" class="form-control" name="kode_konsumen"
                                        placeholder="Contoh: CUST001" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Konsumen</label>
                                    <input type="text" class="form-control" name="nama_konsumen"
                                        placeholder="Contoh: BENGKEL MAJU JAYA" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Konsumen</label>
                                    <select class="form-control" name="tipe_konsumen">
                                        <option value="Bengkel">Bengkel</option>
                                        <option value="Retail">Retail</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telepon</label>
                                    <input type="text" class="form-control" name="telepon"
                                        placeholder="Contoh: +628123456789"
                                        oninput="this.value = this.value.replace(/[^0-9\-+]/g, '');">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="alamat" rows="2"></textarea>
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
                    <h5 class="modal-title">Edit Konsumen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Konsumen</label>
                                    <input type="text" class="form-control" id="edit_kode_konsumen"
                                        name="kode_konsumen" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Konsumen</label>
                                    <input type="text" class="form-control" id="edit_nama_konsumen"
                                        name="nama_konsumen" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Konsumen</label>
                                    <select class="form-control" id="edit_tipe_konsumen" name="tipe_konsumen">
                                        <option value="Bengkel">Bengkel</option>
                                        <option value="Retail">Retail</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telepon</label>
                                    <input type="text" class="form-control" id="edit_telepon" name="telepon"
                                        oninput="this.value = this.value.replace(/[^0-9\-+]/g, '');">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="2"></textarea>
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
                var kode_konsumen = $(this).data('kode_konsumen');
                var nama_konsumen = $(this).data('nama_konsumen');
                var tipe_konsumen = $(this).data('tipe_konsumen');
                var alamat = $(this).data('alamat');
                var telepon = $(this).data('telepon');
                var is_active = $(this).data('is_active');

                var url = "{{ url('admin/konsumens') }}/" + id;
                $('#editForm').attr('action', url);

                $('#edit_kode_konsumen').val(kode_konsumen);
                $('#edit_nama_konsumen').val(nama_konsumen);
                $('#edit_tipe_konsumen').val(tipe_konsumen);
                $('#edit_alamat').val(alamat);
                $('#edit_telepon').val(telepon);
                $('#edit_is_active').val(is_active);
            });

            $('#parts-table').DataTable({
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
