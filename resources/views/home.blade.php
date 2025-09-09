@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

    {{-- BARIS UNTUK NOTIFIKASI PERSETUJUAN --}}
    <div class="row">
        {{-- 1. Notifikasi Persetujuan Purchase Order --}}
        @if(isset($pendingApprovals['purchase_orders']) && !$pendingApprovals['purchase_orders']->isEmpty())
            <div class="col-md-4">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-file-invoice"></i> Persetujuan PO</h5>
                    <p>
                        Ada <strong>{{ $pendingApprovals['purchase_orders']->count() }} PO</strong> menunggu persetujuan Anda.
                    </p>
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-outline-dark">
                        Proses Sekarang <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endif

        {{-- 2. Notifikasi Persetujuan Stock Adjustment --}}
        @if(isset($pendingApprovals['stock_adjustments']) && !$pendingApprovals['stock_adjustments']->isEmpty())
            <div class="col-md-4">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-edit"></i> Persetujuan Adjustment</h5>
                    <p>
                        Ada <strong>{{ $pendingApprovals['stock_adjustments']->count() }} Adjusment Stok</strong> menunggu persetujuan Anda.
                    </p>
                    <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-sm btn-outline-dark">
                        Proses Sekarang <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endif

        {{-- 3. Notifikasi Persetujuan Stock Mutation --}}
        @if(isset($pendingApprovals['stock_mutations']) && !$pendingApprovals['stock_mutations']->isEmpty())
             <div class="col-md-4">
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-truck-loading"></i> Persetujuan Mutasi</h5>
                    <p>
                        Ada <strong>{{ $pendingApprovals['stock_mutations']->count() }} Mutasi Stok</strong> menunggu persetujuan Anda.
                    </p>
                    <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-sm btn-outline-dark">
                        Proses Sekarang <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endif
    </div>
    {{-- Baris untuk Info Box --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $salesToday }}</h3>
                    <p>Penjualan Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
                <a href="{{ route('admin.penjualans.index') }}" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $poToday }}</h3>
                    <p>Purchase Order Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('admin.purchase-orders.index') }}" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Rp {{ number_format($stockValue, 0, ',', '.') }}</h3>
                    <p>Total Nilai Stok</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="#" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
         <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $receivingToday }}</h3>
                    <p>Penerimaan Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-box-open"></i></div>
                <a href="{{ route('admin.receivings.index') }}" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Baris untuk Konten Utama (Grafik dan List) --}}
    <div class="row">
        {{-- Kolom Kiri - Grafik --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Penjualan (30 Hari Terakhir)</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan - Stok Kritis & Approval --}}
        <div class="col-lg-4">
            {{-- Tugas Approval untuk Kepala Gudang --}}
            @if(Auth::user()->jabatan->nama_jabatan === 'Kepala Gudang' && (
                !$pendingApprovals['purchase_orders']->isEmpty() ||
                !$pendingApprovals['stock_adjustments']->isEmpty() ||
                !$pendingApprovals['stock_mutations']->isEmpty()
            ))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tugas Persetujuan Anda</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($pendingApprovals['purchase_orders'] as $po)
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="list-group-item list-group-item-action">PO: {{ $po->nomor_po }}</a>
                        @endforeach
                        @foreach($pendingApprovals['stock_adjustments'] as $adj)
                            <a href="{{ route('admin.stock-adjustments.index') }}" class="list-group-item list-group-item-action">Adjusment: {{ $adj->part->nama_part }} ({{$adj->jumlah}})</a>
                        @endforeach
                        @foreach($pendingApprovals['stock_mutations'] as $mut)
                            <a href="{{ route('admin.stock-mutations.index') }}" class="list-group-item list-group-item-action">Mutasi: {{ $mut->part->nama_part }} ({{$mut->jumlah}})</a>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Daftar Stok Kritis --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Stok Kritis (di Bawah Minimum)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <tbody>
                            @forelse($criticalStockParts as $part)
                            <tr>
                                <td>{{ $part->nama_part }}</td>
                                <td><span class="badge badge-danger">{{ $part->total_stock }} / {{ $part->stok_minimum }}</span></td>
                            </tr>
                            @empty
                            <tr><td class="text-center p-2">Tidak ada stok kritis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesChartLabels) !!},
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: {!! json_encode($salesChartData) !!},
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@stop

@section('css')
<style>
    .list-group-item-action {
        color: #495057;
    }
    .list-group-item-action:hover, .list-group-item-action:focus {
        background-color: #f8f9fa;
        color: #1f2d3d;
    }
</style>
@stop
