@extends('adminlte::page')

@section('title', 'Penerimaan Mutasi')

@section('content_header')
    <h1>Penerimaan Barang Mutasi</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Barang Dalam Perjalanan (In-Transit)</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nomor Mutasi</th>
                            <th>Tanggal Kirim</th>
                            <th>Part</th>
                            <th>Jumlah</th>
                            <th>Dari Gudang</th>
                            <th>Dikirim Oleh</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingMutations as $mutation)
                            <tr>
                                <td>{{ $mutation->nomor_mutasi }}</td>
                                <td>{{ $mutation->approved_at->format('d M Y H:i') }}</td>
                                <td>{{ $mutation->part->nama_part }}</td>
                                <td>{{ $mutation->jumlah }}</td>
                                <td>{{ $mutation->gudangAsal->nama_gudang }}</td>
                                <td>{{ $mutation->createdBy->nama }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.mutation-receiving.show', $mutation) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-box-open"></i> Terima Barang
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada barang mutasi yang sedang dalam perjalanan ke gudang Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $pendingMutations->links() }}
            </div>
        </div>
    </div>
@stop
