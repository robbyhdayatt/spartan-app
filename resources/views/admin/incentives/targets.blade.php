@extends('adminlte::page')
@section('title', 'Set Target Penjualan')
@section('content_header')<h1>Set Target Penjualan Sales</h1>@stop
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Filter Periode</h3></div>
    <div class="card-body">
        <form action="{{ route('admin.incentives.targets') }}" method="GET">
            <div class="row">
                <div class="col-md-5 form-group">
                    <label>Tahun</label>
                    <select name="tahun" class="form-control">
                        @for ($y = now()->year; $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-5 form-group">
                    <label>Bulan</label>
                    <select name="bulan" class="form-control">
                        @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label> <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <form action="{{ route('admin.incentives.targets.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <div class="card-header"><h3 class="card-title">Input Target untuk {{ \Carbon\Carbon::create()->month($bulan)->format('F') }} {{ $tahun }}</h3></div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <table class="table table-bordered">
                <thead><tr><th>Nama Sales</th><th style="width: 300px;">Target Penjualan (Rp)</th></tr></thead>
                <tbody>
                    @foreach($salesUsers as $sales)
                    <tr>
                        <td>{{ $sales->nama }}</td>
                        <td><input type="number" name="targets[{{ $sales->id }}]" class="form-control" value="{{ old('targets.'.$sales->id, $existingTargets[$sales->id] ?? 0) }}" min="0" required></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"><button type="submit" class="btn btn-primary">Simpan Target</button></div>
    </form>
</div>
@stop
