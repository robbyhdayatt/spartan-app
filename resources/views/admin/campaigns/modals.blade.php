<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Campaign Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route('admin.campaigns.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Campaign</label>
                        <input type="text" class="form-control" name="nama_campaign" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Campaign</label>
                        <select class="form-control campaign-type-select" name="tipe">
                            <option value="PENJUALAN">Penjualan</option>
                            <option value="PEMBELIAN">Pembelian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Part</label>
                        <select name="part_id" class="form-control part-select" required>
                            <option value="" disabled selected>Pilih Part</option>
                            @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-default-sell-price="{{ $part->harga_jual_default }}" data-default-buy-price="{{ $part->harga_beli_default }}">{{ $part->nama_part }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Promo (Rp)</label>
                        <input type="number" class="form-control" name="harga_promo" min="0" required>
                        <small class="form-text text-muted default-price-info"></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Campaign</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                     <div class="form-group">
                        <label>Nama Campaign</label>
                        <input type="text" class="form-control" id="edit_nama_campaign" name="nama_campaign" required>
                    </div>
                    <div class="form-group">
                        <label>Tipe Campaign</label>
                        <select class="form-control campaign-type-select" id="edit_tipe" name="tipe">
                            <option value="PENJUALAN">Penjualan</option>
                            <option value="PEMBELIAN">Pembelian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Part</label>
                        <select name="part_id" id="edit_part_id" class="form-control part-select" required>
                            @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-default-sell-price="{{ $part->harga_jual_default }}" data-default-buy-price="{{ $part->harga_beli_default }}">{{ $part->nama_part }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Promo (Rp)</label>
                        <input type="number" class="form-control" id="edit_harga_promo" name="harga_promo" min="0" required>
                        <small class="form-text text-muted default-price-info"></small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" class="form-control" id="edit_tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_is_active" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
