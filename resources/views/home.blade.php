@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    {{--
      Memuat partial view dashboard berdasarkan peran pengguna.
      Variabel $data (yang merupakan array) akan "dibongkar"
      sehingga setiap key di dalamnya (seperti 'poToday', 'salesToday')
      menjadi variabel mandiri ($poToday, $salesToday) di dalam view yang di-include.
    --}}
    @include($viewName, $data)
@stop

@push('css')
<style>
    .list-group-item-action {
        color: #495057;
    }
    .list-group-item-action:hover, .list-group-item-action:focus {
        background-color: #f8f9fa;
        color: #1f2d3d;
    }
</style>
@endpush
