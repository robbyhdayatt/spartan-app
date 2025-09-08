@extends('adminlte::page')

@section('title', 'Daftar Purchase Order')

@section('content_header')
    <h1>Daftar Purchase Order</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            @can('create-po')
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary">Buat PO Baru</a>
            @endcan
        </div>
        <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                    <tr>
                        <td>{{ $po->nomor_po }}</td>
                        <td>{{ \Carbon\Carbon::parse($po->tanggal_po)->format('d-m-Y') }}</td>
                        <td>{{ $po->supplier->nama_supplier }}</td>
                        <td>{{ 'Rp ' . number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $po->status_class }}">{{ $po->status_badge }}</span></td>
                        <td>{{ $po->createdBy->nama ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.purchase-orders.show', $po) }}" class="btn btn-info btn-sm">Detail</a>
                            {{-- TOMBOL HAPUS DAN FORM-NYA SUDAH DIHILANGKAN --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
    </script>
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    </script>
@endif
@stop
