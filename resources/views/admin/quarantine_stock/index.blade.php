@extends('adminlte::page')

@section('title', 'Manajemen Stok Karantina')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Manajemen Stok Karantina</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.quarantine-stock.process-bulk') }}" method="POST" id="bulk-process-form">
        @csrf
        <div class="card-header">
            <h3 class="card-title">Daftar Stok di Karantina</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="bulk-process-btn" disabled>
                    <i class="fas fa-cogs"></i> Proses Item Terpilih
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table id="quarantine-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px;"><input type="checkbox" id="select-all"></th>
                        <th>Part</th>
                        <th>Gudang</th>
                        <th>Rak Karantina</th>
                        <th>Tipe Rak</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quarantineItems as $item)
                        <tr>
                            <td><input type="checkbox" class="item-checkbox" name="inventory_ids[]" value="{{ $item->id }}" data-gudang-id="{{ $item->gudang_id }}"></td>
                            <td>{{ $item->part->kode_part }} - {{ $item->part->nama_part }}</td>
                            <td>{{ $item->gudang->nama_gudang }}</td>
                            <td>{{ $item->rak->kode_rak }}</td>
                            <td><span class="badge badge-warning">{{ $item->rak->tipe_rak }}</span></td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm process-btn"
                                        data-inventory-id="{{ $item->id }}"
                                        data-part-name="{{ $item->part->nama_part }}"
                                        data-gudang-id="{{ $item->gudang_id }}"
                                        data-max-qty="{{ $item->quantity }}">
                                    Proses
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

{{-- Modal untuk Proses SATUAN --}}
<div class="modal fade" id="processModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proses Stok Karantina (Satuan)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.quarantine-stock.process') }}" method="POST">
                @csrf
                <input type="hidden" name="inventory_id" id="inventory_id">
                <div class="modal-body">
                    <p>Part: <strong id="part_name"></strong></p>
                    <div class="form-group">
                        <label for="quantity">Jumlah yang akan diproses</label>
                        <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Aksi</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input action-radio" type="radio" name="action" value="return_to_stock" checked>
                                <label class="form-check-label">Kembalikan ke Stok Jual</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input action-radio" type="radio" name="action" value="write_off">
                                <label class="form-check-label">Hapus (Barang Rusak)</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="destination_rak_div">
                        <label for="destination_rak_id">Rak Tujuan (Penyimpanan)</label>
                        <select class="form-control" name="destination_rak_id" id="destination_rak_id" required>
                            {{-- Options populated by JS --}}
                        </select>
                    </div>
                    <div class="form-group" id="reason_div" style="display: none;">
                        <label for="reason">Alasan Penghapusan</label>
                        <textarea class="form-control" name="reason" id="reason" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal untuk Proses MASSAL (BULK) --}}
