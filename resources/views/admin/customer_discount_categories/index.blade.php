@extends('adminlte::page')

@section('title', 'Kategori Diskon Konsumen')

@section('content_header')
    <h1>Kategori Diskon Konsumen</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kategori</h3>
        <div class="card-tools">
            <a href="{{ route('admin.customer-discount-categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
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

        <table id="categories-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Diskon (%)</th>
                    <th>Jumlah Konsumen</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category->nama_kategori }}</td>
                        <td>{{ number_format($category->discount_percentage, 2) }}%</td>
                        <td>{{ $category->konsumens_count }}</td>
                        <td>
                            @if($category->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.customer-discount-categories.edit', $category) }}" class="btn btn-xs btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.customer-discount-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#categories-table').DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>
@stop