@extends('adminlte::page')

@section('title', 'Rekomendasi PO')

@section('content_header')
    <h1 class="m-0 text-dark">Rekomendasi PO</h1>
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="under-construction-page">
                    <div class="content">
                        {{-- Ikon Tools yang besar --}}
                        <i class="fas fa-tools fa-5x text-warning mb-4"></i>

                        {{-- Judul Halaman --}}
                        <h2 class="font-weight-bold">Halaman Sedang Dalam Pengerjaan</h2>

                        {{-- Deskripsi --}}
                        <p class="lead text-muted">
                            Fitur Rekomendasi Purchase Order sedang kami siapkan untuk membantu Anda.
                            <br>
                            Silakan kembali lagi nanti untuk melihat pembaruan.
                        </p>

                        {{-- Tombol Kembali --}}
                        <a href="{{ route('admin.home') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-fw fa-arrow-left"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .under-construction-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 65vh; /* Menggunakan min-height agar konten tidak terpotong */
        background-color: #f4f6f9; /* Warna latar belakang yang senada */
        border-radius: .25rem;
        padding: 2rem;
    }
    .under-construction-page .content {
        max-width: 600px;
    }
</style>
@stop