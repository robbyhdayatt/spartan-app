@extends('adminlte::page')

@section('title', 'Laporan Kartu Stok')

@section('content_header')
    <h1>Laporan Kartu Stok</h1>
@stop

@section('content')
    {{-- Form Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pilih Part</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.stock-card') }}" method="GET">
                <div class="form-group row">
                    <label for="part_id" class="col-sm-2 col-form-label">Spare Part</label>
                    <div class="col-sm-8">
                        <select name="part_id" id="part_id" class="form-control" required>
                            <option value="" disabled selected>--- Pilih Spare Part ---</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}" {{ request('part_id') == $part->id ? 'selected' : '' }}>
                                    {{ $part->nama_part }} ({{ $part->kode_part }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
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
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th>Tipe Gerakan</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Stok Sebelum</th>
                        <th class="text-right">Stok Sesudah</th>
                        <th>Referensi</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $move)
                    <tr>
                        <td>{{ $move->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ $move->gudang->nama_gudang }}</td>
                        <td>{{ str_replace('_', ' ', $move->tipe_gerakan) }}</td>
                        <td class="text-right font-weight-bold {{ $move->jumlah > 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($move->jumlah > 0 ? '+' : '') . $move->jumlah }}
                        </td>
                        <td class="text-right">{{ $move->stok_sebelum }}</td>
                        <td class="text-right">{{ $move->stok_sesudah }}</td>
                        <td>{{ $move->referensi }}</td>
                        <td>{{ $move->user->nama }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada riwayat pergerakan untuk part ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
@stop

{{-- Tambahkan Select2 untuk dropdown yang lebih baik --}}
@section('plugins.Select2', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#part_id').select2({
            placeholder: "--- Pilih Spare Part ---"
        });
    });
</script>
@stop
