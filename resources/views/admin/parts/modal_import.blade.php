<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Parts dari Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route('admin.parts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Pilih file (.xlsx, .xls)</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <p class="text-muted">
                        Pastikan file Excel Anda memiliki header kolom berikut: <br>
                        `kode_part`, `nama_part`, `brand`, `kategori`, `satuan`, `stok_minimum`, `harga_beli_default`, `harga_jual_default`
                    </p>
                    <a href="{{ asset('templates/template_import_part.xlsx') }}" download>Unduh Template</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
