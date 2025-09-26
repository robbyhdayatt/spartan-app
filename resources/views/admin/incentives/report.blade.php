@extends('adminlte::page')

@section('title', 'Laporan Insentif Sales')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

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
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label> <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
                    </div>
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
        @if(session('success'))
            <input type="hidden" id="success-message" value="{{ session('success') }}">
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table id="incentive-report-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Sales</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Pencapaian</th>
                    <th class="text-right">Persentase</th>
                    <th class="text-right">Jumlah Insentif</th>
                    <th class="text-center">Status Pembayaran</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $incentive)
                <tr>
                    <td>{{ $incentive->user->nama ?? 'N/A' }}</td>
                    <td class="text-right">Rp {{ number_format($incentive->salesTarget->target_amount ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($incentive->total_penjualan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($incentive->persentase_pencapaian, 2) }}%</td>
                    <td class="text-right font-weight-bold">Rp {{ number_format($incentive->jumlah_insentif, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($incentive->status == 'PAID')
                            <span class="badge badge-success">LUNAS</span>
                        @else
                            <span class="badge badge-danger">BELUM DIBAYAR</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($incentive->status == 'UNPAID' && $incentive->jumlah_insentif > 0)
                            <form action="{{ route('admin.incentives.mark-as-paid', $incentive) }}" method="POST" class="d-inline mark-as-paid-form">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success">
                                    <i class="fas fa-check"></i> Tandai Lunas
                                </button>
                            </form>
                        @elseif($incentive->status == 'PAID')
                            <span class="text-muted" style="font-size: 0.8rem;">Dibayar pada:<br>{{ \Carbon\Carbon::parse($incentive->paid_at)->format('d-m-Y H:i') }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada target yang ditetapkan untuk periode ini.</td>
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
        $('#incentive-report-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "order": [[ 4, "desc" ]]
        });

        // --- KODE JAVASCRIPT FINAL ---
        $('.mark-as-paid-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;

            Swal.fire({
                title: 'Anda yakin?',
                text: "Anda akan menandai insentif ini sebagai LUNAS. Aksi ini tidak dapat dibatalkan.",
                type: 'warning',  // <-- PERBAIKAN: Ubah 'icon' menjadi 'type'
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Tandai Lunas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.value) { // Pada versi ini, gunakan 'result.value'
                    form.submit();
                }
            })
        });

        // Tampilkan notifikasi sukses dari server
        if ($('#success-message').length) {
            Swal.fire({
                title: 'Berhasil!',
                text: $('#success-message').val(),
                type: 'success' // <-- PERBAIKAN: Ubah 'icon' menjadi 'type'
            });
        }
    });
</script>
@stop
