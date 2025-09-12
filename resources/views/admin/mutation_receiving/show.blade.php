@extends('adminlte::page')

@section('title', 'Proses Penerimaan Mutasi')

@section('content_header')
    <h1>Proses Penerimaan Mutasi: {{ $mutation->nomor_mutasi }}</h1>
@stop

@section('content')
<form action="{{ route('admin.mutation-receiving.receive', $mutation) }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Detail Barang</h3></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Nomor Mutasi:</b> {{ $mutation->nomor_mutasi }}</li>
                        <li class="list-group-item"><b>Part:</b> {{ $mutation->part->nama_part }}</li>
                        <li class="list-group-item"><b>Jumlah Dikirim:</b> <span class="badge badge-primary">{{ $mutation->jumlah }}</span></li>
                        <li class="list-group-item"><b>Dari Gudang:</b> {{ $mutation->gudangAsal->nama_gudang }}</li>
                        <li class="list-group-item"><b>Rak Asal:</b> {{ $mutation->rakAsal->nama_rak }}</li>
                        <li class="list-group-item"><b>Tanggal Kirim:</b> {{ $mutation->approved_at->format('d M Y, H:i') }}</li>
                        <li class="list-group-item"><b>Keterangan:</b> {{ $mutation->keterangan ?? '-' }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-success">
                 <div class="card-header"><h3 class="card-title">Konfirmasi Penerimaan</h3></div>
                 <div class="card-body">
                    <p>Silakan pilih rak tujuan untuk menyimpan barang ini.</p>
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
                        <label for="rak_tujuan_id">Pilih Rak Penyimpanan</label>
                        <select name="rak_tujuan_id" id="rak_tujuan_id" class="form-control" required>
                            <option value="">-- Pilih Rak --</option>
                            @foreach ($raks as $rak)
                                <option value="{{ $rak->id }}">{{ $rak->nama_rak }}</option>
                            @endforeach
                        </select>
                     </div>
                 </div>
                 <div class="card-footer">
                     <button type="submit" class="btn btn-success">
                         <i class="fas fa-check-circle"></i> Konfirmasi Terima Barang
                     </button>
                     <a href="{{ route('admin.mutation-receiving.index') }}" class="btn btn-secondary">Batal</a>
                 </div>
            </div>
        </div>
    </div>
</form>
@stop
