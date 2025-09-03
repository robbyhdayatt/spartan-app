@extends('adminlte::page')

@section('title', 'Mutasi Gudang')

@section('content_header')
    <h1>Mutasi Gudang</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan Mutasi</h3>
        <div class="card-tools">
            <a href="{{ route('admin.stock-mutations.create') }}" class="btn btn-primary btn-sm">Buat Permintaan Mutasi</a>
        </div>
    </div>
    <div class="card-body">
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        <table id="stock_mutations-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>No. Mutasi</th>
                    <th>Part</th>
                    <th>Asal -> Tujuan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $mutation)
                <tr>
                    <td>{{ $mutation->nomor_mutasi }}</td>
                    <td>{{ $mutation->part->nama_part }}</td>
                    <td>{{ $mutation->gudangAsal->nama_gudang }} -> {{ $mutation->gudangTujuan->nama_gudang }}</td>
                    <td>{{ $mutation->jumlah }}</td>
                    <td>{{ $mutation->status }}</td>
                    <td>
                        @if($mutation->status === 'PENDING_APPROVAL')
                            <form action="{{ route('admin.stock-mutations.approve', $mutation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-xs">Approve</button>
                            </form>
                            <form action="{{ route('admin.stock-mutations.reject', $mutation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-xs">Reject</button>
                            </form>
                        @else
                           -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada permintaan mutasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#stock_mutations-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
