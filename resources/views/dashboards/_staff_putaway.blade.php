{{-- resources/views/dashboards/_staff_putaway.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-dolly-flatbed"></i> Tugas Penyimpanan Barang (Putaway)</h5>
            <p>Selamat datang, <strong>{{ Auth::user()->nama }}</strong>! Berikut adalah daftar barang yang telah lulus QC dan siap untuk disimpan ke rak.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Barang Menunggu Penyimpanan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nomor Penerimaan</th>
                            <th>Nomor PO</th>
                            <th>Tanggal Lolos QC</th>
                            <th>Supplier</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPutaway as $receiving)
                            <tr>
                                <td><strong>{{ $receiving->nomor_penerimaan }}</strong></td>
                                <td>{{ $receiving->purchaseOrder->nomor_po }}</td>
                                <td>{{ \Carbon\Carbon::parse($receiving->qc_at)->format('d M Y') }}</td>
                                <td>{{ $receiving->purchaseOrder->supplier->nama_supplier }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.putaway.form', $receiving->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-sign-in-alt"></i> Proses Simpan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Tidak ada barang yang perlu disimpan saat ini. Luar biasa! 🎉</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
