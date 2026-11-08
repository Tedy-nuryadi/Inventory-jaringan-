<x-app-layout>

<style>

.barang-page {
    background:#f8fafc;
    min-height:calc(100vh - 64px);
    padding:28px 20px 50px;
}

.barang-container {
    max-width:1400px;
    margin:auto;
}

.page-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:22px;
}

.page-title {
    margin:0;
    font-size:27px;
    line-height:1.2;
    font-weight:800;
    color:#0f172a;
}

.page-subtitle {
    margin-top:6px;
    margin-bottom:0;
    font-size:14px;
    color:#64748b;
}

.add-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    background:#2563eb;
    color:#fff;
    padding:11px 18px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    font-weight:700;
    box-shadow:0 3px 7px rgba(37,99,235,.20);
    transition:.15s;
    white-space:nowrap;
}

.add-button:hover {
    background:#1d4ed8;
    transform:translateY(-1px);
}

.summary-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-bottom:18px;
}

.summary-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:13px;
    padding:16px 18px;
    box-shadow:0 2px 7px rgba(15,23,42,.035);
}

.summary-label {
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#64748b;
}

.summary-value {
    font-size:24px;
    font-weight:800;
    color:#0f172a;
    margin-top:3px;
}

.main-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    box-shadow:0 3px 12px rgba(15,23,42,.045);
    overflow:hidden;
}

.toolbar {
    padding:18px 20px;
    border-bottom:1px solid #e2e8f0;
}

.filter-grid {
    display:grid;
    grid-template-columns:minmax(0,1fr) 200px auto auto;
    gap:10px;
    align-items:center;
}

.search-wrap {
    position:relative;
}

.search-icon {
    position:absolute;
    left:13px;
    top:50%;
    transform:translateY(-50%);
    width:18px;
    height:18px;
    color:#94a3b8;
    pointer-events:none;
}

.search-input {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:9px;
    padding:11px 14px 11px 40px;
    color:#0f172a;
    background:#fff;
    outline:none;
    font-size:13px;
}

.search-input:focus,
.sort-select:focus {
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

.sort-select {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:9px;
    padding:11px 12px;
    background:#fff;
    color:#334155;
    font-size:13px;
    outline:none;
}

.search-button {
    border:none;
    background:#2563eb;
    color:#fff;
    border-radius:9px;
    padding:11px 18px;
    cursor:pointer;
    font-size:13px;
    font-weight:700;
}

.reset-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #cbd5e1;
    color:#475569;
    background:#fff;
    border-radius:9px;
    padding:10px 15px;
    text-decoration:none;
    font-size:13px;
    font-weight:700;
}

.search-info {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-top:12px;
    color:#64748b;
    font-size:12px;
}

.table-wrap {
    width:100%;
    overflow-x:auto;
}

.inventory-table {
    width:100%;
    border-collapse:collapse;
    min-width:1000px;
}

.inventory-table thead {
    background:#f8fafc;
}

.inventory-table th {
    padding:13px 18px;
    text-align:left;
    color:#64748b;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.055em;
    border-bottom:1px solid #e2e8f0;
}

.inventory-table th.center {
    text-align:center;
}

.inventory-table td {
    padding:15px 18px;
    border-bottom:1px solid #f1f5f9;
    color:#334155;
    font-size:13px;
    vertical-align:middle;
}

.inventory-table tbody tr {
    transition:.12s;
}

.inventory-table tbody tr:hover {
    background:#f8fafc;
}

.kode {
    font-family:ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size:12px;
    font-weight:700;
    color:#475569;
}

.nama-barang {
    font-size:13px;
    font-weight:750;
    color:#0f172a;
}

.sub-info {
    color:#94a3b8;
    font-size:11px;
    margin-top:3px;
}

.category-badge {
    display:inline-flex;
    align-items:center;
    background:#eef2ff;
    color:#4f46e5;
    border:1px solid #e0e7ff;
    padding:5px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
}

.stock-normal {
    color:#0f172a;
    font-weight:800;
}

.stock-warning {
    display:inline-block;
    background:#ffedd5;
    color:#c2410c;
    padding:5px 9px;
    border-radius:7px;
    font-weight:800;
}

