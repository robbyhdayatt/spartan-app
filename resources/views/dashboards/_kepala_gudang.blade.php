{{-- resources/views/dashboards/_kepala_gudang.blade.php --}}

{{-- BARIS UNTUK NOTIFIKASI PERSETUJUAN --}}
<div class="row">
    {{-- 1. Notifikasi Persetujuan Purchase Order --}}
    @if(isset($pendingApprovals['purchase_orders']) && !$pendingApprovals['purchase_orders']->isEmpty())
        <div class="col-md-4">
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-file-invoice"></i> Persetujuan PO</h5>
                <p>Ada <strong>{{ $pendingApprovals['purchase_orders']->count() }} PO</strong> menunggu persetujuan Anda.</p>
                {{-- Link ini sudah benar, mengarah ke index PO yang bisa diakses KG --}}
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-outline-dark">
                    Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- 2. Notifikasi Persetujuan Stock Adjustment --}}
    @if(isset($pendingApprovals['stock_adjustments']) && !$pendingApprovals['stock_adjustments']->isEmpty())
        <div class="col-md-4">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-edit"></i> Persetujuan Adjustment</h5>
                <p>Ada <strong>{{ $pendingApprovals['stock_adjustments']->count() }} Adjusment Stok</strong> menunggu.</p>
                {{-- Link ini diubah agar KG tetap bisa melihat daftar adjusment --}}
                <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-sm btn-outline-dark">
                    Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- 3. Notifikasi Persetujuan Stock Mutation --}}
    @if(isset($pendingApprovals['stock_mutations']) && !$pendingApprovals['stock_mutations']->isEmpty())
         <div class="col-md-4">
            <div class="alert alert-success">
                <h5><i class="icon fas fa-truck-loading"></i> Persetujuan Mutasi</h5>
                <p>Ada <strong>{{ $pendingApprovals['stock_mutations']->count() }} Mutasi Stok</strong> menunggu.</p>
                {{-- Link ini diubah agar KG tetap bisa melihat daftar mutasi --}}
                <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-sm btn-outline-dark">
                    Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endif
</div>

<div class="row">
    {{-- KOLOM KIRI --}}
    <div class="col-lg-8">
        {{-- DAFTAR TUGAS PERSETUJUAN --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Tugas Persetujuan</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php $hasTasks = false; @endphp

                    {{-- Link di bawah ini diubah agar mengarah ke halaman 'show' --}}
                    @if(isset($pendingApprovals['purchase_orders']) && !$pendingApprovals['purchase_orders']->isEmpty())
                        @php $hasTasks = true; @endphp
                        @foreach($pendingApprovals['purchase_orders'] as $po)
                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Persetujuan PO: <strong>{{ $po->nomor_po }}</strong>
                                <span class="badge badge-warning">PO</span>
                            </a>
                        @endforeach
                    @endif

                    @if(isset($pendingApprovals['stock_adjustments']) && !$pendingApprovals['stock_adjustments']->isEmpty())
                        @php $hasTasks = true; @endphp
                        @foreach($pendingApprovals['stock_adjustments'] as $adj)
                             {{-- Adjusment tidak memiliki halaman 'show', jadi kita arahkan ke index --}}
                             <a href="{{ route('admin.stock-adjustments.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                Adjustment: <strong>{{ $adj->part->nama_part }} ({{$adj->jumlah}})</strong>
                                <span class="badge badge-info">Adj</span>
                            </a>
                        @endforeach
                    @endif

                    @if(isset($pendingApprovals['stock_mutations']) && !$pendingApprovals['stock_mutations']->isEmpty())
                         @php $hasTasks = true; @endphp
                        @foreach($pendingApprovals['stock_mutations'] as $mut)
                            <a href="{{ route('admin.stock-mutations.show', $mut->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                               Mutasi: <strong>{{ $mut->part->nama_part }} ({{$mut->jumlah}})</strong>
                               <span class="badge badge-success">Mutasi</span>
                            </a>
                        @endforeach
                    @endif

                    @if(!$hasTasks)
                        <li class="list-group-item text-center">Tidak ada tugas persetujuan saat ini.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-lg-4">
        {{-- Info Box Nilai Stok --}}
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>Rp {{ number_format($stockValue, 0, ',', '.') }}</h3>
                <p>Total Nilai Stok (Gudang Anda)</p>
            </div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
            <a href="{{ route('admin.reports.stock-by-warehouse') }}" class="small-box-footer">Info lebih <i class="fas fa-arrow-circle-right"></i></a>
        </div>

        {{-- Info Box Stok Kritis --}}
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $criticalStockParts->count() }}</h3>
                <p>Part Stok Kritis (Gudang Anda)</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
             <a href="#" class="small-box-footer" data-toggle="modal" data-target="#criticalStockModal">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- MODAL UNTUK STOK KRITIS --}}
<div class="modal fade" id="criticalStockModal" tabindex="-1" role="dialog" aria-labelledby="criticalStockModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="criticalStockModalLabel">Daftar Stok Kritis</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Nama Part</th>
                    <th>Stok Saat Ini</th>
                    <th>Stok Minimum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($criticalStockParts as $part)
                <tr>
                    <td>{{ $part->nama_part }}</td>
                    <td><span class="badge badge-danger">{{ $part->total_stock }}</span></td>
                    <td>{{ $part->stok_minimum }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada stok kritis.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
