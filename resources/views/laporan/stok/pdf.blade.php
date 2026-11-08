<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Laporan Stok Barang</title>

    <style>

        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 10px;
        }

        .summary {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .summary td {
            width: 33.33%;
            border: 1px solid #d1d5db;
            padding: 10px;
        }

        .summary-label {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 16px;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 7px 6px;
            font-size: 9px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 7px 6px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .status-aman {
            color: #166534;
            font-weight: bold;
        }

        .status-menipis {
            color: #92400e;
            font-weight: bold;
        }

        .status-kritis {
            color: #991b1b;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            font-size: 8px;
            color: #6b7280;
            text-align: right;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>
            LAPORAN STOK BARANG
        </h1>

        <p>
            Sistem Informasi Monitoring Stok
            dan Riwayat Penggunaan Perangkat Jaringan
        </p>

    </div>


    <table class="summary">

        <tr>

            <td>

                <div class="summary-label">
                    Total Jenis Barang
                </div>

                <div class="summary-value">
                    {{ $totalJenisBarang }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Total Stok
                </div>

                <div class="summary-value">
                    {{ $totalStok }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Stok Menipis
                </div>

                <div class="summary-value">
                    {{ $stokMenipis }}
                </div>

            </td>

        </tr>

    </table>


    <table class="data-table">

        <thead>

            <tr>

                <th style="width: 4%;">
                    No
                </th>

                <th style="width: 10%;">
                    Kode
                </th>

                <th style="width: 19%;">
                    Nama Barang
                </th>

                <th style="width: 12%;">
                    Kategori
                </th>

                <th style="width: 12%;">
                    Merk
                </th>

                <th style="width: 14%;">
                    Lokasi
                </th>

                <th style="width: 8%;">
                    Stok
                </th>

                <th style="width: 9%;">
                    Minimum
                </th>

                <th style="width: 12%;">
                    Status
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($barangs as $barang)

                @php

                    if ($barang->stok == 0) {

                        $status = 'Kritis';
                        $statusClass = 'status-kritis';

                    } elseif ($barang->stok <= $barang->stok_minimum) {

                        $status = 'Menipis';
                        $statusClass = 'status-menipis';

                    } else {

                        $status = 'Aman';
                        $statusClass = 'status-aman';

                    }

                @endphp


                <tr>

                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $barang->kode_barang }}
                    </td>

                    <td>
                        {{ $barang->nama_barang }}
                    </td>

                    <td>
                        {{ $barang->kategori?->nama_kategori ?? '-' }}
                    </td>

                    <td>
                        {{ $barang->merk ?: '-' }}
                    </td>

                    <td>
                        {{ $barang->lokasi ?: '-' }}
                    </td>

                    <td class="text-center">
                        {{ $barang->stok }}
                        {{ $barang->satuan }}
                    </td>

                    <td class="text-center">
                        {{ $barang->stok_minimum }}
                    </td>

                    <td class="{{ $statusClass }}">
                        {{ $status }}
                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="9"
                        class="text-center"
                    >
                        Tidak ada data stok barang.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Dicetak:
        {{ now()->format('d-m-Y H:i') }}

    </div>

</body>

</html>