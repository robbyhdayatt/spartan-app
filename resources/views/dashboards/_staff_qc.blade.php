{{-- resources/views/dashboards/_staff_qc.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-warning">
            <h5><i class="fas fa-check-circle"></i> Tugas Quality Control</h5>
            <p>Selamat datang, <strong>{{ Auth::user()->nama }}</strong>! Di bawah ini adalah daftar penerimaan barang yang menunggu untuk diperiksa kualitasnya.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Barang Menunggu Pemeriksaan Kualitas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nomor Penerimaan</th>
                            <th>Nomor PO</th>
                            <th>Tanggal Diterima</th>
                            <th>Supplier</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingQc as $receiving)
                            <tr>
                                <td><strong>{{ $receiving->nomor_penerimaan }}</strong></td>
                                <td>{{ $receiving->purchaseOrder->nomor_po }}</td>
                                <td>{{ \Carbon\Carbon::parse($receiving->tanggal_terima)->format('d M Y') }}</td>
                                <td>{{ $receiving->purchaseOrder->supplier->nama_supplier }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.qc.form', $receiving->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-search"></i> Periksa Sekarang
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Tidak ada tugas pemeriksaan kualitas saat ini. Hebat! ✨</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
