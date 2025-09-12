{{-- resources/views/dashboards/_default.blade.php --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang, {{ Auth::user()->nama }}!</h3>
            </div>
            <div class="card-body">
                <p>Anda telah berhasil login ke sistem SPARTAN.</p>
            </div>
        </div>
    </div>
</div>
