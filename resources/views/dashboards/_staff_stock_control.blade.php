{{-- resources/views/dashboards/_staff_stock_control.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-danger">
            <h5><i class="fas fa-tasks"></i> Tugas Kontrol Stok</h5>
            <p>Selamat datang, <strong>{{ Auth::user()->nama }}</strong>! Berikut adalah ringkasan aktivitas kontrol stok yang telah Anda ajukan.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $adjustmentCount }}</h3>
                <p>Pengajuan Adjusment Bulan Ini</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
            <a href="{{ route('admin.stock-adjustments.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $mutationCount }}</h3>
                <p>Pengajuan Mutasi Bulan Ini</p>
            </div>
            <div class="icon"><i class="fas fa-truck-loading"></i></div>
            <a href="{{ route('admin.stock-mutations.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">5 Aktivitas Terakhir Anda</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Detail</th>
                            <th>Tanggal Diajukan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                            <tr>
                                <td>
                                    @if($activity->type == 'adjustment')
                                        <span class="badge badge-info">Adjusment</span>
                                    @else
                                        <span class="badge badge-success">Mutasi</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $activity->part->nama_part }}</strong>
                                    ({{ $activity->jumlah }})
                                </td>
                                <td>{{ $activity->created_at->format('d M Y H:i') }}</td>
                                <td class="text-center">
                                    @if($activity->status == 'PENDING_APPROVAL')
                                        <span class="badge badge-warning">Menunggu Persetujuan</span>
                                    @elseif($activity->status == 'APPROVED')
                                        <span class="badge badge-success">Disetujui</span>
                                    @elseif($activity->status == 'REJECTED')
                                        <span class="badge badge-danger">Ditolak</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $activity->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Anda belum memiliki aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
