@extends('adminlte::page')

@section('title', 'Laporan Kartu Stok')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Laporan Kartu Stok</h1>
@stop

@section('content')
    {{-- Form Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.stock-card') }}" method="GET">
                {{-- PERBAIKAN 1: Logika untuk user Kepala Gudang --}}
                @php
                    $user = Auth::user();
                    $isKepalaGudang = $user->jabatan->nama_jabatan === 'Kepala Gudang';
                @endphp
                <div class="row align-items-end">
                    {{-- Filter Part --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Spare Part</label>
                            <select name="part_id" id="part_id" class="form-control" required>
                                <option></option> {{-- Placeholder --}}
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}" {{ request('part_id') == $part->id ? 'selected' : '' }}>
                                        {{ $part->nama_part }} ({{ $part->kode_part }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filter Gudang --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Gudang</label>
                            @if($isKepalaGudang)
                                {{-- Jika Kepala Gudang, tampilkan sebagai teks biasa --}}
                                <input type="text" class="form-control" value="{{ $user->gudang->nama_gudang }}" readonly>
                                <input type="hidden" name="gudang_id" value="{{ $user->gudang_id }}">
                            @else
                                {{-- Jika bukan, tampilkan dropdown --}}
                                <select name="gudang_id" id="gudang_id" class="form-control">
                                    <option value="">Semua Gudang</option>
                                    @foreach($gudangs as $gudang)
                                         <option value="{{ $gudang->id }}" {{ request('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    {{-- Filter Tanggal --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                     <div class="col-md-2">
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                             <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                             </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Hasil --}}
    @if(request()->filled('part_id'))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pergerakan Stok</h3>
            <div class="card-tools">
                <a href="{{ route('admin.reports.stock-card.export', request()->all()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="stock-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Stok Sebelum</th>
                        <th class="text-right">Stok Sesudah</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $move)
                    <tr>
                        <td>{{ $move->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ $move->gudang->nama_gudang ?? 'N/A' }}</td>
                        <td>{{ $move->keterangan }}</td>
                        <td class="text-right font-weight-bold {{ $move->jumlah > 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($move->jumlah > 0 ? '+' : '') . $move->jumlah }}
                        </td>
                        <td class="text-right">{{ $move->stok_sebelum }}</td>
                        <td class="text-right">{{ $move->stok_sesudah }}</td>
                        <td>{{ $move->user->nama ?? 'Sistem' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada riwayat pergerakan untuk part ini pada periode yang dipilih.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
@stop

{{-- PERBAIKAN 2: Tambahkan CSS kustom untuk Select2 --}}
@push('css')
<style>
    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding-top: 0.375rem !important;
        padding-left: 0.75rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@endpush

@section('js')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#part_id').select2({
            placeholder: "--- Pilih Spare Part ---"
        });

        // Hanya inisialisasi Select2 untuk gudang jika elemennya ada (bukan readonly input)
        if ($('#gudang_id').is('select')) {
            $('#gudang_id').select2();
        }

        // Inisialisasi DataTable
        $('#stock-table').DataTable({
            "responsive": true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "order": [[ 0, "asc" ]]
        });
    });
</script>
@stop
