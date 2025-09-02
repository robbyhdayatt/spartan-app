@extends('adminlte::page')

@section('title', 'Proses Penyimpanan Barang')

@section('content_header')
    <h1>Proses Penyimpanan (Putaway)</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('admin.putaway.store', $receiving->id) }}" method="POST">
        @csrf
        <div class="card-header">
            <h3 class="card-title">No. Penerimaan: {{ $receiving->nomor_penerimaan }}</h3>
        </div>
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
            <p>Pilih rak tujuan untuk setiap item yang telah lolos QC.</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th style="width: 150px">Qty Lolos QC</th>
                        <th style="width: 300px">Simpan ke Rak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receiving->details as $detail)
                    <tr>
                        <td>{{ $detail->part->nama_part }} ({{ $detail->part->kode_part }})</td>
                        <td>
                            <input type="text" class="form-control" value="{{ $detail->qty_lolos_qc }}" readonly>
                        </td>
                        <td>
                             <select name="items[{{ $detail->id }}][rak_id]" class="form-control" required>
                                <option value="" disabled selected>Pilih Rak</option>
                                @foreach($raks as $rak)
                                    <option value="{{ $rak->id }}">{{ $rak->nama_rak }} ({{ $rak->kode_rak }})</option>
                                @endforeach
                             </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div classs="card-footer">
            <button type="submit" class="btn btn-primary">Simpan ke Rak & Update Stok</button>
            <a href="{{ route('admin.putaway.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop
