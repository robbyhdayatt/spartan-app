@extends('adminlte::page')

@section('title', 'Tambah Kategori Diskon Konsumen')

@section('content_header')
    <h1>Tambah Kategori Diskon Konsumen</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.customer-discount-categories.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="nama_kategori">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}" required>
            </div>

            <div class="form-group">
                <label for="discount_percentage">Persentase Diskon (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" required min="0" max="100" step="0.01">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-group">
                <label for="konsumen_ids">Pilih Konsumen</label>
                <select name="konsumen_ids[]" id="konsumen_ids" class="form-control select2" multiple="multiple">
                    @foreach($konsumens as $konsumen)
                        <option value="{{ $konsumen->id }}">{{ $konsumen->nama_konsumen }}</option>
                    @endforeach
                </select>
            </div>
             <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">Aktifkan Kategori</label>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.customer-discount-categories.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('plugins.Select2', true)

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih konsumen...",
        });
    });
</script>
@stop