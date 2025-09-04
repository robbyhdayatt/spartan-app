@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Login - SPARTAN')

@section('auth_header')
    {{-- Biarkan kosong --}}
@stop

@section('auth_body')
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1">
                <img src="{{ asset('img/logo_perusahaan.png') }}" alt="SPARTAN Logo"
                     style="max-height: 120px; width: auto; display: block; margin: 0 auto 10px auto;">
                <b>SPARTAN</b>
            </a>
            <p class="login-box-msg mt-3">Sistem Informasi Part & Transaksi Yamaha LTI</p>
        </div>
        <div class="card-body">
            <form action="{{ route('login') }}" method="post">
                @csrf

                {{-- Username field --}}
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}" placeholder="Username" autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Password field --}}
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Sign In button --}}
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                            <span class="fas fa-sign-in-alt"></span>
                            Sign In
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('auth_footer')
    {{-- Kosongkan footer --}}
@stop

@section('adminlte_css')
    <style>
        /* ========================================================== */
        /* PERBAIKAN FINAL ADA DI SINI */
        /* ========================================================== */
        .login-logo {
            display: none !important; /* Paksa untuk sembunyi */
        }
    </style>
@endsection
