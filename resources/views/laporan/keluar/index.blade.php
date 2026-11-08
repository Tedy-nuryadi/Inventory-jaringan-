<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Barang Keluar
        </h2>
    </x-slot>


    @php
        $exportFilters = array_filter(
            [
                'tanggal_mulai' => request('tanggal_mulai'),
                'tanggal_selesai' => request('tanggal_selesai'),
                'barang_id' => request('barang_id'),
                'kategori_id' => request('kategori_id'),
                'teknisi_id' => request('teknisi_id'),
            ],
            function ($value) {
                return $value !== null && $value !== '';
            }
        );

        $exportQuery = http_build_query($exportFilters);

        $excelUrl = route('laporan.keluar.excel')
            . ($exportQuery ? '?' . $exportQuery : '');

        $pdfUrl = route('laporan.keluar.pdf')
            . ($exportQuery ? '?' . $exportQuery : '');
    @endphp


    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- HEADER --}}
            <div class="laporan-header">

                <div>

                    <h1>
                        Laporan Barang Keluar
                    </h1>

                    <p>
                        Riwayat pengeluaran dan penggunaan
                        perangkat jaringan oleh teknisi.
                    </p>

                </div>


                {{-- EXPORT --}}
                <div class="export-actions">

                    <a
                        href="{!! $excelUrl !!}"
                        class="btn-export btn-excel"
                    >
                        Export Excel
                    </a>

                    <a
                        href="{!! $pdfUrl !!}"
                        class="btn-export btn-pdf"
                    >
                        Export PDF
                    </a>

                </div>

            </div>


            {{-- FILTER --}}
            <div class="filter-card">

                <div class="filter-header">

                    <h3>
                        Filter Laporan
                    </h3>

                    <p>
                        Pilih periode, barang, kategori, atau teknisi.
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('laporan.keluar.index') }}"
                >

                    <div class="filter-grid">


                        {{-- TANGGAL MULAI --}}
                        <div class="form-group">

                            <label for="tanggal_mulai">
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                id="tanggal_mulai"
                                name="tanggal_mulai"
                                value="{{ request('tanggal_mulai') }}"
                            >

                        </div>


                        {{-- TANGGAL SELESAI --}}
                        <div class="form-group">

                            <label for="tanggal_selesai">
                                Tanggal Selesai
                            </label>

                            <input
                                type="date"
                                id="tanggal_selesai"
                                name="tanggal_selesai"
                                value="{{ request('tanggal_selesai') }}"
                            >

                        </div>


                        {{-- BARANG --}}
                        <div class="form-group">

                            <label for="barang_id">
                                Barang
                            </label>

                            <select
                                id="barang_id"
                                name="barang_id"
                            >

                                <option value="">
                                    Semua Barang
                                </option>

                                @foreach($barangs as $barang)

                                    <option value="{{ $barang->id }}" @selected((string) request('barang_id') === (string) $barang->id)>
                                        {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- KATEGORI --}}
                        <div class="form-group">

                            <label for="kategori_id">
                                Kategori
                            </label>

                            <select
                                id="kategori_id"
                                name="kategori_id"
                            >

                                <option value="">
                                    Semua Kategori
                                </option>

                                @foreach($kategoris as $kategori)

                                    <option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>
                                        {{ $kategori->nama_kategori }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- TEKNISI --}}
                        <div class="form-group">

                            <label for="teknisi_id">
                                Teknisi
                            </label>

                            <select
                                id="teknisi_id"
                                name="teknisi_id"
                            >

                                <option value="">
                                    Semua Teknisi
                                </option>

                                @foreach($teknisis as $teknisi)

                                    <option value="{{ $teknisi->id }}" @selected((string) request('teknisi_id') === (string) $teknisi->id)>
                                        {{ $teknisi->nama }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>


                    <div class="filter-actions">

                        <button
                            type="submit"
                            class="btn-filter"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('laporan.keluar.index') }}"
                            class="btn-reset"
                        >
                            Reset Filter
                        </a>

                    </div>

                </form>

            </div>


            {{-- RINGKASAN --}}
            <div class="summary-grid">

                <div class="summary-card">

                    <div class="summary-label">
                        Total Transaksi
                    </div>

                    <div class="summary-value">
                        {{ $totalTransaksi }}
                    </div>

                </div>


                <div class="summary-card">

                    <div class="summary-label">
                        Total Barang Keluar
                    </div>

                    <div class="summary-value">
                        {{ $totalJumlahKeluar }}
                    </div>

                </div>

            </div>


            {{-- TABEL --}}
            <div class="table-card">

                <div class="table-title">

                    <span>
                        Riwayat Barang Keluar
                    </span>

                    <span class="table-count">
                        {{ $barangKeluars->count() }} data
                    </span>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Teknisi</th>
                                <th>Jumlah</th>
                                <th>Pengguna</th>
                                <th>Keperluan</th>
                                <th>Tujuan</th>
                                <th>Foto</th>
                            </tr>

                        </thead>


                        <tbody>

                            @forelse($barangKeluars as $barangKeluar)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    <td>
                                        {{ $barangKeluar->tanggal
                                            ? $barangKeluar->tanggal->format('d-m-Y')
                                            : '-' }}
                                    </td>


                                    <td>

                                        <span class="kode-barang">
                                            {{ $barangKeluar->barang?->kode_barang ?? '-' }}
                                        </span>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $barangKeluar->barang?->nama_barang ?? '-' }}
                                        </strong>

                                    </td>


                                    <td>
                                        {{ $barangKeluar->barang?->kategori?->nama_kategori ?? '-' }}
                                    </td>


                                    <td>
                                        {{ $barangKeluar->teknisi?->nama ?? '-' }}
                                    </td>


                                    <td>

                                        <strong>
                                            {{ $barangKeluar->jumlah }}
                                        </strong>

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


                                    <td>

                                        @if($barangKeluar->foto)

                                            <span class="foto-ada">
                                                Ada Foto
                                            </span>

                                        @else

                                            <span class="foto-tidak-ada">
                                                -
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="11"
                                        class="empty-data"
                                    >
                                        Tidak ada data barang keluar.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <style>

        .laporan-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .laporan-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #111827;
        }

        .laporan-header p {
            margin-top: 6px;
            color: #6b7280;
            font-size: 14px;
        }


        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        .export-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-excel {
            background: #16a34a;
            color: #ffffff;
        }

        .btn-excel:hover {
            background: #15803d;
        }

        .btn-pdf {
            background: #dc2626;
            color: #ffffff;
        }

        .btn-pdf:hover {
            background: #b91c1c;
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        .filter-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .filter-header {
            margin-bottom: 18px;
        }

        .filter-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #111827;
        }

        .filter-header p {
            margin-top: 4px;
            font-size: 13px;
            color: #6b7280;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            padding: 0 11px;
            font-size: 14px;
            color: #111827;
            background: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .btn-filter {
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-filter:hover {
            background: #1d4ed8;
        }

        .btn-reset {
            border-radius: 9px;
            padding: 10px 18px;
            background: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-reset:hover {
            background: #e5e7eb;
        }


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .summary-label {
            font-size: 13px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .summary-value {
            margin-top: 8px;
            font-size: 30px;
            font-weight: 800;
            color: #111827;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLE
        |--------------------------------------------------------------------------
        */

        .table-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .table-title {
            padding: 18px 20px;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-count {
            padding: 5px 9px;
            background: #f3f4f6;
            border-radius: 999px;
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
        }

        th {
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 800;
            color: #6b7280;
            text-transform: uppercase;
            white-space: nowrap;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 15px 16px;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .kode-barang {
            font-weight: 700;
            color: #2563eb;
        }

        .foto-ada {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
        }

        .foto-tidak-ada {
            color: #9ca3af;
        }

        .empty-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {

            .filter-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 768px) {

            .laporan-header {
                flex-direction: column;
            }

            .export-actions {
                width: 100%;
            }

            .btn-export {
                flex: 1;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-filter,
            .btn-reset {
                text-align: center;
            }

        }

    </style>

</x-app-layout>