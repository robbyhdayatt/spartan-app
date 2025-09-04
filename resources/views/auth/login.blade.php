@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Login - SPARTAN')

@section('auth_header')
    {{-- Kosong --}}
@stop

@section('auth_body')
    <div class="login-container">
        <div class="login-card">
            <div class="login-header text-center">
                <img src="{{ asset('img/SPARTAN.png') }}" alt="SPARTAN Logo" class="login-logo-img">
                <h2 class="login-title">SPARTAN</h2>
                <p class="login-subtitle">Sistem Informasi Part & Transaksi Yamaha LTI</p>
            </div>
            <div class="login-body">
                <form action="{{ route('login') }}" method="post">
                    @csrf

                    {{-- Username --}}
                    <div class="input-group mb-3">
                        <input type="text" name="username"
                               class="form-control custom-input @error('username') is-invalid @enderror"
                               value="{{ old('username') }}" placeholder="Username" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text custom-icon">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="input-group mb-3">
                        <input type="password" name="password"
                               class="form-control custom-input @error('password') is-invalid @enderror"
                               placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text custom-icon">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Tombol Sign In --}}
                    <div class="row">
                        <div class="col-12">
                            <button type="submit"
                                    class="btn btn-primary btn-block btn-custom">
                                <span class="fas fa-sign-in-alt"></span>
                                Sign In
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('auth_footer')
    {{-- Kosong --}}
@stop

@section('adminlte_css')
    <style>
        /* Reset semua background AdminLTE */
        html, body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: linear-gradient(135deg, #1e3c72, #2a5298) !important;
            font-family: 'Poppins', sans-serif;
        }

        /* Background modern untuk login page */
        body.login-page {
            background: linear-gradient(135deg, #1e3c72, #2a5298) !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hilangkan wrapper putih AdminLTE */
        .auth-page {
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Hilangkan card wrapper AdminLTE */
        .auth-page .card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        /* Hilangkan card-body wrapper AdminLTE */
        .auth-page .card-body {
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Container untuk login form */
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Card login custom */
        .login-card {
            background: #fff !important;
            border-radius: 20px;
            padding: 30px 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            animation: fadeInUp 0.8s ease;
        }

        .login-header {
            margin-bottom: 20px;
        }

        .login-logo-img {
            max-height: 120px;
            margin-bottom: 10px;
        }

        .login-title {
            font-weight: 700;
            color: #2a5298;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: #666;
        }

        .custom-input {
            border-radius: 12px 0 0 12px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }

        .custom-icon {
            border-radius: 0 12px 12px 0;
            background: #f0f0f0;
        }

        .btn-custom {
            border-radius: 12px;
            font-weight: 600;
            padding: 10px;
            background: #2a5298;
            border: none;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background: #1e3c72;
            transform: translateY(-2px);
        }

        /* Animasi */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hilangkan semua elemen AdminLTE yang tidak diperlukan */
        .login-logo {
            display: none !important;
        }

        .login-logo a {
            display: none !important;
        }

        /* Override untuk container AdminLTE */
        .container, .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
            width: 100% !important;
        }

        /* FORCE REMOVE semua elemen AdminLTE yang bisa menyebabkan background putih */
        .login-page *:not(.login-container):not(.login-card):not(.login-header):not(.login-body):not(.login-title):not(.login-subtitle):not(.login-logo-img):not(form):not(.input-group):not(input):not(.form-control):not(.btn):not(span):not(.fas):not(.invalid-feedback):not(strong):not(.row):not(.col-12):not(.input-group-append):not(.input-group-text) {
            background: transparent !important;
        }

        /* Pastikan tidak ada scroll horizontal */
        body, html {
            overflow-x: hidden !important;
        }

        /* Media query untuk mobile */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }

            .login-card {
                padding: 25px 20px;
                margin: 10px;
            }
        }

        /* Override AdminLTE wrapper */
        .wrapper {
            background: linear-gradient(135deg, #1e3c72, #2a5298) !important;
            min-height: 100vh !important;
        }

        /* NUCLEAR OPTION - Force semua div background transparent kecuali login-card */
        div:not(.login-card):not(.login-header):not(.login-body):not(.input-group):not(.input-group-append):not(.input-group-text):not(.row):not(.col-12) {
            background: transparent !important;
        }

        /* Reset semua margin dan padding yang mungkin ditambahkan AdminLTE */
        * {
            box-sizing: border-box;
        }

        /* Override khusus untuk elemen AdminLTE yang suka bikin background putih */
        .login-page .card-header,
        .login-page .card-footer,
        .card-header,
        .card-footer {
            display: none !important;
        }
    </style>
@endsection
