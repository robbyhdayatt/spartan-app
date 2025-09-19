<h5>Aturan Khusus Penjualan (Kategori Diskon Tambahan)</h5>
<p class="text-muted small">
    Buat kategori untuk memberikan diskon tambahan kepada grup konsumen tertentu. Diskon ini akan ditambahkan di atas diskon utama.
</p>
<div id="categoryRepeaterContainer">
    @foreach($campaign->categories as $index => $category)
        <div class="category-item border rounded p-3 mb-3">
            <button type="button" class="close" aria-label="Close" onclick="$(this).parent().remove();"><span aria-hidden="true">&times;</span></button>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Nama Kategori</label>
                    <input type="text" class="form-control" name="categories[{{ $index }}][nama]" value="{{ $category->nama_kategori }}" placeholder="Cth: Bengkel Prioritas">
                </div>
                <div class="col-md-2 form-group">
                    <label>Diskon (%)</label>
                    <input type="number" class="form-control" name="categories[{{ $index }}][diskon]" value="{{ $category->discount_percentage }}" min="0" max="100" step="0.01">
                </div>
                <div class="col-md-6 form-group">
                    <label>Pilih Konsumen untuk Kategori ini</label>
                    <select name="categories[{{ $index }}][konsumen_ids][]" class="form-control select2" multiple="multiple" style="width: 100%;">
                        @php $selectedKonsumenIds = $category->konsumens->pluck('id')->toArray(); @endphp
                        @foreach($konsumens as $konsumen)
                            <option value="{{ $konsumen->id }}" @if(in_array($konsumen->id, $selectedKonsumenIds)) selected @endif>{{ $konsumen->nama_konsumen }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endforeach
</div>
<button type="button" class="btn btn-sm btn-outline-success mt-2" id="addCategoryBtn"><i class="fas fa-plus"></i> Tambah Kategori Diskon</button>

{{-- Template untuk Kategori Diskon BARU (disembunyikan) --}}
<div id="categoryTemplate" style="display: none;">
    <div class="category-item border rounded p-3 mb-3">
        <button type="button" class="close" aria-label="Close" onclick="$(this).parent().remove();"><span aria-hidden="true">&times;</span></button>
        <div class="row">
            <div class="col-md-4 form-group"><label>Nama Kategori</label><input type="text" class="form-control" name="categories[__INDEX__][nama]" placeholder="Cth: Bengkel Prioritas"></div>
            <div class="col-md-2 form-group"><label>Diskon (%)</label><input type="number" class="form-control" name="categories[__INDEX__][diskon]" min="0" max="100" step="0.01"></div>
            <div class="col-md-6 form-group">
                <label>Pilih Konsumen untuk Kategori ini</label>
                <select name="categories[__INDEX__][konsumen_ids][]" class="form-control select2-template" multiple="multiple" style="width: 100%;">
                    @foreach($konsumens as $konsumen)
                        <option value="{{ $konsumen->id }}">{{ $konsumen->nama_konsumen }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
