@extends('adminlte::page')

@section('title', 'Manajemen Stok Karantina')

@section('content_header')
    <h1>Manajemen Stok Karantina</h1>
@stop

@section('content')
<div class="card">
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
                        <td>{{ $item->part->kode_part }} - {{ $item->part->nama_part }}</td>
                        <td>{{ $item->gudang->nama_gudang }}</td>
                        <td>{{ $item->rak->kode_rak }}</td>
                        <td><span class="badge badge-warning">{{ $item->rak->tipe_rak }}</span></td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            <button class="btn btn-primary btn-sm process-btn"
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
</div>

<div class="modal fade" id="processModal" tabindex="-1" role="dialog" aria-labelledby="processModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processModalLabel">Proses Stok Karantina</h5>
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
                                <input class="form-check-input" type="radio" name="action" id="action_return" value="return_to_stock" checked>
                                <label class="form-check-label" for="action_return">Kembalikan ke Stok Jual</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="action" id="action_write_off" value="write_off">
                                <label class="form-check-label" for="action_write_off">Hapus (Barang Rusak)</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="destination_rak_div">
                        <label for="destination_rak_id">Rak Tujuan (Penyimpanan)</label>
                        <select class="form-control" name="destination_rak_id" id="destination_rak_id" required>
                            {{-- Options will be populated by JS --}}
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
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#quarantine-table').DataTable();

        // Data rak penyimpanan yang di-pass dari controller
        const storageRaks = @json($storageRaks);

        // Modal Handler
        $('.process-btn').on('click', function() {
            const inventoryId = $(this).data('inventory-id');
            const partName = $(this).data('part-name');
            const maxQty = $(this).data('max-qty');
            const gudangId = $(this).data('gudang-id');

            $('#inventory_id').val(inventoryId);
            $('#part_name').text(partName);
            $('#quantity').val(maxQty).attr('max', maxQty);

            // Populate rak tujuan berdasarkan gudang
            const destinationSelect = $('#destination_rak_id');
            destinationSelect.empty();
            if(storageRaks[gudangId]) {
                storageRaks[gudangId].forEach(function(rak) {
                    destinationSelect.append(new Option(`${rak.kode_rak} - ${rak.nama_rak}`, rak.id));
                });
            } else {
                 destinationSelect.append('<option value="">Tidak ada rak penyimpanan di gudang ini</option>');
            }

            $('#processModal').modal('show');
        });

        // Toggling form fields based on action
        $('input[name="action"]').on('change', function() {
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
    });
</script>
@stop
