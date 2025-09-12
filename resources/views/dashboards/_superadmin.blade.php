{{-- resources/views/dashboards/_superadmin.blade.php --}}

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
            <a href="{{ route('admin.reports.inventory-value') }}" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
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

    {{-- Kolom Kanan - Stok Kritis --}}
    <div class="col-lg-4">
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

{{-- ... kode HTML untuk superadmin ... --}}

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
@endpush
