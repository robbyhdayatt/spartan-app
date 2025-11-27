@extends('adminlte::page')

@section('title', 'Daftar Purchase Order')

@section('content_header')
    <h1>Daftar Purchase Order</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data Purchase Order</h3>
        <div class="card-tools">
            @can('create-po')
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat PO Baru</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table id="po-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nomor PO</th>
                    <th>Supplier</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                <tr>
                    <td>
                        <strong>{{ $po->nomor_po }}</strong><br>
                        <small class="text-muted">{{ $po->tanggal_po->format('d M Y') }}</small>
                    </td>
                    <td>{{ $po->supplier->nama_supplier }}</td>
                    <td class="text-right">{{ 'Rp ' . number_format($po->total_amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $po->status_class }}">{{ $po->status_badge }}</span>
                    </td>
                    <td>
                        {{ $po->createdBy->nama ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $po->createdBy->jabatan->nama_jabatan ?? '' }}</small>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.purchase-orders.show', $po) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

{{-- Tambahkan inisialisasi DataTables di sini --}}
@section('js')
    <script>
        $(function () {
            $("#po-table").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "order": [[0, "asc"]], // Urutkan berdasarkan kolom pertama (Nomor PO) secara menaik
                "buttons": ["copy", "csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#po-table_wrapper .col-md-6:eq(0)');
        });
    </script>
@stop
