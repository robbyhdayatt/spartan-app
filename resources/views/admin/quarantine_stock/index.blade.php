@extends('adminlte::page')

@section('title', 'Stok Karantina')

@section('content_header')
    <h1>Manajemen Stok Karantina</h1>
@stop

@section('content')

{{-- Menampilkan Notifikasi Global (Sukses atau Error dari session) --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@if(session('error'))
     <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- BLOK BARU: Menampilkan Error Validasi Form --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi kesalahan validasi!</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif


<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="quarantine-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-move-to-quarantine" data-toggle="pill" href="#content-move-to-quarantine" role="tab" aria-controls="content-move-to-quarantine" aria-selected="true">1. Pindahkan ke Karantina</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-process-quarantine" data-toggle="pill" href="#content-process-quarantine" role="tab" aria-controls="content-process-quarantine" aria-selected="false">2. Proses Stok Karantina</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="quarantine-tabs-content">

            {{-- TAB 1: FORM UNTUK MEMINDAHKAN STOK KE RAK KARANTINA --}}
            <div class="tab-pane fade show active" id="content-move-to-quarantine" role="tabpanel" aria-labelledby="tab-move-to-quarantine">
                <h5>Daftar Stok di Rak Penyimpanan</h5>
                <p>Pilih barang yang ingin Anda pindahkan ke rak karantina karena rusak atau alasan lain.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th>Gudang / Rak</th>
                                <th class="text-center">Stok Tersedia</th>
                                <th style="width: 130px;">Jumlah Pindah</th>
                                <th>Alasan Pemindahan</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($storageItems as $item)
                            <tr>
                                <form action="{{ route('admin.quarantine-stock.move') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="inventory_id" value="{{ $item->id }}">
                                    <td>{{ $item->part->nama_part }} <br><small class="text-muted">{{ $item->part->kode_part }}</small></td>
                                    <td>{{ $item->gudang->nama_gudang }} / {{ $item->rak->nama_rak }}</td>
                                    <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                    <td>
                                        <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" max="{{ $item->quantity }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Contoh: Kemasan rusak" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-arrow-right"></i> Pindahkan</button>
                                    </td>
                                </form>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada stok di rak penyimpanan yang bisa dipindahkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: DAFTAR STOK YANG SUDAH DI KARANTINA & SIAP DIPROSES --}}
            <div class="tab-pane fade" id="content-process-quarantine" role="tabpanel" aria-labelledby="tab-process-quarantine">
                <h5>Daftar Stok di Rak Karantina</h5>
                <p>Pilih aksi untuk barang yang sudah ada di rak karantina.</p>
                 <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th>Gudang / Rak</th>
                                <th class="text-center">Stok Karantina</th>
                                <th style="width: 350px;">Aksi Proses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quarantineItems as $item)
                                <tr>
                                    <td>{{ $item->part->nama_part }} <br><small class="text-muted">{{ $item->part->kode_part }}</small></td>
                                    <td>{{ $item->gudang->nama_gudang }} / {{ $item->rak->nama_rak }}</td>
                                    <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                    <td>
                                        <form action="{{ route('admin.quarantine-stock.process') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="inventory_id" value="{{ $item->id }}">
                                            <div class="input-group">
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" max="{{ $item->quantity }}" min="1" class="form-control form-control-sm" required>
                                                <select name="action" class="form-control form-control-sm action-select" required>
                                                    <option value="">-- Pilih Aksi --</option>
                                                    <option value="write_off">Hapus Permanen (Write-Off)</option>
                                                    <option value="return_to_stock">Kembalikan ke Rak</option>
                                                </select>
                                                <div class="input-group-append">
                                                     <button type="submit" class="btn btn-sm btn-success">Proses</button>
                                                </div>
                                            </div>
                                            <select name="destination_rak_id" class="form-control form-control-sm mt-1 destination-rak" style="display: none;">
                                                <option value="">-- Pilih Rak Tujuan --</option>
                                                @if(isset($storageRaks[$item->gudang_id]))
                                                    @foreach($storageRaks[$item->gudang_id] as $rak)
                                                        <option value="{{ $rak->id }}">{{ $rak->nama_rak }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="text" name="reason" class="form-control form-control-sm mt-1 reason-input" placeholder="Alasan Write-Off" style="display: none;" >
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada stok di rak karantina saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    // Script untuk menampilkan/menyembunyikan input tambahan berdasarkan aksi
    document.querySelectorAll('.action-select').forEach(selectElement => {
        selectElement.addEventListener('change', function() {
            const form = this.closest('form');
            const destinationRakSelect = form.querySelector('.destination-rak');
            const reasonInput = form.querySelector('.reason-input');

            // Reset keduanya terlebih dahulu
            destinationRakSelect.style.display = 'none';
            destinationRakSelect.required = false;
            reasonInput.style.display = 'none';
            reasonInput.required = false;

            if (this.value === 'return_to_stock') {
                destinationRakSelect.style.display = 'block';
                destinationRakSelect.required = true;
            } else if (this.value === 'write_off') {
                reasonInput.style.display = 'block';
                reasonInput.required = true;
            }
        });
    });
</script>
@stop
