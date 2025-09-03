@extends('adminlte::page')

@section('title', 'Manajemen Gudang')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Manajemen Gudang</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Gudang</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createGudangModal">
                    Tambah Gudang
                </button>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <table id="gudangs-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Kode</th>
                        <th>Nama Gudang</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gudangs as $gudang)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $gudang->kode_gudang }}</td>
                        <td>{{ $gudang->nama_gudang }}</td>
                        <td>{{ $gudang->alamat }}</td>
                        <td>
                            @if($gudang->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-xs edit-btn"
                                    data-id="{{ $gudang->id }}"
                                    data-kode_gudang="{{ $gudang->kode_gudang }}"
                                    data-nama_gudang="{{ $gudang->nama_gudang }}"
                                    data-alamat="{{ $gudang->alamat }}"
                                    data-is_active="{{ $gudang->is_active }}"
                                    data-toggle="modal"
                                    data-target="#editGudangModal">
                                Edit
                            </button>
                            <form action="{{ route('admin.gudangs.destroy', $gudang->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
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

    <div class="modal fade" id="createGudangModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Gudang Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('admin.gudangs.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kode Gudang</label>
                            <input type="text" class="form-control" name="kode_gudang" placeholder="Contoh: BDL" required>
                            <small class="form-text text-muted">Gunakan singkatan 3 huruf dari nama lokasi. Contoh: Bandar Lampung -> BDL.</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Gudang</label>
                            <input type="text" class="form-control" name="nama_gudang" placeholder="Contoh: Gudang Bandar Lampung" required>
                        </div>
                         <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3"></textarea>
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

    <div class="modal fade" id="editGudangModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Gudang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editGudangForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kode Gudang</label>
                            <input type="text" class="form-control" id="edit_kode_gudang" name="kode_gudang" required>
                            <small class="form-text text-muted">Gunakan singkatan 3 huruf dari nama lokasi. Contoh: Bandar Lampung -> BDL.</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Gudang</label>
                            <input type="text" class="form-control" id="edit_nama_gudang" name="nama_gudang" required>
                        </div>
                         <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
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
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var kode_gudang = $(this).data('kode_gudang');
            var nama_gudang = $(this).data('nama_gudang');
            var alamat = $(this).data('alamat');
            var is_active = $(this).data('is_active');

            var url = "{{ url('admin/gudangs') }}/" + id;
            $('#editGudangForm').attr('action', url);

            $('#edit_kode_gudang').val(kode_gudang);
            $('#edit_nama_gudang').val(nama_gudang);
            $('#edit_alamat').val(alamat);
            $('#edit_is_active').val(is_active);
        });

        $('#gudangs-table').DataTable({
            "responsive": true,
        });

        @if ($errors->any())
            $('#createGudangModal').modal('show');
        @endif
    });
</script>
@stop
