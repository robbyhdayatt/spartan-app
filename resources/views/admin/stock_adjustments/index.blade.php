@extends('adminlte::page')

@section('title', 'Adjusment Stok')

@section('content_header')
    <h1>Adjusment Stok</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan Adjusment</h3>
        <div class="card-tools">
            <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-primary btn-sm">Buat Adjusment Baru</a>
        </div>
    </div>
    <div class="card-body">
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        <table id="stock_adjusment-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Part</th>
                    <th>Gudang</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td>{{ $adj->part->nama_part }}</td>
                    <td>{{ $adj->gudang->nama_gudang }}</td>
                    <td>{{ $adj->tipe }}</td>
                    <td>{{ $adj->jumlah }}</td>
                    <td>{{ $adj->alasan }}</td>
                    <td>{{ $adj->status }}</td>
                    <td>
                        @if($adj->status === 'PENDING_APPROVAL')
                        @can('perform-approval', $adj)
                            <form action="{{ route('admin.stock-adjustments.approve', $adj->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-xs">Approve</button>
                            </form>
                            <form action="{{ route('admin.stock-adjustments.reject', $adj->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-xs">Reject</button>
                            </form>
                        @endcan
                        @else
                            Diproses oleh {{ $adj->approvedBy->nama ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada permintaan adjusment.</td>
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
        $('#stock_adjusment-table').DataTable({
            "responsive": true,
        });
    });
</script>
@stop
