<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Laporan Pemakaian Teknisi
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
            border-collapse: collapse;
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
            font-size: 8px;
            color: #6b7280;
        }

    </style>

</head>

<body>


    <div class="header">

        <h1>
            Laporan Pemakaian Teknisi
        </h1>

        <p>
            Sistem Informasi Monitoring Stok Barang
            dan Riwayat Penggunaan Perangkat Jaringan
        </p>

    </div>


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
                    Total Jumlah Pemakaian
                </div>

                <div class="summary-value">
                    {{ $totalJumlahPemakaian }}
                </div>

            </td>

        </tr>

    </table>


    <table class="data-table">

        <thead>

            <tr>

                <th style="width: 3%;">
                    No
                </th>

                <th style="width: 8%;">
                    Tanggal
                </th>

                <th style="width: 11%;">
                    Teknisi
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

                <th style="width: 7%;">
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

                <th style="width: 6%;">
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

                        {{ $barangKeluar->teknisi?->nama
                            ?? '-' }}

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


                    <td class="text-center">

                        {{ $barangKeluar->jumlah }}

                        {{ $barangKeluar->barang?->satuan ?? '' }}

                    </td>


                    <td>

                        {{ $barangKeluar->pengguna ?: '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->keperluan ?: '-' }}

                    </td>


                    <td>

                        {{ $barangKeluar->tujuan ?: '-' }}

                    </td>


                    <td class="text-center">

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
                        Tidak ada data pemakaian teknisi.
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