{{-- resources/views/dashboards/_pj_gudang.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-primary">
            <h5><i class="fas fa-warehouse"></i> Dashboard Operasional Gudang</h5>
            <p>Selamat datang, <strong>{{ Auth::user()->nama }}</strong>! Berikut adalah ringkasan status operasional di gudang Anda saat ini.</p>
        </div>
    </div>
</div>

{{-- Baris untuk Info Box Status --}}
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $pendingReceivingCount }}</h3>
                <p>Menunggu Penerimaan</p>
            </div>
            <div class="icon"><i class="fas fa-truck-loading"></i></div>
            <a href="{{ route('admin.receivings.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pendingQcCount }}</h3>
                <p>Menunggu Quality Control</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.qc.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $pendingPutawayCount }}</h3>
                <p>Menunggu Penyimpanan</p>
            </div>
            <div class="icon"><i class="fas fa-dolly-flatbed"></i></div>
            <a href="{{ route('admin.putaway.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Baris untuk Shortcut Aksi --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Akses Cepat</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-app bg-primary">
                    <i class="fas fa-shopping-cart"></i> Buat PO
                </a>
                <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-app bg-info">
                    <i class="fas fa-edit"></i> Buat Adjusment
                </a>
                <a href="{{ route('admin.stock-mutations.create') }}" class="btn btn-app bg-success">
                    <i class="fas fa-truck-loading"></i> Buat Mutasi
                </a>
                <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-app bg-danger">
                    <i class="fas fa-undo"></i> Buat Retur
                </a>
            </div>
        </div>
    </div>
</div>
