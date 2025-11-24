<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan: {{ $penjualan->nomor_faktur }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'sans-serif';
            font-size: 10pt; /* Ukuran font sedikit lebih besar */
            padding: 0.7cm; /* Padding dari tepi kertas */
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 4px; /* Sedikit padding tambahan */
            text-align: left;
            vertical-align: top;
        }
        .bordered th, .bordered td {
            border: 1px solid #000;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header h2, .header h3 {
            margin: 0;
        }
        .header, .info, .items {
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

    <div class="header text-center">
        <h2>FAKTUR PENJUALAN</h2>
        <h3>PT. Lautan Teduh Interniaga</h3>
    </div>

    <div class="info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>No. Faktur</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 33%;">{{ $penjualan->nomor_faktur }}</td>
                <td style="width: 15%;"><strong>Konsumen</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 33%;">{{ $penjualan->nama_konsumen }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>:</td>
                <td>{{ $penjualan->tanggal_jual->format('d/m/Y') }}</td>
                <td><strong>Sales</strong></td>
                <td>:</td>
                <td>{{ $penjualan->sales->nama ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="items">
        <table class="bordered">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No.</th>
                    <th style="width: 20%;">Kode Part</th>
                    <th>Nama Part</th>
                    <th class="text-center" style="width: 7%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Harga</th>
                    <th class="text-right" style="width: 18%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->part->kode_part }}</td>
                    <td>{{ $detail->part->nama_part }}</td>
                    <td class="text-center">{{ $detail->qty_jual }}</td>
                    <td class="text-right">@rupiah($detail->harga_jual)</td>
                    <td class="text-right">@rupiah($detail->subtotal)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals">
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 60%; border:0;"></td>
                    <td style="width: 20%; border:0;">Subtotal</td>
                    <td class="text-right" style="width: 20%; border:0;">@rupiah($penjualan->subtotal)</td>
                </tr>
                <tr>
                    <td style="border:0;"></td>
                    <td style="border:0;">Total Diskon</td>
                    <td class="text-right" style="border:0;">@rupiah($penjualan->total_diskon)</td>
                </tr>
                <tr>
                    <td style="border:0;"></td>
                    <td style="border:0;">PPN (11%)</td>
                    <td class="text-right" style="border:0;">@rupiah($penjualan->pajak)</td>
                </tr>
                <tr>
                    <td style="border:0;"></td>
                    <td style="border:0;"><strong>Total</strong></td>
                    <td class="text-right" style="border:0;"><strong>@rupiah($penjualan->total_harga)</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