<div class="modal fade" id="bulkProcessModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proses Stok Karantina Terpilih</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda telah memilih <strong id="bulk_item_count"></strong> item untuk diproses. Seluruh kuantitas dari item yang dipilih akan diproses.</p>
                <div class="form-group">
                    <label>Pilih Aksi</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input bulk-action-radio" type="radio" name="bulk_action" value="return_to_stock" checked>
                            <label class="form-check-label">Kembalikan ke Stok Jual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input bulk-action-radio" type="radio" name="bulk_action" value="write_off">
                            <label class="form-check-label">Hapus (Barang Rusak)</label>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="bulk_destination_rak_div">
                    <div class="alert alert-info">
                        <strong>Perhatian!</strong> Semua item yang dipilih harus berasal dari gudang yang sama untuk dapat dikembalikan ke stok.
                    </div>
                    <label for="bulk_destination_rak_id">Rak Tujuan (Penyimpanan)</label>
                    <select class="form-control" id="bulk_destination_rak_id" required>
                        {{-- Options populated by JS --}}
                    </select>
                </div>
                <div class="form-group" id="bulk_reason_div" style="display: none;">
                    <label for="bulk_reason">Alasan Penghapusan</label>
                    <textarea class="form-control" id="bulk_reason" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submit-bulk-process">Simpan Proses Massal</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#quarantine-table').DataTable();
    const storageRaks = @json($storageRaks);

    // --- LOGIKA UNTUK PROSES SATUAN (YANG SEBELUMNYA HILANG) ---
    $('.process-btn').on('click', function() {
        const inventoryId = $(this).data('inventory-id');
        const partName = $(this).data('part-name');
        const maxQty = $(this).data('max-qty');
        const gudangId = $(this).data('gudang-id');

        $('#inventory_id').val(inventoryId);
        $('#part_name').text(partName);
        $('#quantity').val(maxQty).attr('max', maxQty);

        const destinationSelect = $('#destination_rak_id');
        destinationSelect.empty();
        if (storageRaks[gudangId]) {
            storageRaks[gudangId].forEach(function(rak) {
                destinationSelect.append(new Option(`${rak.kode_rak} - ${rak.nama_rak}`, rak.id));
            });
        } else {
            destinationSelect.append('<option value="">Tidak ada rak penyimpanan di gudang ini</option>');
        }
        $('#processModal').modal('show');
    });

    $('.action-radio').on('change', function() {
        if (this.value === 'return_to_stock') {
            $('#destination_rak_div').show();
            $('#destination_rak_id').prop('required', true);
            $('#reason_div').hide();
            $('#reason').prop('required', false);
        } else {
            $('#destination_rak_div').hide();
            $('#destination_rak_id').prop('required', false);
            $('#reason_div').show();
            $('#reason').prop('required', true);
        }
    });

    // --- LOGIKA UNTUK PROSES MASSAL ---
    function updateBulkButtonState() {
        const checkedCount = $('.item-checkbox:checked').length;
        $('#bulk-process-btn').prop('disabled', checkedCount === 0);
    }

    $('#select-all').on('click', function() {
        $('.item-checkbox').prop('checked', this.checked);
        updateBulkButtonState();
    });

    $('#quarantine-table').on('click', '.item-checkbox', function() {
        updateBulkButtonState();
        if (!this.checked) {
            $('#select-all').prop('checked', false);
        }
    });

    $('#bulk-process-btn').on('click', function() {
        const selectedItems = $('.item-checkbox:checked');
        $('#bulk_item_count').text(selectedItems.length);

        const gudangIds = new Set();
        selectedItems.each(function() {
            gudangIds.add($(this).data('gudang-id'));
        });

        const bulkDestinationSelect = $('#bulk_destination_rak_id');
        bulkDestinationSelect.empty().prop('disabled', false);

        if (gudangIds.size === 1) {
            const gudangId = gudangIds.values().next().value;
            if (storageRaks[gudangId] && storageRaks[gudangId].length > 0) {
                storageRaks[gudangId].forEach(function(rak) {
                    bulkDestinationSelect.append(new Option(`${rak.kode_rak} - ${rak.nama_rak}`, rak.id));
                });
            } else {
                 bulkDestinationSelect.append('<option value="">Tidak ada rak penyimpanan di gudang ini</option>').prop('disabled', true);
            }
        } else {
            bulkDestinationSelect.append('<option value="">Pilih item dari gudang yang sama</option>').prop('disabled', true);
        }

        $('#bulkProcessModal').modal('show');
    });

    $('.bulk-action-radio').on('change', function() {
        if (this.value === 'return_to_stock') {
            $('#bulk_destination_rak_div').show();
            $('#bulk_reason_div').hide();
        } else {
            $('#bulk_destination_rak_div').hide();
            $('#bulk_reason_div').show();
        }
    });

    $('#submit-bulk-process').on('click', function() {
        $('#bulk-process-form .bulk-fields').remove();

        const action = $('input[name="bulk_action"]:checked').val();
        $('<input>').attr({ type: 'hidden', name: 'action', value: action, class: 'bulk-fields'}).appendTo('#bulk-process-form');

        if (action === 'return_to_stock') {
            const rakId = $('#bulk_destination_rak_id').val();
             if(!rakId){
                alert('Silakan pilih rak tujuan atau pastikan item berasal dari gudang yang sama.');
                return;
            }
            $('<input>').attr({ type: 'hidden', name: 'destination_rak_id', value: rakId, class: 'bulk-fields'}).appendTo('#bulk-process-form');
        } else {
            const reason = $('#bulk_reason').val();
            $('<input>').attr({ type: 'hidden', name: 'reason', value: reason, class: 'bulk-fields'}).appendTo('#bulk-process-form');
        }

        $('#bulk-process-form').submit();
    });
});
</script>
@stop
