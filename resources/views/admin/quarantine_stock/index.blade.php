@extends('adminlte::page')

@section('title', 'Manajemen Stok Karantina')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Manajemen Stok Karantina</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Barang di Rak Karantina</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table id="quarantine-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Part</th>
                    <th>Gudang</th>
                    <th>Rak Karantina</th>
                    <th class="text-right">Total Qty</th>
                    <th style="width: 150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quarantineItems as $item)
                <tr>
                    <td>{{ $item->part->nama_part }} <br><small class="text-muted">{{ $item->part->kode_part }}</small></td>
                    <td>{{ $item->gudang->nama_gudang }}</td>
                    <td>{{ $item->rak->kode_rak }}</td>
                    <td class="text-right font-weight-bold">{{ $item->total_quantity }}</td>
                    <td>
                        <button class="btn btn-primary btn-xs process-btn"
                                data-part-id="{{ $item->part_id }}"
                                data-rak-id="{{ $item->rak_id }}"
                                data-gudang-id="{{ $item->gudang_id }}"
                                data-max-qty="{{ $item->total_quantity }}"
                                data-part-name="{{ $item->part->nama_part }}"
                                data-toggle="modal" data-target="#processModal">
                            Proses
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada stok di rak karantina.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Proses Karantina --}}
<div class="modal fade" id="processModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proses Stok Karantina</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.quarantine-stock.process') }}" method="POST">
                @csrf
                <input type="hidden" name="part_id" id="part_id">
                <input type="hidden" name="rak_id" id="rak_id">
                <input type="hidden" name="gudang_id" id="gudang_id">

                <div class="modal-body">
                    <p>Anda akan memproses part: <strong id="part_name"></strong></p>

                    <div class="form-group">
                        <label for="quantity">Jumlah yang Diproses</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="action">Pilih Aksi</label>
                        <select name="action" id="action" class="form-control" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="return_to_stock">Kembalikan ke Stok Penjualan</option>
                            <option value="write_off">Ajukan Hapus Stok (Write-Off)</option>
                        </select>
                    </div>

                    <div class="form-group" id="destination-rak-group" style="display: none;">
                        <label for="destination_rak_id">Pilih Rak Tujuan</label>
                        <select name="destination_rak_id" id="destination_rak_id" class="form-control select2" style="width: 100%;">
                            {{-- Options populated by JS --}}
                        </select>
                    </div>

                    <div class="form-group" id="reason-group" style="display: none;">
                        <label for="reason">Alasan Write-Off</label>
                        <textarea name="reason" id="reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(document).ready(function() {
    $('#quarantine-table').DataTable({ "responsive": true, "autoWidth": false });
    $('.select2').select2({ dropdownParent: $('#processModal') });

    const storageRaksByGudang = @json($storageRaks);

    $('.process-btn').on('click', function() {
        const partId = $(this).data('part-id');
        const rakId = $(this).data('rak-id');
        const gudangId = $(this).data('gudang-id');
        const maxQty = $(this).data('max-qty');
        const partName = $(this).data('part-name');

        $('#part_id').val(partId);
        $('#rak_id').val(rakId);
        $('#gudang_id').val(gudangId);

        $('#part_name').text(partName);
        $('#quantity').val(maxQty).attr('max', maxQty);

        const destinationRakSelect = $('#destination_rak_id');
        destinationRakSelect.html('<option value="">-- Pilih Rak Tujuan --</option>');

        if (storageRaksByGudang[gudangId]) {
            storageRaksByGudang[gudangId].forEach(function(rak) {
                destinationRakSelect.append(new Option(`${rak.kode_rak} - ${rak.nama_rak}`, rak.id));
            });
        }
    });

    $('#action').on('change', function() {
        const selectedAction = $(this).val();
        $('#destination-rak-group').hide();
        $('#destination_rak_id').prop('required', false);
        $('#reason-group').hide();
        $('#reason').prop('required', false);

        if (selectedAction === 'return_to_stock') {
            $('#destination-rak-group').show();
            $('#destination_rak_id').prop('required', true);
        } else if (selectedAction === 'write_off') {
            $('#reason-group').show();
            $('#reason').prop('required', true);
        }
    }).trigger('change');
});
</script>
@endpush
