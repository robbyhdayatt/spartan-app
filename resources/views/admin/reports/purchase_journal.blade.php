@extends('adminlte::page')

@section('title', 'Laporan Jurnal Pembelian')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Laporan Jurnal Pembelian</h1>
@stop

@section('content')
    {{-- Form Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Berdasarkan Tanggal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.purchase-journal') }}" method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai Terima</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                         <div class="form-group">
                            <label for="end_date">Tanggal Selesai Terima</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                         <div class="form-group" style="width: 100%;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Tampilkan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Hasil --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Laporan</h3>
            <div class="card-tools">
                @if(!$purchaseDetails->isEmpty())
                    <a href="{{ route('admin.reports.purchase-journal.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <table id="purchase-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tgl. Terima</th>
                        <th>No. Penerimaan</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Part</th>
                        <th class="text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseDetails as $detail)
                    <tr>
                        <td>{{ $detail->receiving->tanggal_terima->format('d-m-Y') }}</td>
                        <td>{{ $detail->receiving->nomor_penerimaan }}</td>
                        <td>{{ $detail->receiving->purchaseOrder->nomor_po }}</td>
                        <td>{{ $detail->receiving->purchaseOrder->supplier->nama_supplier }}</td>
                        <td>{{ $detail->part->nama_part }}</td>
                        <td class="text-right">{{ $detail->qty_terima }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data pembelian pada rentang tanggal ini.</td>
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
        $('#purchase-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        });
    });
</script>
@stop
