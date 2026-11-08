<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Barang Masuk
        </h2>
    </x-slot>


    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- HEADER --}}
            <div class="laporan-header">

                <div>

                    <h1>
                        Laporan Barang Masuk
                    </h1>

                    <p>
                        Riwayat penerimaan dan penambahan
                        stok perangkat jaringan.
                    </p>

                </div>


                {{-- EXPORT --}}
                <div class="export-actions">

                    <a
                        href="{!! route(
                            'laporan.masuk.excel',
                            [
                                'search' => request('search'),
                                'tanggal_mulai' => request('tanggal_mulai'),
                                'tanggal_selesai' => request('tanggal_selesai'),
                            ]
                        ) !!}"
                        class="btn-export btn-excel"
                    >
                        Export Excel
                    </a>


                    <a
                        href="{!! route(
                            'laporan.masuk.pdf',
                            [
                                'search' => request('search'),
                                'tanggal_mulai' => request('tanggal_mulai'),
                                'tanggal_selesai' => request('tanggal_selesai'),
                            ]
                        ) !!}"
                        class="btn-export btn-pdf"
                    >
                        Export PDF
                    </a>

                </div>

            </div>


            {{-- FILTER LAPORAN --}}
            <div class="filter-card">

                <div class="filter-header">

                    <h3>
                        Filter Laporan
                    </h3>

                    <p>
                        Cari transaksi dan filter berdasarkan
                        periode tanggal barang masuk.
                    </p>

                </div>


                <form
                    method="GET"
                    action="{{ route('laporan.masuk.index') }}"
                    class="filter-form"
                >

                    {{-- SEARCH --}}
                    <div class="filter-field">

                        <label for="search">
                            Pencarian
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Barang, merk, supplier..."
                        >

                    </div>


                    {{-- TANGGAL MULAI --}}
                    <div class="filter-field">

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
                    <div class="filter-field">

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


                    {{-- ACTION FILTER --}}
                    <div class="filter-actions">

                        <button
                            type="submit"
                            class="btn-filter"
                        >
                            Cari
                        </button>

                        <a
                            href="{{ route('laporan.masuk.index') }}"
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
                        Total Barang Masuk
                    </div>

                    <div class="summary-value">
                        {{ $totalJumlahMasuk }}
                    </div>

                </div>

            </div>


            {{-- TABEL --}}
            <div class="table-card">

                <div class="table-title">

                    <span>
                        Riwayat Barang Masuk
                    </span>

                    <span class="table-count">
                        {{ $barangMasuks->count() }} data
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
                                    Supplier
                                </th>

                                <th>
                                    Keterangan
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($barangMasuks as $barangMasuk)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>


                                    <td>
                                        {{ $barangMasuk->tanggal }}
                                    </td>


                                    <td>

                                        <span class="kode-barang">

                                            {{ $barangMasuk->barang?->kode_barang ?? '-' }}

                                        </span>

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $barangMasuk->barang?->nama_barang ?? '-' }}
                                        </strong>

                                    </td>


                                    <td>
                                        {{ $barangMasuk->barang?->kategori?->nama_kategori ?? '-' }}
                                    </td>


                                    <td>

                                        <strong>
                                            {{ $barangMasuk->jumlah }}
                                        </strong>

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
                                        class="empty-data"
                                    >
                                        Tidak ada data barang masuk
                                        yang sesuai dengan filter.
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
            align-items: center;
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
        }

        .btn-export {
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 17px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s ease;
        }

        .btn-excel {
            background: #16a34a;
            border: 1px solid #16a34a;
            color: #ffffff;
        }

        .btn-excel:hover {
            background: #15803d;
            border-color: #15803d;
        }

        .btn-pdf {
            background: #dc2626;
            border: 1px solid #dc2626;
            color: #ffffff;
        }

        .btn-pdf:hover {
            background: #b91c1c;
            border-color: #b91c1c;
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
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }

        .filter-header p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .filter-form {
            display: grid;
            grid-template-columns:
                minmax(220px, 2fr)
                minmax(160px, 1fr)
                minmax(160px, 1fr)
                auto;
            gap: 14px;
            align-items: end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .filter-field label {
            font-size: 12px;
            font-weight: 800;
            color: #374151;
        }

        .filter-field input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            background: #ffffff;
            color: #111827;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
        }

        .filter-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-filter,
        .btn-reset {
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-filter {
            background: #2563eb;
            border: 1px solid #2563eb;
            color: #ffffff;
            cursor: pointer;
        }

        .btn-filter:hover {
            background: #1d4ed8;
        }

        .btn-reset {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-reset:hover {
            background: #f9fafb;
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

        @media (max-width: 1000px) {

            .filter-form {
                grid-template-columns: 1fr 1fr;
            }

        }


        @media (max-width: 768px) {

            .laporan-header {
                display: block;
            }

            .export-actions {
                margin-top: 16px;
                width: 100%;
            }

            .btn-export {
                flex: 1;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                width: 100%;
            }

            .btn-filter,
            .btn-reset {
                flex: 1;
            }

        }

    </style>

</x-app-layout>