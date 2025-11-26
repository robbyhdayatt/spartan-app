@extends('adminlte::page')

@section('title', 'Dashboard Super Admin')

@section('content_header')
    <h1>Overview Sistem Menyeluruh</h1>
@stop

@section('content')
    {{-- Baris 1: Ringkasan Entitas --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\Gudang::count() }}</h3>
                    <p>Total Gudang</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <a href="{{ route('admin.gudangs.index') }}" class="small-box-footer">Info Gudang <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\User::count() }}</h3>
                    <p>Total Pengguna</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">Kelola User <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Supplier::count() }}</h3>
                    <p>Total Supplier</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
                <a href="{{ route('admin.suppliers.index') }}" class="small-box-footer">Lihat Supplier <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ \App\Models\Part::count() }}</h3>
                    <p>Master Part</p>
                </div>
                <div class="icon"><i class="fas fa-cogs"></i></div>
                <a href="{{ route('admin.parts.index') }}" class="small-box-footer">Lihat Part <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Baris 2: Grafik Keuangan (Copy dari _default blade Manajer) --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Analisis Keuangan (Global)</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative mb-4">
                        <canvas id="financialChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 5 Produk (Global)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Terjual</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topProducts as $prod)
                        <tr>
                            <td>{{ $prod->nama_part }}</td>
                            <td>
                                <span class="text-success mr-1"><i class="fas fa-arrow-up"></i></span>
                                {{ $prod->total_qty }} Unit
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 3: Transaksi Terbaru --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">Penjualan Terbaru Hari Ini</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                            <tr>
                                <th>Faktur</th>
                                <th>Pelanggan</th>
                                <th>Gudang</th>
                                <th>Total</th>
                                <th>Waktu</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(\App\Models\Penjualan::with('gudang')->latest()->limit(5)->get() as $p)
                            <tr>
                                <td><a href="#">{{ $p->nomor_faktur }}</a></td>
                                <td>{{ $p->nama_konsumen }}</td>
                                <td>{{ $p->gudang->nama_gudang }}</td>
                                <td>Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                                <td>{{ $p->created_at->format('H:i') }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <a href="{{ route('admin.penjualans.index') }}" class="btn btn-sm btn-secondary float-right">Lihat Semua Transaksi</a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Menggunakan data yang dikirim dari HomeController
    var ctx = document.getElementById('financialChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Penjualan',
                    borderColor: '#007bff',
                    backgroundColor: 'transparent',
                    data: {!! json_encode($salesData) !!}
                },
                {
                    label: 'Pembelian',
                    borderColor: '#dc3545',
                    backgroundColor: 'transparent',
                    data: {!! json_encode($purchaseData) !!}
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: true
            },
            hover: {
                mode: 'index',
                intersect: true
            },
            scales: {
                yAxes: [{
                    ticks: {
                        callback: function(value) { return 'Rp ' + value/1000000 + ' Jt'; }
                    }
                }]
            }
        }
    });
</script>
@stop