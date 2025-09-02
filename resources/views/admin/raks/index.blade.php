@extends('adminlte::page')

@section('title', 'Manajemen Rak')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Manajemen Rak</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Rak</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Rak
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <table id="raks-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Gudang</th>
                        <th>Kode Rak</th>
                        <th>Nama Rak</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($raks as $rak)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rak->gudang->nama_gudang ?? 'N/A' }}</td>
                        <td>{{ $rak->kode_rak }}</td>
                        <td>{{ $rak->nama_rak }}</td>
                        <td>
                            @if($rak->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-xs edit-btn"
                                    data-id="{{ $rak->id }}"
                                    data-gudang_id="{{ $rak->gudang_id }}"
                                    data-kode_rak="{{ $rak->kode_rak }}"
                                    data-nama_rak="{{ $rak->nama_rak }}"
                                    data-is_active="{{ $rak->is_active }}"
                                    data-toggle="modal"
                                    data-target="#editModal">
                                Edit
                            </button>
                            <form action="{{ route('admin.raks.destroy', $rak->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Rak Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('admin.raks.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Gudang</label>
                            <select class="form-control select2" name="gudang_id" required style="width: 100%;">
                                <option value="" disabled selected>Pilih Gudang</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }} ({{$gudang->kode_gudang}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kode Rak</label>
                            <input type="text" class="form-control" name="kode_rak" placeholder="Contoh: BDL-A-01-01" required>
                            <small class="form-text text-muted">Gunakan format: [Kode Gudang]-[Zona]-[Tingkat]-[Posisi]. Contoh: BDL-A-01-01</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Rak</label>
                            <input type="text" class="form-control" name="nama_rak" placeholder="Contoh: Rak Oli Samping" required>
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Gudang</label>
                            <select class="form-control" id="edit_gudang_id" name="gudang_id" required>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }} ({{$gudang->kode_gudang}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kode Rak</label>
                            <input type="text" class="form-control" id="edit_kode_rak" name="kode_rak" required>
                            <small class="form-text text-muted">Gunakan format: [Kode Gudang]-[Zona]-[Tingkat]-[Posisi]. Contoh: BDL-A-01-01</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Rak</label>
                            <input type="text" class="form-control" id="edit_nama_rak" name="nama_rak" required>
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

        $('#createModal .select2').select2({ dropdownParent: $('#createModal') });
        $('#editModal .select2').select2({ dropdownParent: $('#editModal') });

        // Event listener untuk tombol edit
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var gudang_id = $(this).data('gudang_id');
            var kode_rak = $(this).data('kode_rak');
            var nama_rak = $(this).data('nama_rak');
            var is_active = $(this).data('is_active');

            // Set action URL untuk form edit
            var url = "{{ url('admin/raks') }}/" + id;
            $('#editForm').attr('action', url);

            // Isi nilai-nilai form di dalam modal
            $('#edit_gudang_id').val(gudang_id);
            $('#edit_kode_rak').val(kode_rak);
            $('#edit_nama_rak').val(nama_rak);
            $('#edit_is_active').val(is_active);
        });

        $('#raks-table').DataTable({
            "responsive": true,
        });

        // Jika ada error validasi, buka kembali modal yang sesuai
        @if ($errors->any())
            @if (old('id')) // Asumsi ada input hidden 'id' di form edit jika diperlukan
                $('#editModal').modal('show');
            @else
                $('#createModal').modal('show');
            @endif
        @endif
    });
</script>
@stop
