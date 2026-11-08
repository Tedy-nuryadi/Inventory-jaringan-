<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Riwayat Pemakaian Saya
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


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header {
            margin-bottom: 16px;
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


        /*
        |--------------------------------------------------------------------------
        | IDENTITAS TEKNISI
        |--------------------------------------------------------------------------
        */

        .teknisi-box {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .teknisi-label {
            color: #6b7280;
            font-size: 8px;
            text-transform: uppercase;
        }

        .teknisi-name {
            margin-top: 3px;
            font-size: 13px;
            font-weight: bold;
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        .periode {
            margin-bottom: 15px;
            font-size: 9px;
            color: #374151;
        }


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | DATA TABLE
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            margin-top: 12px;
            font-size: 8px;
            color: #6b7280;
        }

    </style>

</head>

<body>


    {{-- HEADER --}}

    <div class="header">

        <h1>
            Riwayat Pemakaian Saya
        </h1>

        <p>
            Sistem Informasi Monitoring Stok Barang
            dan Riwayat Penggunaan Perangkat Jaringan
        </p>

    </div>


    {{-- IDENTITAS TEKNISI --}}

    <div class="teknisi-box">

        <div class="teknisi-label">
            Teknisi
        </div>

        <div class="teknisi-name">
            {{ $teknisi->nama }}
        </div>

    </div>


    {{-- PERIODE FILTER --}}

    @if(request('tanggal_mulai') || request('tanggal_selesai'))

        <div class="periode">

            <strong>
                Periode:
            </strong>

            {{ request('tanggal_mulai') ?: 'Awal' }}

            s/d

            {{ request('tanggal_selesai') ?: 'Akhir' }}

        </div>

    @endif


    {{-- SUMMARY --}}

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


    {{-- DATA --}}

    <table class="data-table">

        <thead>

            <tr>

                <th style="width: 4%;">
                    No
                </th>

                <th style="width: 9%;">
                    Tanggal
                </th>

                <th style="width: 11%;">
                    Kode
                </th>

                <th style="width: 15%;">
                    Barang
                </th>

                <th style="width: 10%;">
                    Kategori
                </th>

                <th style="width: 8%;">
                    Jumlah
                </th>

                <th style="width: 12%;">
                    Pengguna
                </th>

                <th style="width: 14%;">
                    Keperluan
                </th>

                <th style="width: 11%;">
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


                    {{-- NO --}}

                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>


                    {{-- TANGGAL --}}

                    <td class="nowrap">

                        {{ $barangKeluar->tanggal
                            ? $barangKeluar->tanggal->format('d-m-Y')
                            : '-' }}

                    </td>


                    {{-- KODE --}}

                    <td>

                        {{ $barangKeluar->barang?->kode_barang
                            ?? '-' }}

                    </td>


                    {{-- BARANG --}}

                    <td>

                        {{ $barangKeluar->barang?->nama_barang
                            ?? '-' }}

                    </td>


                    {{-- KATEGORI --}}

                    <td>

                        {{ $barangKeluar->barang?->kategori?->nama_kategori
                            ?? '-' }}

                    </td>


                    {{-- JUMLAH --}}

                    <td class="text-center">

                        {{ $barangKeluar->jumlah }}

                        {{ $barangKeluar->barang?->satuan ?? '' }}

                    </td>


                    {{-- PENGGUNA --}}

                    <td>

                        {{ $barangKeluar->pengguna ?: '-' }}

                    </td>


                    {{-- KEPERLUAN --}}

                    <td>

                        {{ $barangKeluar->keperluan ?: '-' }}

                    </td>


                    {{-- TUJUAN --}}

                    <td>

                        {{ $barangKeluar->tujuan ?: '-' }}

                    </td>


                    {{-- FOTO --}}

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
                        colspan="10"
                        class="text-center"
                    >
                        Tidak ada riwayat pemakaian.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- FOOTER --}}

    <div class="footer">

        Dicetak pada:
        {{ now()->format('d-m-Y H:i') }}

    </div>


</body>

</html>