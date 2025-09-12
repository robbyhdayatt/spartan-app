{{-- resources/views/dashboards/_staff_receiving.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-success">
            <h5><i class="fas fa-box-open"></i> Tugas Penerimaan Barang</h5>
            <p>Selamat datang, <strong>{{ Auth::user()->nama }}</strong>! Di bawah ini adalah daftar tugas penerimaan
                untuk gudang Anda.</p>
        </div>
    </div>
</div>

<div class="row">
    {{-- Tabel untuk Penerimaan dari Purchase Order --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dari Purchase Order (Supplier)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Supplier</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPoReceivings as $po)
                            <tr>
                                <td><strong>{{ $po->nomor_po }}</strong></td>
                                <td>{{ $po->supplier->nama_supplier }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.receivings.create', ['purchase_order_id' => $po->id]) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fas fa-plus-circle"></i> Buat Penerimaan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">Tidak ada tugas penerimaan dari PO.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabel untuk Penerimaan dari Mutasi --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dari Mutasi Gudang</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nomor Mutasi</th>
                            <th>Dari Gudang</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingMutationReceivings as $mutation)
                            <tr>
                                <td><strong>{{ $mutation->nomor_mutasi }}</strong></td>
                                <td>{{ $mutation->gudangAsal->nama_gudang }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.mutation-receiving.show', $mutation) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-box-open"></i> Terima Barang
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">Tidak ada tugas penerimaan dari mutasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
