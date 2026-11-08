<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>
        Laporan Barang Masuk
    </title>

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
            font-weight: bold;
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
            width: 50%;
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
            color: #111827;
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
            LAPORAN BARANG MASUK
        </h1>

        <p>
            Sistem Informasi Monitoring Stok dan Riwayat
            Penggunaan Perangkat Jaringan
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
                    Total Barang Masuk
                </div>

                <div class="summary-value">
                    {{ $totalJumlahMasuk }}
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
                    Tanggal
                </th>

                <th style="width: 10%;">
                    Kode Barang
                </th>

                <th style="width: 18%;">
                    Nama Barang
                </th>

                <th style="width: 12%;">
                    Kategori
                </th>

                <th style="width: 8%;">
                    Jumlah
                </th>

                <th style="width: 18%;">
                    Supplier
                </th>

                <th style="width: 20%;">
                    Keterangan
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($barangMasuks as $barangMasuk)

                <tr>

                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>


                    <td>
                        {{ $barangMasuk->tanggal }}
                    </td>


                    <td>
                        {{ $barangMasuk->barang?->kode_barang ?? '-' }}
                    </td>


                    <td>
                        {{ $barangMasuk->barang?->nama_barang ?? '-' }}
                    </td>


                    <td>
                        {{ $barangMasuk->barang?->kategori?->nama_kategori ?? '-' }}
                    </td>


                    <td class="text-center">

                        {{ $barangMasuk->jumlah }}

                        {{ $barangMasuk->barang?->satuan ?? '' }}

                    </td>


                    <td>
                        {{ $barangMasuk->supplier ?: '-' }}
                    </td>


                    <td>
                        {{ $barangMasuk->keterangan ?: '-' }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="8"
                        class="text-center"
                    >
                        Tidak ada data barang masuk.
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