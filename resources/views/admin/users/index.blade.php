@extends('adminlte::page')

@section('title', 'Manajemen Pengguna')

@section('content_header')
    <h1>Manajemen Pengguna</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pengguna</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">
                    Tambah Pengguna
                </button>
            </div>
        </div>
        <div class="card-body">
             @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
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

            <table id="parts-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Jabatan</th>
                        <th>Gudang</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->nama }} <br><small class="text-muted">NIK: {{ $user->nik }}</small></td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->jabatan->nama_jabatan ?? 'N/A' }}</td>
                        <td>{{ $user->gudang->nama_gudang ?? 'Semua Gudang' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-xs edit-btn"
                                    data-id="{{ $user->id }}"
                                    data-user='@json($user)'
                                    data-toggle="modal"
                                    data-target="#editModal">
                                Edit
                            </button>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
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
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>NIK</label>
                                <input type="text" class="form-control" name="nik" placeholder="Contoh: KG-BDL-001" required>
                                <small class="form-text text-muted">Format: [Singkatan Jabatan]-[Kode Gudang]-[Nomor Urut]</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Jabatan</label>
                                <select class="form-control select2" name="jabatan_id" required style="width: 100%;">
                                    <option value="" disabled selected>Pilih Jabatan</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Gudang</label>
                                <select class="form-control select2" name="gudang_id" required style="width: 100%;">
                                    <option value="">Tidak Terikat Gudang (Manajer/Admin)</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }} ({{ $gudang->kode_gudang }})</option>
                                    @endforeach
                                </select>
                                <small>Kosongkan jika bukan staf gudang.</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                             <div class="col-md-6 form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" name="username" placeholder="Contoh: budi_kg" required>
                                <small class="form-text text-muted">Format: [Nama Depan]_[Singkatan Jabatan]. Huruf kecil.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                         <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" class="form-control" id="edit_nama" name="nama" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>NIK</label>
                                <input type="text" class="form-control" id="edit_nik" name="nik" required>
                                <small class="form-text text-muted">Format: [Singkatan Jabatan]-[Kode Gudang]-[Nomor Urut]</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Jabatan</label>
                                <select class="form-control" id="edit_jabatan_id" name="jabatan_id" required>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Gudang</label>
                                <select class="form-control" id="edit_gudang_id" name="gudang_id">
                                    <option value="">Tidak Terikat Gudang (Manajer/Admin)</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }} ({{ $gudang->kode_gudang }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                            <small class="form-text text-muted">Format: [Nama Depan]_[Singkatan Jabatan]. Huruf kecil.</small>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="edit_is_active" name="is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                        <hr>
                        <p class="text-muted">Kosongkan password jika tidak ingin mengubahnya.</p>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Password Baru</label>
                                <input type="password" class="form-control" name="password">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
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
    $('#createModal .select2').select2({ dropdownParent: $('#createModal') });
    $('#editModal .select2').select2({ dropdownParent: $('#editModal') });

    $(document).ready(function() {
        // Event listener untuk tombol edit
        $('.edit-btn').on('click', function() {
            var user = $(this).data('user');
            var id = user.id;

            var url = "{{ url('admin/users') }}/" + id;
            $('#editForm').attr('action', url);

            // Isi nilai-nilai form di dalam modal
            $('#edit_nama').val(user.nama);
            $('#edit_nik').val(user.nik);
            $('#edit_username').val(user.username);
            $('#edit_jabatan_id').val(user.jabatan_id);
            $('#edit_gudang_id').val(user.gudang_id);
            $('#edit_is_active').val(user.is_active);
        });

        $('#parts-table').DataTable({
            "responsive": true,
        });

        // Tampilkan modal jika ada error validasi
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
