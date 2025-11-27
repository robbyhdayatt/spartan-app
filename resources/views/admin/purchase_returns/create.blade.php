@extends('adminlte::page')
@section('title', 'Buat Retur Pembelian')
@section('content_header')
    <h1>Buat Retur Pembelian</h1>
@stop
@section('content')
<div class="card">
    <form action="{{ route('admin.purchase-returns.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Pilih Dokumen Penerimaan</label>
                    <select id="receiving-select" name="receiving_id" class="form-control" required>
                        <option value="" disabled selected>--- Pilih Penerimaan dengan item Gagal QC ---</option>
                        @foreach($receivings as $receiving)
                        <option value="{{ $receiving->id }}">{{ $receiving->nomor_penerimaan }} - {{ $receiving->purchaseOrder->supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Tanggal Retur</label>
                    <input type="date" class="form-control" name="tanggal_retur" value="{{ now()->format('Y-m-d') }}" required>
                </div>
            </div>
             <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan untuk retur"></textarea>
            </div>

            <h5 class="mt-4">Item untuk Diretur</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th style="width: 200px">Qty Gagal QC (Tersedia)</th>
                        <th style="width: 200px">Qty Diretur</th>
                        <th>Alasan Retur</th>
                    </tr>
                </thead>
                <tbody id="return-items-table">
                    {{-- Items loaded via JS --}}
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Dokumen Retur</button>
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#receiving-select').on('change', function() {
        let receivingId = $(this).val();
        let tableBody = $('#return-items-table');

        tableBody.html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

        if (receivingId) {
            // 1. Buat template URL dengan placeholder ':id'
            let urlTemplate = "{{ route('admin.api.receivings.failed-items', ['receiving' => ':id']) }}";

            // 2. Ganti placeholder ':id' dengan receivingId yang dipilih
            let url = urlTemplate.replace(':id', receivingId);

            $.getJSON(url, function(data) {
                tableBody.empty();
                if(data.length > 0) {
                    data.forEach(function(item) {
                        let availableToReturn = item.qty_available_for_return;
                        let row = `
                            <tr>
                                <td>${item.part.nama_part}</td>
                                <td><input type="text" class="form-control" value="${availableToReturn}" readonly></td>
                                <td><input type="number" name="items[${item.id}][qty_retur]" class="form-control" min="1" max="${availableToReturn}" value="${availableToReturn}" required></td>
                                <td><input type="text" name="items[${item.id}][alasan]" class="form-control" value="${item.catatan_qc || ''}" placeholder="Alasan retur"></td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.html('<tr><td colspan="4" class="text-center">Tidak ada item yang bisa diretur dari penerimaan ini.</td></tr>');
                }
            }).fail(function() {
                // Tambahan: Memberi pesan jika API gagal diakses
                tableBody.html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat item. Periksa koneksi atau hubungi administrator.</td></tr>');
            });
        } else {
            tableBody.empty();
        }
    });
});
</script>
@stop
