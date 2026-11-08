<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Laporan Barang Keluar
    </title>

    <style>

        @page {
            margin: 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .header {
            margin-bottom: 18px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }

        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 10px;
        }

        .summary {
            width: 100%;
            margin-bottom: 16px;
        }

        .summary td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 10px;
        }

        .summary-label {
            font-size: 9px;
            color: #6b7280;
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
            padding: 6px 5px;
            font-size: 8px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            font-size: 8px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .footer {
            margin-top: 12px;
            color: #6b7280;
            font-size: 8px;
        }

    </style>

</head>


<body>


    {{-- HEADER --}}

    <div class="header">

        <h1>
            Laporan Barang Keluar
        </h1>

        <p>
            Sistem Informasi Monitoring Stok Barang
            dan Riwayat Penggunaan Perangkat Jaringan
        </p>

    </div>


    {{-- RINGKASAN --}}

    <table class="summary">

        <tr>

            <td>

                <div class="summary-label">
                    Total Transaksi
                </div>

                <div class="summary-value">
                    {{ $totalTransaksi }}
                </div>

            </td>


            <td>

                <div class="summary-label">
                    Total Barang Keluar
                </div>

                <div class="summary-value">
                    {{ $totalJumlahKeluar }}
                </div>

            </td>

        </tr>

    </table>


    {{-- DATA --}}

    <table class="data-table">

        <thead>

            <tr>

                <th style="width: 3%;">
                    No
                </th>

                <th style="width: 8%;">
                    Tanggal
                </th>

                <th style="width: 9%;">
                    Kode
                </th>

                <th style="width: 13%;">
                    Barang
                </th>

                <th style="width: 9%;">
                    Kategori
                </th>

                <th style="width: 11%;">
                    Teknisi
                </th>

                <th style="width: 6%;">
                    Jumlah
                </th>

                <th style="width: 11%;">
                    Pengguna
                </th>

                <th style="width: 13%;">
                    Keperluan
                </th>

                <th style="width: 10%;">
                    Tujuan
                </th>

                <th style="width: 7%;">
                    Foto
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse($barangKeluars as $barangKeluar)

                <tr>

                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>


                    <td class="nowrap">

                        {{ $barangKeluar->tanggal
                            ? $barangKeluar->tanggal->format('d-m-Y')
                            : '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->barang?->kode_barang
                            ?? '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->barang?->nama_barang
                            ?? '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->barang?->kategori?->nama_kategori
                            ?? '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->teknisi?->nama
                            ?? '-' }}

                    </td>


                    <td class="text-center">

                        {{ $barangKeluar->jumlah }}

                        {{ $barangKeluar->barang?->satuan ?? '' }}

                    </td>


                    <td>

                        {{ $barangKeluar->pengguna
                            ?: '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->keperluan
                            ?: '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->tujuan
                            ?: '-' }}

                    </td>


                    <td>

                        @if($barangKeluar->foto)

                            Ada

                        @else

                            -

                        @endif

                    </td>

                </tr>


            @empty

                <tr>

                    <td
                        colspan="11"
                        class="text-center"
                    >
                        Tidak ada data barang keluar.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Dicetak pada:
        {{ now()->format('d-m-Y H:i') }}

    </div>


</body>

</html>