.stock-critical {
    display:inline-block;
    background:#ffe4e6;
    color:#be123c;
    padding:5px 9px;
    border-radius:7px;
    font-weight:800;
}

.action-wrap {
    display:flex;
    justify-content:center;
    gap:7px;
}

.edit-btn,
.delete-btn {
    border:none;
    border-radius:7px;
    padding:7px 12px;
    color:white;
    font-size:11px;
    font-weight:750;
    cursor:pointer;
    text-decoration:none;
}

.edit-btn {
    background:#f59e0b;
}

.edit-btn:hover {
    background:#d97706;
}

.delete-btn {
    background:#dc2626;
}

.delete-btn:hover {
    background:#b91c1c;
}

.empty-state {
    padding:55px 20px !important;
    text-align:center;
    color:#94a3b8 !important;
}

.success-message {
    background:#dcfce7;
    border:1px solid #86efac;
    color:#166534;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:17px;
    font-size:13px;
}

@media(max-width:900px) {

    .summary-grid {
        grid-template-columns:1fr 1fr;
    }

    .filter-grid {
        grid-template-columns:1fr 1fr;
    }

}

@media(max-width:600px) {

    .page-header {
        align-items:flex-start;
        flex-direction:column;
    }

    .summary-grid {
        grid-template-columns:1fr;
    }

    .filter-grid {
        grid-template-columns:1fr;
    }

    .add-button {
        width:100%;
    }

}

</style>


@php

    $totalJenisBarang = $barangs->count();

    $totalStokBarang = $barangs->sum('stok');

    $stokRendah = $barangs->filter(function($item) {
        return $item->stok <= 10;
    })->count();

@endphp


<div class="barang-page">

