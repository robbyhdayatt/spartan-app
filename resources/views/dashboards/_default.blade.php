@extends('adminlte::page')

@section('title', 'Dashboard Manajer Area')

@section('content_header')
    <h1>Overview Kinerja Bisnis</h1>
@stop

@section('content')
    {{-- Info Boxes --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Part Aktif</span>
                    <span class="info-box-number">{{ \App\Models\Part::count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Penjualan Bulan Ini</span>
                    <span class="info-box-number">Rp {{ number_format($salesData->last(), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Konsumen</span>
                    {{-- Karena tabel konsumen dihapus, kita hitung distinct nama dari penjualan --}}
                    <span class="info-box-number">{{ \App\Models\Penjualan::distinct('nama_konsumen')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pembelian Bulan Ini</span>
                    <span class="info-box-number">Rp {{ number_format($purchaseData->last(), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grafik Penjualan vs Pembelian --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Penjualan & Pembelian (6 Bulan)</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Produk --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">5 Produk Terlaris</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th class="text-right">Qty Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $product)
                            <tr>
                                <td>{{ $product->nama_part }}</td>
                                <td class="text-right">{{ $product->total_qty }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Penjualan (Rp)',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    data: {!! json_encode($salesData) !!}
                },
                {
                    label: 'Pembelian (Rp)',
                    backgroundColor: 'rgba(210, 214, 222, 1)',
                    data: {!! json_encode($purchaseData) !!}
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) { return 'Rp ' + value.toLocaleString(); }
                    }
                }]
            }
        }
    });
</script>
@stop