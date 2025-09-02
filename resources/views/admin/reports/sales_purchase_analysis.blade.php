@extends('adminlte::page')

@section('title', 'Analisis Penjualan & Pembelian')

@section('content_header')
    <h1>Analisis Penjualan & Pembelian</h1>
@stop

@section('content')
    {{-- Form Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.sales-purchase-analysis') }}" method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                         <div class="form-group">
                            <label for="end_date">Tanggal Selesai</label>
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

    {{-- Hasil Analisis --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Spare Part Terlaris (Berdasarkan Kuantitas)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Part</th>
                                <th class="text-right">Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingParts as $part)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $part->part->nama_part }}</td>
                                <td class="text-right">{{ $part->total_qty }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Spare Part Paling Sering Dibeli (Berdasarkan Kuantitas)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                         <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Part</th>
                                <th class="text-right">Total Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                             @forelse($topPurchasedParts as $part)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $part->part->nama_part }}</td>
                                <td class="text-right">{{ $part->total_qty }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi Penjualan per Kategori (Berdasarkan Nilai)</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesByCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales by Category Chart
    var ctx = document.getElementById('salesByCategoryChart').getContext('2d');
    var salesByCategoryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($salesByCategory->keys()) !!},
            datasets: [{
                label: 'Total Penjualan',
                data: {!! json_encode($salesByCategory->values()) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
</script>
@stop
