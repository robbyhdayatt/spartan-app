<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO: {{ $purchaseOrder->nomor_po }}</title>
    <style>
        /* Pengaturan Halaman & Font Global */
        @page {
            margin: 10px 20px; /* Margin tipis agar muat banyak */
        }
        body {
            font-family: Arial, Helvetica, sans-serif; /* Font Arial */
            font-size: 14px; /* Ukuran default diperbesar */
            color: #000;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .po-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
        }

        /* Informasi Supplier & PO */
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section td {
            vertical-align: top;
            padding: 2px;
        }
        .label-text {
            font-weight: bold;
            width: 120px;
        }

        /* Tabel Item (Minim Garis) */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            text-align: left;
            padding: 10px 5px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 16px; /* Header tabel lebih besar */
        }
        .items-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #ddd; /* Garis tipis hanya di bawah */
            font-size: 15px; /* Isi tabel besar agar jelas */
        }
        /* Hilangkan garis samping */
        .items-table th, .items-table td {
            border-left: none;
            border-right: none;
        }

        /* Total & Tanda Tangan */
        .footer-section {
            width: 100%;
            margin-top: 20px;
        }
        .total-table {
            width: 40%;
            float: right;
            font-size: 16px;
        }
        .total-table td {
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }
        .grand-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: none !important;
        }

        /* Helper untuk text alignment */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%">
                <div class="company-name">PT. Lautan Teduh Interniaga</div>
                <div>Jl. Ikan Tenggiri, Pesawahan, Bandar Lampung</div>
                <div>Telp: (0721) 123456</div>
            </td>
            <td style="width: 40%" class="po-title">
                PURCHASE ORDER
                <div style="font-size: 16px; font-weight: normal; margin-top: 5px;">
                    {{ $purchaseOrder->nomor_po }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Info Supplier & Tanggal --}}
    <table class="info-section">
        <tr>
            <td style="width: 55%">
                <strong>KEPADA (SUPPLIER):</strong><br>
                <span style="font-size: 18px; font-weight: bold;">{{ $purchaseOrder->supplier->nama_supplier }}</span><br>
                {{ $purchaseOrder->supplier->alamat ?? '-' }}<br>
                {{ $purchaseOrder->supplier->telepon ?? '-' }}
            </td>
            <td style="width: 45%">
                <table style="width: 100%">
                    <tr>
                        <td class="label-text">TANGGAL PO</td>
                        <td>: {{ $purchaseOrder->tanggal_po->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-text">GUDANG</td>
                        <td>: {{ $purchaseOrder->gudang->nama_gudang }}</td>
                    </tr>
                    <tr>
                        <td class="label-text">DIBUAT OLEH</td>
                        <td>: {{ $purchaseOrder->createdBy->name ?? 'Admin' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Tabel Item --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">KODE PART</th>
                <th style="width: 40%;">NAMA BARANG</th>
                <th style="width: 10%;" class="text-center">QTY</th>
                <th style="width: 15%;" class="text-right">HARGA</th>
                <th style="width: 15%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-bold">{{ $detail->part->kode_part }}</td>
                <td>{{ $detail->part->nama_part }}</td>
                <td class="text-center">{{ $detail->qty_pesan }}</td>
                <td class="text-right">{{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                <td class="text-right text-bold">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer: Catatan & Total --}}
    <div class="footer-section">
        {{-- Catatan di Kiri --}}
        <div style="width: 55%; float: left;">
            <strong>CATATAN:</strong>
            <div style="border: 1px dashed #999; padding: 10px; min-height: 60px; margin-top: 5px; background-color: #f9f9f9;">
                {{ $purchaseOrder->catatan ?? 'Tidak ada catatan khusus.' }}
            </div>
            
            <br><br>
            <table style="width: 100%; text-align: center; margin-top: 20px;">
                <tr>
                    <td style="width: 50%;">Dibuat Oleh,</td>
                    <td style="width: 50%;">Disetujui Oleh,</td>
                </tr>
                <tr>
                    <td style="height: 80px; vertical-align: bottom;">
                        ( {{ $purchaseOrder->createdBy->nama ?? '....................' }} )
                    </td>
                    <td style="height: 80px; vertical-align: bottom;">
                        ( {{ $purchaseOrder->approvedBy->nama ?? '....................' }} )
                    </td>
                </tr>
            </table>
        </div>

        {{-- Total di Kanan --}}
        <table class="total-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($purchaseOrder->pajak > 0)
            <tr>
                <td>PPN</td>
                <td class="text-right">{{ number_format($purchaseOrder->pajak, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="grand-total">GRAND TOTAL</td>
                <td class="grand-total text-right">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div style="clear: both;"></div>
    </div>

</body>
</html>