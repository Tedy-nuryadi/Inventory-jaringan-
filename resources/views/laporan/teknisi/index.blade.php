<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Pemakaian Teknisi
        </h2>
    </x-slot>


    @php

        /*
        |--------------------------------------------------------------------------
        | FILTER UNTUK EXPORT
        |--------------------------------------------------------------------------
        |
        | Filter disusun secara eksplisit agar urutan query string konsisten:
        |
        | tanggal_mulai
        | tanggal_selesai
        | teknisi_id
        |
        */

        $exportFilters = array_filter([
            'tanggal_mulai'   => request('tanggal_mulai'),
            'tanggal_selesai' => request('tanggal_selesai'),
            'teknisi_id'      => request('teknisi_id'),
        ], function ($value) {
            return $value !== null && $value !== '';
        });


        /*
        |--------------------------------------------------------------------------
        | URL EXPORT
        |--------------------------------------------------------------------------
        */

        $excelUrl = route(
            'laporan.teknisi.excel',
            $exportFilters
        );

        $pdfUrl = route(
            'laporan.teknisi.pdf',
            $exportFilters
        );

    @endphp


    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- HEADER --}}
            <div class="laporan-header">

                <div>

                    <h1>
                        Laporan Pemakaian Teknisi
                    </h1>

                    <p>
                        Monitoring penggunaan perangkat jaringan
                        berdasarkan transaksi barang keluar oleh teknisi.
                    </p>

                </div>


                {{-- EXPORT --}}
                <div class="export-actions">

                    <a
                        href="{!! $excelUrl !!}"
                        class="btn-excel"
                    >
                        Export Excel
                    </a>


                    <a
                        href="{!! $pdfUrl !!}"
                        class="btn-pdf"
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
                        Filter berdasarkan periode tanggal
                        dan teknisi.
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('laporan.teknisi.index') }}"
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


                    {{-- ACTION FILTER --}}
                    <div class="filter-actions">

                        <button
                            type="submit"
                            class="btn-filter"
                        >
                            Filter
                        </button>


                        <a
                            href="{{ route('laporan.teknisi.index') }}"
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
                        Total Jumlah Pemakaian
                    </div>

                    <div class="summary-value">
                        {{ $totalJumlahPemakaian }}
                    </div>

                </div>


            </div>


            {{-- TABEL --}}
            <div class="table-card">


                <div class="table-title">

                    <span>
                        Riwayat Pemakaian Teknisi
                    </span>

                    <span class="table-count">
                        {{ $barangKeluars->count() }} data
                    </span>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    No
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th>
                                    Teknisi
                                </th>

                                <th>
                                    Kode Barang
                                </th>

                                <th>
                                    Nama Barang
                                </th>

                                <th>
                                    Kategori
                                </th>

                                <th>
                                    Jumlah
                                </th>

                                <th>
                                    Pengguna
                                </th>

                                <th>
                                    Keperluan
                                </th>

                                <th>
                                    Tujuan
                                </th>

                                <th>
                                    Foto
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($barangKeluars as $barangKeluar)

                                <tr>


                                    {{-- NO --}}
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    {{-- TANGGAL --}}
                                    <td>

                                        {{ $barangKeluar->tanggal
                                            ? $barangKeluar->tanggal->format('d-m-Y')
                                            : '-' }}

                                    </td>


                                    {{-- TEKNISI --}}
                                    <td>

                                        <strong class="teknisi-name">

                                            {{ $barangKeluar->teknisi?->nama
                                                ?? '-' }}

                                        </strong>

                                    </td>


                                    {{-- KODE BARANG --}}
                                    <td>

                                        <span class="kode-barang">

                                            {{ $barangKeluar->barang?->kode_barang
                                                ?? '-' }}

                                        </span>

                                    </td>


                                    {{-- NAMA BARANG --}}
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
                                    <td>

                                        <strong>
                                            {{ $barangKeluar->jumlah }}
                                        </strong>

                                        {{ $barangKeluar->barang?->satuan ?? '' }}

                                    </td>


                                    {{-- PENGGUNA --}}
                                    <td>

                                        {{ $barangKeluar->pengguna
                                            ?: '-' }}

                                    </td>


                                    {{-- KEPERLUAN --}}
                                    <td>

                                        {{ $barangKeluar->keperluan
                                            ?: '-' }}

                                    </td>


                                    {{-- TUJUAN --}}
                                    <td>

                                        {{ $barangKeluar->tujuan
                                            ?: '-' }}

                                    </td>


                                    {{-- FOTO --}}
                                    <td>

                                        @if($barangKeluar->foto)

                                            <span class="foto-ada">
                                                Ada Foto
                                            </span>

                                        @else

                                            <span class="foto-tidak">
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
                                        Belum ada data pemakaian teknisi.
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

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .laporan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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
            margin-bottom: 0;
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

        .btn-excel,
        .btn-pdf {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 17px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.2s ease;
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
            margin-bottom: 0;
            color: #6b7280;
            font-size: 13px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
            background: #ffffff;
            color: #111827;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER ACTION
        |--------------------------------------------------------------------------
        */

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
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
            font-size: 16px;
            font-weight: 800;
        }

        .table-count {
            padding: 5px 9px;
            border-radius: 999px;
            background: #f3f4f6;
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
            color: #374151;
            font-size: 14px;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .teknisi-name {
            color: #111827;
        }

        .kode-barang {
            color: #2563eb;
            font-weight: 700;
        }

        .foto-ada {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-size: 11px;
            font-weight: 700;
        }

        .foto-tidak {
            color: #9ca3af;
        }

        .empty-data {
            padding: 40px;
            text-align: center;
            color: #9ca3af;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 900px) {

            .laporan-header {
                flex-direction: column;
            }

            .export-actions {
                width: 100%;
            }

            .btn-excel,
            .btn-pdf {
                flex: 1;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 768px) {

            .summary-grid {
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


        @media (max-width: 500px) {

            .export-actions {
                flex-direction: column;
            }

            .btn-excel,
            .btn-pdf {
                width: 100%;
            }

        }

    </style>

</x-app-layout>