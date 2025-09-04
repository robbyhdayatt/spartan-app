@extends('adminlte::page')

@section('title', 'Profil Saya')

@section('content_header')
    <h1>Profil Saya</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 200px;">Nama</th>
                        <td>{{ $user->nama }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td>{{ $user->nik }}</td>
                    </tr>
                    <tr>
                        <th>Jabatan</th>
                        <td>{{ $user->jabatan->nama_jabatan ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Gudang (Jika Ada)</th>
                        <td>{{ $user->gudang->nama_gudang ?? 'Tidak terikat gudang' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop
