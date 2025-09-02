@extends('adminlte::page')

@section('title', 'Purchase Orders')

@section('content_header')
    <h1>Purchase Orders (PO)</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar PO</h3>
            <div class="card-tools">
                @can('create-po')
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm">Buat PO Baru</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
             @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif
             @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Gudang</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->nomor_po }}</td>
                        <td>{{ $po->tanggal_po->format('d-m-Y') }}</td>
                        <td>{{ $po->supplier->nama_supplier }}</td>
                        <td>{{ $po->gudang->nama_gudang }}</td>
                        <td>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $statusClass = [
                                    'PENDING_APPROVAL' => 'badge-warning',
                                    'APPROVED' => 'badge-success',
                                    'REJECTED' => 'badge-danger',
                                    'FULLY_RECEIVED' => 'badge-primary',
                                    'PARTIALLY_RECEIVED' => 'badge-info',
                                    'DRAFT' => 'badge-secondary',
                                ][$po->status] ?? 'badge-light';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', $po->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-info btn-xs">Lihat</a>
                            @if($po->status === 'PENDING_APPROVAL' || $po->status === 'DRAFT')
                            <form action="{{ route('admin.purchase-orders.destroy', $po->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada Purchase Order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         <div class="card-footer clearfix">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
@stop
