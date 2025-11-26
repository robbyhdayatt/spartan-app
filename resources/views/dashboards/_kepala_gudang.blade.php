@extends('adminlte::page')

@section('title', 'Dashboard Kepala Gudang')

@section('content_header')
    <h1>Dashboard Kepala Gudang</h1>
@stop

@section('content')
    <div class="row">
        {{-- Alert Approval --}}
        @if($needApprovalPO > 0 || $needApprovalAdj > 0)
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                Ada transaksi yang membutuhkan persetujuan Anda:
                <ul>
                    @if($needApprovalPO > 0) <li>{{ $needApprovalPO }} Purchase Order menunggu approval. <a href="{{ route('admin.purchase-orders.index') }}">Lihat</a></li> @endif
                    @if($needApprovalAdj > 0) <li>{{ $needApprovalAdj }} Penyesuaian Stok menunggu approval. <a href="{{ route('admin.stock-adjustments.index') }}">Lihat</a></li> @endif
                </ul>
            </div>
        </div>
        @endif

        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\InventoryBatch::where('gudang_id', Auth::user()->gudang_id)->count() }}</h3>
                    <p>Batch Stok Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-cubes"></i></div>
                <a href="{{ route('admin.reports.stock-by-warehouse') }}" class="small-box-footer">Lihat Stok <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $recentMovements->count() }}</h3>
                    <p>Pergerakan Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-dolly"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $needApprovalPO + $needApprovalAdj }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon"><i class="fas fa-stamp"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">5 Transaksi Stok Terakhir</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Part</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $m)
                            <tr>
                                <td>{{ $m->created_at->diffForHumans() }}</td>
                                <td>{{ $m->part->nama_part }}</td>
                                <td>
                                    <span class="badge bg-{{ $m->jumlah > 0 ? 'success' : 'danger' }}">
                                        {{ $m->tipe_gerakan }}
                                    </span>
                                </td>
                                <td>{{ $m->jumlah }}</td>
                                <td>{{ $m->user->nama }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">Belum ada transaksi hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop