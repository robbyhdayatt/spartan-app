@extends('adminlte::page')

@section('title', 'Manajemen Campaign')

@section('plugins.Datatables', true)

@section('content_header')
    <h1>Manajemen Campaign</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Campaign Promosi</h3>
        <div class="card-tools">
            {{-- Tombol ini sekarang menjadi link ke halaman 'create' --}}
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Buat Campaign Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table id="campaigns-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nama Campaign</th>
                    <th>Tipe</th>
                    <th>Diskon (%)</th>
                    <th>Cakupan</th>
                    <th>Periode Aktif</th>
                    <th>Status</th>
                    <th style="width: 100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->nama_campaign }}</td>
                    <td><span class="badge badge-{{ $campaign->tipe == 'PENJUALAN' ? 'info' : 'warning' }}">{{ $campaign->tipe }}</span></td>
                    <td>{{ $campaign->discount_percentage }}%</td>
                    <td>
                        @if($campaign->parts->isEmpty()) <span class="badge badge-light">Semua Part</span>
                        @else <span class="badge badge-secondary">{{ $campaign->parts->count() }} Part</span> @endif

                        @if($campaign->tipe == 'PEMBELIAN')
                            @if($campaign->suppliers->isEmpty()) <span class="badge badge-light">Semua Supplier</span>
                            @else <span class="badge badge-dark">{{ $campaign->suppliers->count() }} Supplier</span> @endif
                        @endif
                    </td>
                    <td>{{ $campaign->tanggal_mulai->format('d M Y') }} - {{ $campaign->tanggal_selesai->format('d M Y') }}</td>
                    <td>@if($campaign->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-danger">Non-Aktif</span>@endif</td>
                    <td>
                        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn btn-warning btn-xs">Edit</a>
                        <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#campaigns-table').DataTable({ "responsive": true });
});
</script>
@stop
