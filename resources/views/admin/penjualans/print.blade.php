<!DOCTYPE html>
<html>
<head>
    <title>Faktur Penjualan - {{ $penjualan->nomor_faktur }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font-size: 12px; }
        .invoice-header, .invoice-footer { text-align: center; margin-bottom: 20px; }
        .invoice-details { margin-bottom: 20px; }
        .table th, .table td { padding: 0.5rem; }
        .total-section { margin-top: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header mt-4">
            <h3>FAKTUR PENJUALAN</h3>
            <p>PT. LAUTAN TEDUH INTERNIAGA</p>
        </div>

        <div class="row invoice-details">
            <div class="col-6">
                <strong>Kepada Yth:</strong><br>
                {{ $penjualan->konsumen->nama_konsumen }}<br>
                {{ $penjualan->konsumen->alamat ?? 'Alamat tidak tersedia' }}
            </div>
            <div class="col-6 text-right">
                <strong>Nomor Faktur:</strong> {{ $penjualan->nomor_faktur }}<br>
                <strong>Tanggal:</strong> {{ $penjualan->tanggal_jual->format('d/m/Y') }}<br>
                <strong>Sales:</strong> {{ $penjualan->sales->nama ?? 'N/A' }}
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>No.</th>
                    <th>Kode Part</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->part->kode_part }}</td>
                    <td>{{ $detail->part->nama_part }}</td>
                    <td>{{ $detail->qty_jual }}</td>
                    <td class="text-right">{{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row">
            <div class="col-6">
                <p>Catatan:</p>
                <p><em>Barang yang sudah dibeli tidak dapat dikembalikan.</em></p>
            </div>
            <div class="col-6">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th class="text-right">Subtotal</th>
                            <td class="text-right">{{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        {{-- BARIS BARU UNTUK DISKON --}}
                        <tr>
                            <th class="text-right">Total Diskon</th>
                            <td class="text-right">{{ number_format($penjualan->total_diskon, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">PPN (11%)</th>
                            <td class="text-right">{{ number_format($penjualan->pajak, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="text-right"><strong>Total Akhir</strong></th>
                            <td class="text-right"><strong>{{ number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row invoice-footer" style="margin-top: 50px;">
            <div class="col-4 text-center">
                <p>Disiapkan Oleh,</p>
                <br><br><br>
                <p>(______________________)</p>
                <p>{{ $penjualan->sales->nama ?? 'Sales' }}</p>
            </div>
            <div class="col-4 text-center">
                <p>Disetujui Oleh,</p>
                <br><br><br>
                <p>(______________________)</p>
            </div>
            <div class="col-4 text-center">
                <p>Diterima Oleh,</p>
                <br><br><br>
                <p>(______________________)</p>
                <p>{{ $penjualan->konsumen->nama_konsumen }}</p>
            </div>
        </div>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">Cetak Faktur</button>
        </div>
    </div>
</body>
</html>