<div class="barang-container">


    {{-- HEADER --}}
    <div class="page-header">

        <div>

            <h1 class="page-title">
                Data Barang & Perangkat
            </h1>

            <p class="page-subtitle">
                Kelola seluruh perangkat dan stok inventaris jaringan.
            </p>

        </div>


        <a
            href="{{ route('barang.create') }}"
            class="add-button"
        >

            <span style="font-size:18px;line-height:1;">
                +
            </span>

            Tambah Barang

        </a>

    </div>



    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="success-message">

            {{ session('success') }}

        </div>

    @endif



    {{-- SUMMARY --}}
    <div class="summary-grid">


        <div class="summary-card">

            <div class="summary-label">
                Data Ditampilkan
            </div>

            <div class="summary-value">
                {{ number_format($totalJenisBarang) }}
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-label">
                Total Stok Ditampilkan
            </div>

            <div class="summary-value">
                {{ number_format($totalStokBarang) }}
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-label">
                Stok ≤ 10
            </div>

            <div
                class="summary-value"
                style="color:{{ $stokRendah > 0 ? '#e11d48' : '#059669' }};"
            >
                {{ $stokRendah }}
            </div>

        </div>


    </div>



    {{-- MAIN CARD --}}
    <div class="main-card">


        {{-- SEARCH --}}
        <div class="toolbar">

            <form
                action="{{ route('barang.index') }}"
                method="GET"
            >

                <div class="filter-grid">


                    {{-- SEARCH INPUT --}}
                    <div class="search-wrap">

                        <svg
                            class="search-icon"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0z"
                            />

                        </svg>


                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama, kode, merk, lokasi atau kategori..."
                            class="search-input"
                        >

                    </div>



                    {{-- SORT --}}
                    <select
                        name="sort"
                        class="sort-select"
                    >

                        <option value="terbaru"
                            {{ request('sort', 'terbaru') === 'terbaru' ? 'selected' : '' }}>
                            Terbaru
                        </option>

                        <option value="terlama"
                            {{ request('sort') === 'terlama' ? 'selected' : '' }}>
                            Terlama
                        </option>

                        <option value="nama_az"
                            {{ request('sort') === 'nama_az' ? 'selected' : '' }}>
                            Nama A → Z
                        </option>

                        <option value="nama_za"
                            {{ request('sort') === 'nama_za' ? 'selected' : '' }}>
                            Nama Z → A
                        </option>

                        <option value="stok_terbanyak"
                            {{ request('sort') === 'stok_terbanyak' ? 'selected' : '' }}>
                            Stok Terbanyak
                        </option>

                        <option value="stok_tersedikit"
                            {{ request('sort') === 'stok_tersedikit' ? 'selected' : '' }}>
                            Stok Tersedikit
                        </option>

                    </select>



                    <button
                        type="submit"
                        class="search-button"
                    >
                        Cari
                    </button>



                    <a
                        href="{{ route('barang.index') }}"
                        class="reset-button"
                    >
                        Reset
                    </a>


                </div>



                @if(request('search'))

                    <div class="search-info">

                        <span>

                            Hasil untuk

                            <strong style="color:#0f172a;">
                                "{{ request('search') }}"
                            </strong>

                        </span>


                        <strong style="color:#2563eb;">

                            {{ $barangs->count() }} data ditemukan

                        </strong>

                    </div>

                @endif

            </form>

        </div>



        {{-- TABLE --}}
        <div class="table-wrap">

            <table class="inventory-table">


                <thead>

                    <tr>

                        <th>
                            Kode
                        </th>

                        <th>
                            Perangkat
                        </th>

                        <th>
                            Kategori
                        </th>

                        <th>
                            Merk
                        </th>

                        <th>
                            Lokasi
                        </th>

                        <th class="center">
                            Stok
                        </th>

                        <th class="center">
                            Aksi
                        </th>

                    </tr>

                </thead>



                <tbody>


                    @forelse($barangs as $barang)


                        <tr>


                            {{-- KODE --}}
                            <td>

                                <span class="kode">

                                    {{ $barang->kode_barang }}

                                </span>

                            </td>



                            {{-- BARANG --}}
                            <td>

                                <div class="nama-barang">

                                    {{ $barang->nama_barang }}

                                </div>


                                <div class="sub-info">

                                    Satuan:
                                    {{ strtoupper($barang->satuan ?? '-') }}

                                </div>

                            </td>



                            {{-- KATEGORI --}}
                            <td>

                                <span class="category-badge">

                                    {{ $barang->kategori->nama_kategori ?? 'Tanpa Kategori' }}

                                </span>

                            </td>



                            {{-- MERK --}}
                            <td>

                                {{ $barang->merk ?? '-' }}

                            </td>



                            {{-- LOKASI --}}
                            <td>

                                {{ $barang->lokasi ?? '-' }}

                            </td>



                            {{-- STOK --}}
                            <td style="text-align:center;">


                                @if($barang->stok <= 3)

                                    <span class="stock-critical">

                                        {{ number_format($barang->stok) }}
                                        {{ $barang->satuan }}

                                    </span>


                                @elseif($barang->stok <= 10)

                                    <span class="stock-warning">

                                        {{ number_format($barang->stok) }}
                                        {{ $barang->satuan }}

                                    </span>


                                @else

                                    <span class="stock-normal">

                                        {{ number_format($barang->stok) }}
                                        {{ $barang->satuan }}

                                    </span>

                                @endif


                            </td>



                            {{-- AKSI --}}
                            <td>

                                <div class="action-wrap">


                                    <a
                                        href="{{ route('barang.edit', $barang->id) }}"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        action="{{ route('barang.destroy', $barang->id) }}"
                                        method="POST"
                                        style="margin:0;"
                                    >

                                        @csrf
                                        @method('DELETE')


                                        <button
                                            type="submit"
                                            class="delete-btn"
                                            onclick="return confirm('Yakin ingin menghapus {{ $barang->nama_barang }}?')"
                                        >
                                            Hapus
                                        </button>


                                    </form>


                                </div>

                            </td>


                        </tr>


                    @empty


                        <tr>

                            <td
                                colspan="7"
                                class="empty-state"
                            >

                                @if(request('search'))

                                    Tidak ditemukan barang dengan kata kunci

                                    <strong>
                                        "{{ request('search') }}"
                                    </strong>.

                                @else

                                    Belum ada data barang.

                                @endif

                            </td>

                        </tr>


                    @endforelse


                </tbody>


            </table>

        </div>


    </div>


</div>

</div>

</x-app-layout>