@php
    $appliesToAllSuppliers = old('applies_to_all_suppliers', $campaign->suppliers->isEmpty());
    $selectedSupplierIds = old('supplier_ids', $campaign->suppliers->pluck('id')->toArray());
@endphp
<h5>Aturan Khusus Pembelian</h5>
<div class="form-group">
    <label>Cakupan Supplier</label>
    <div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="allSuppliers" value="1" @if($appliesToAllSuppliers) checked @endif>
            <label class="form-check-label" for="allSuppliers">Berlaku untuk Semua Supplier</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="applies_to_all_suppliers" id="specificSuppliers" value="0" @if(!$appliesToAllSuppliers) checked @endif>
            <label class="form-check-label" for="specificSuppliers">Hanya untuk Supplier Tertentu</label>
        </div>
    </div>
</div>
<div class="form-group" id="supplierSelectionContainer" style="display: {{ $appliesToAllSuppliers ? 'none' : 'block' }};">
    <label>Pilih Supplier</label>
    <select name="supplier_ids[]" class="form-control select2" multiple="multiple" style="width: 100%;">
         @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}" @if(in_array($supplier->id, $selectedSupplierIds)) selected @endif>{{ $supplier->nama_supplier }}</option>
        @endforeach
    </select>
</div>
