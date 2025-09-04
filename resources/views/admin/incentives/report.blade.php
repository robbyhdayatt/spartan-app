@extends('adminlte::page')

@section('title', 'Laporan Insentif Sales')

{{-- 1. Aktifkan plugin DataTables untuk halaman ini --}}
@section('plugins.Datatables', true)

@section('content_header')
    <h1>Laporan Insentif Sales</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Periode</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.incentives.report') }}" method="GET">
            <div class="row">
                <div class="col-md-5 form-group">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        @for ($y = now()->year; $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-5 form-group">
                    <label>Bulan</label>
                    <select name="bulan" class="form-control">
                        @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Laporan Insentif untuk {{ \Carbon\Carbon::create()->month($bulan)->format('F') }} {{ $tahun }}</h3>
    </div>
    <div class="card-body">
        {{-- 2. Beri ID pada tabel --}}
        <table id="incentive-report-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Sales</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Pencapaian</th>
                    <th class="text-right">Persentase</th>
                    <th class="text-right">Jumlah Insentif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $data)
                <tr>
                    <td>{{ $data['sales_name'] }}</td>
                    <td class="text-right">Rp {{ number_format($data['target_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($data['total_penjualan'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['pencapaian'], 2) }}%</td>
                    <td class="text-right font-weight-bold">Rp {{ number_format($data['jumlah_insentif'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada target yang ditetapkan untuk periode ini.</td>
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
        // 3. Inisialisasi DataTables
        $('#incentive-report-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        });
    });
</script>
@stop
