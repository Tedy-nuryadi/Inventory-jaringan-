<x-app-layout>

<style>

.dashboard-wrap {
    background: #f8fafc;
    min-height: calc(100vh - 64px);
    padding: 28px 20px 50px;
}

.dashboard-container {
    max-width: 1400px;
    margin: auto;
}

.dashboard-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.dashboard-title h1 {
    font-size: 28px;
    line-height: 1.2;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.dashboard-title p {
    margin-top: 6px;
    color: #64748b;
    font-size: 14px;
}

.date-box {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 16px;
    color: #475569;
    font-size: 13px;
    box-shadow: 0 1px 3px rgba(15,23,42,.05);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.stat-card {
    position: relative;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15,23,42,.04);
}

.stat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    color: #64748b;
    text-transform: uppercase;
}

.stat-number {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 7px;
}

.stat-desc {
    color: #94a3b8;
    font-size: 12px;
    margin-top: 3px;
}

.stat-icon {
    position: absolute;
    right: 17px;
    top: 20px;
    width: 43px;
    height: 43px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon svg {
    width: 22px;
    height: 22px;
}

.blue-icon {
    background: #eef2ff;
    color: #4f46e5;
}

.green-icon {
    background: #ecfdf5;
    color: #059669;
}

.cyan-icon {
    background: #eff6ff;
    color: #2563eb;
}

.orange-icon {
    background: #fff7ed;
    color: #ea580c;
}

.red-icon {
    background: #fff1f2;
    color: #e11d48;
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(290px, .8fr);
    gap: 20px;
    margin-bottom: 20px;
}

.panel {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(15,23,42,.04);
    overflow: hidden;
}

.panel-header {
    padding: 19px 21px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-title {
    font-size: 16px;
    font-weight: 750;
    color: #0f172a;
}

.panel-subtitle {
    color: #94a3b8;
    font-size: 12px;
    margin-top: 3px;
}

.legend {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 12px;
    color: #64748b;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 99px;
}

.legend-in {
    background: #4f46e5;
}

.legend-out {
    background: #f97316;
}


/* CHART */

.chart-container {
    height: 280px;
    padding: 25px 25px 15px;
}

.chart-area {
    height: 220px;
    display: flex;
    align-items: flex-end;
    gap: 18px;
    border-bottom: 1px solid #e2e8f0;
    background:
        repeating-linear-gradient(
            to top,
            transparent,
            transparent 43px,
            #f1f5f9 44px
        );
}

.chart-month {
    flex: 1;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 7px;
    position: relative;
}

.chart-bar {
    width: 26px;
    border-radius: 7px 7px 2px 2px;
    min-height: 2px;
    position: relative;
    cursor: pointer;
    transition: .15s;
}

.chart-bar:hover {
    opacity: .78;
    transform: translateY(-2px);
}

.bar-in {
    background: #4f46e5;
}

.bar-out {
    background: #f97316;
}

.chart-value {
    position: absolute;
    bottom: calc(100% + 5px);
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: 700;
    color: #475569;
}

.chart-labels {
    display: flex;
    gap: 18px;
    padding-top: 9px;
}

.chart-label {
    flex: 1;
    text-align: center;
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
}


/* MONTH SUMMARY */

.summary-panel {
    padding: 20px;
}

.flow-summary {
    display: grid;
    gap: 13px;
}

.flow-card {
    padding: 15px;
    border-radius: 12px;
    border: 1px solid;
}

.flow-card strong {
    display: block;
    font-size: 24px;
    margin-top: 3px;
}

.flow-card span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.flow-in {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
}

.flow-out {
    background: #fff7ed;
    border-color: #fed7aa;
    color: #c2410c;
}

.flow-net {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #334155;
}

.bottom-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.stock-table {
    width: 100%;
    border-collapse: collapse;
}

.stock-table th {
    background: #f8fafc;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10px;
    padding: 11px 15px;
    text-align: left;
}

.stock-table td {
    border-top: 1px solid #f1f5f9;
    padding: 13px 15px;
    font-size: 13px;
    color: #334155;
}

.stock-badge {
    display: inline-block;
    min-width: 36px;
    text-align: center;
    padding: 4px 8px;
    border-radius: 7px;
    font-weight: 700;
    font-size: 11px;
}

.stock-critical {
    background: #ffe4e6;
    color: #be123c;
}

.stock-warning {
    background: #ffedd5;
    color: #c2410c;
}

.activity-list {
    padding: 3px 20px 8px;
}

.activity-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 13px 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-row:last-child {
    border-bottom: 0;
}

.activity-name {
    font-size: 13px;
    color: #0f172a;
    font-weight: 700;
}

.activity-info {
    margin-top: 3px;
    color: #94a3b8;
    font-size: 11px;
}

.activity-number {
    color: #e11d48;
    font-weight: 800;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 12px;
    padding: 20px;
}

.quick-button {
    text-decoration: none;
    color: #334155;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 14px 10px;
    border-radius: 12px;
    text-align: center;
    font-weight: 700;
    font-size: 12px;
    transition: .15s;
}

.quick-button:hover {
    border-color: #a5b4fc;
    background: #f8faff;
    transform: translateY(-1px);
}

.empty-state {
    text-align: center;
    color: #94a3b8;
    padding: 28px 20px;
    font-size: 13px;
}

@media (max-width: 1100px) {

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .content-grid,
    .bottom-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 760px) {

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .quick-actions {
        grid-template-columns: repeat(2,1fr);
    }

}

@media (max-width: 480px) {

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .chart-bar {
        width: 13px;
    }

}

</style>


@php

    $netBulanIni = ($totalMasuk ?? 0) - ($totalKeluar ?? 0);

@endphp


<div class="dashboard-wrap">

<div class="dashboard-container">


    {{-- HEADER --}}

    <div class="dashboard-top">

        <div class="dashboard-title">

            <h1>
                Dashboard Inventaris
            </h1>

            <p>
                Monitoring stok dan aktivitas perangkat jaringan secara real-time.
            </p>

        </div>


        <div class="date-box">

            {{ now()->translatedFormat('d F Y') }}

            &nbsp;•&nbsp;

            {{ strtoupper(Auth::user()->role) }}

        </div>

    </div>



    {{-- STATISTICS --}}

    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-label">
                Total Barang
            </div>

            <div class="stat-number">
                {{ number_format($totalBarang ?? 0) }}
            </div>

            <div class="stat-desc">
                Jenis perangkat
            </div>


            <div class="stat-icon blue-icon">

                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                    />

                </svg>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-label">
                Total Stok
            </div>

            <div class="stat-number">
                {{ number_format($totalStok ?? 0) }}
            </div>

            <div class="stat-desc">
                Unit tersedia
            </div>


            <div class="stat-icon green-icon">

                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 8h14M5 12h14M5 16h14"
                    />

                </svg>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-label">
                Masuk Bulan Ini
            </div>

            <div class="stat-number" style="color:#2563eb;">
                +{{ number_format($totalMasuk ?? 0) }}
            </div>

            <div class="stat-desc">
                Unit barang masuk
            </div>


            <div class="stat-icon cyan-icon">

                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 19V5m0 0l-5 5m5-5l5 5"
                    />

                </svg>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-label">
                Keluar Bulan Ini
            </div>

            <div class="stat-number" style="color:#ea580c;">
                {{ number_format($totalKeluar ?? 0) }}
            </div>

            <div class="stat-desc">
                Unit barang keluar
            </div>


            <div class="stat-icon orange-icon">

                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 5v14m0 0l5-5m-5 5l-5-5"
                    />

                </svg>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-label" style="color:#e11d48;">
                Stok Menipis
            </div>

            <div class="stat-number" style="color:#e11d48;">
                {{ number_format($jumlahStokMenipis ?? 0) }}
            </div>

            <div class="stat-desc">
                Perlu perhatian
            </div>


            <div class="stat-icon red-icon">

                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01M10.3 3.9L3 17a2 2 0 001.7 3h14.6a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"
                    />

                </svg>

            </div>

        </div>


    </div>



    {{-- CHART + MONTH SUMMARY --}}

    <div class="content-grid">


        <div class="panel">

            <div class="panel-header">

                <div>

                    <div class="panel-title">
                        Pergerakan Inventaris
                    </div>

                    <div class="panel-subtitle">
                        Barang masuk vs barang keluar selama 6 bulan terakhir
                    </div>

                </div>


                <div class="legend">

                    <div class="legend-item">

                        <span class="legend-dot legend-in"></span>

                        Masuk

                    </div>


                    <div class="legend-item">

                        <span class="legend-dot legend-out"></span>

                        Keluar

                    </div>

                </div>

            </div>


            <div class="chart-container">


                <div class="chart-area">

                    @foreach($grafikLabels ?? [] as $index => $label)

                        @php

                            $masuk = $grafikMasuk[$index] ?? 0;

                            $keluar = $grafikKeluar[$index] ?? 0;

                            $masukHeight =
                                ($masuk / max($grafikMaksimum ?? 1, 1)) * 88;

                            $keluarHeight =
                                ($keluar / max($grafikMaksimum ?? 1, 1)) * 88;

                        @endphp


                        <div class="chart-month">


                            <div
                                class="chart-bar bar-in"
                                style="height:{{ $masukHeight }}%;"
                                title="Barang Masuk: {{ $masuk }}"
                            >

                                @if($masuk > 0)

                                    <span class="chart-value">
                                        {{ $masuk }}
                                    </span>

                                @endif

                            </div>


                            <div
                                class="chart-bar bar-out"
                                style="height:{{ $keluarHeight }}%;"
                                title="Barang Keluar: {{ $keluar }}"
                            >

                                @if($keluar > 0)

                                    <span class="chart-value">
                                        {{ $keluar }}
                                    </span>

                                @endif

                            </div>


                        </div>

                    @endforeach

                </div>


                <div class="chart-labels">

                    @foreach($grafikLabels ?? [] as $label)

                        <div class="chart-label">
                            {{ $label }}
                        </div>

                    @endforeach

                </div>


            </div>

        </div>



        {{-- SUMMARY BULAN INI --}}

        <div class="panel">

            <div class="panel-header">

                <div>

                    <div class="panel-title">
                        Ringkasan Bulan Ini
                    </div>

                    <div class="panel-subtitle">
                        Pergerakan stok bulan berjalan
                    </div>

                </div>

            </div>


            <div class="summary-panel">


                <div class="flow-summary">


                    <div class="flow-card flow-in">

                        <span>
                            Barang Masuk
                        </span>

                        <strong>
                            +{{ number_format($totalMasuk ?? 0) }}
                        </strong>

                    </div>


                    <div class="flow-card flow-out">

                        <span>
                            Barang Keluar
                        </span>

                        <strong>
                            -{{ number_format($totalKeluar ?? 0) }}
                        </strong>

                    </div>


                    <div class="flow-card flow-net">

                        <span>
                            Perubahan Bersih
                        </span>

                        <strong
                            style="color:
                            {{ $netBulanIni >= 0 ? '#059669' : '#e11d48' }}"
                        >

                            {{ $netBulanIni >= 0 ? '+' : '' }}
                            {{ number_format($netBulanIni) }}

                        </strong>

                    </div>


                </div>

            </div>

        </div>


    </div>



    {{-- STOCK + ACTIVITIES --}}

    <div class="bottom-grid">


        {{-- STOK MENIPIS --}}

        <div class="panel">

            <div class="panel-header">

                <div>

                    <div class="panel-title">
                        Perangkat Stok Menipis
                    </div>

                    <div class="panel-subtitle">
                        Perangkat dengan stok ≤ 10 unit
                    </div>

                </div>


                <span class="stock-badge
                    {{ ($jumlahStokMenipis ?? 0) > 0
                        ? 'stock-critical'
                        : '' }}">

                    {{ $jumlahStokMenipis ?? 0 }}

                </span>

            </div>


            @if(($stokMenipis ?? collect())->count())


                <div style="overflow:auto;">

                    <table class="stock-table">

                        <thead>

                            <tr>

                                <th>
                                    Perangkat
                                </th>

                                <th>
                                    Kategori
                                </th>

                                <th>
                                    Stok
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            @foreach($stokMenipis as $barang)

                                <tr>

                                    <td>

                                        <strong>
                                            {{ $barang->nama_barang }}
                                        </strong>

                                    </td>


                                    <td>

                                        {{ $barang->kategori->nama_kategori ?? '-' }}

                                    </td>


                                    <td>

                                        {{ $barang->stok }}

                                    </td>


                                    <td>


                                        @if($barang->stok <= 3)

                                            <span class="stock-badge stock-critical">
                                                Kritis
                                            </span>

                                        @else

                                            <span class="stock-badge stock-warning">
                                                Menipis
                                            </span>

                                        @endif


                                    </td>

                                </tr>

                            @endforeach


                        </tbody>

                    </table>

                </div>


            @else

                <div class="empty-state">

                    ✓ Semua stok dalam kondisi aman.

                </div>

            @endif

        </div>



        {{-- AKTIVITAS TERBARU --}}

        <div class="panel">

            <div class="panel-header">

                <div>

                    <div class="panel-title">
                        Aktivitas Barang Keluar
                    </div>

                    <div class="panel-subtitle">
                        Transaksi terakhir teknisi
                    </div>

                </div>

            </div>


            <div class="activity-list">


                @forelse($barangKeluarTerbaru ?? [] as $keluar)


                    <div class="activity-row">


                        <div>

                            <div class="activity-name">

                                {{ $keluar->barang->nama_barang ?? '-' }}

                            </div>


                            <div class="activity-info">

                                {{ $keluar->teknisi->nama ?? $keluar->pengguna ?? '-' }}

                                •

                                {{ $keluar->tanggal?->format('d/m/Y') ?? '-' }}

                            </div>

                        </div>


                        <div class="activity-number">

                            -{{ number_format($keluar->jumlah) }}

                        </div>


                    </div>


                @empty


                    <div class="empty-state">

                        Belum ada transaksi barang keluar.

                    </div>


                @endforelse


            </div>

        </div>


    </div>



    {{-- QUICK ACTIONS ADMIN --}}

    @if(Auth::user()->role === 'admin')

        <div class="panel">

            <div class="panel-header">

                <div>

                    <div class="panel-title">
                        Akses Cepat
                    </div>

                    <div class="panel-subtitle">
                        Menu administrasi yang sering digunakan
                    </div>

                </div>

            </div>


            <div class="quick-actions">


                <a
                    href="{{ route('barang.create') }}"
                    class="quick-button"
                >
                    + Tambah Barang
                </a>


                <a
                    href="{{ route('barang-masuk.create') }}"
                    class="quick-button"
                >
                    + Barang Masuk
                </a>


                <a
                    href="{{ route('barang-keluar.create') }}"
                    class="quick-button"
                >
                    + Barang Keluar
                </a>


                <a
                    href="{{ route('teknisi.index') }}"
                    class="quick-button"
                >
                    Data Teknisi
                </a>


            </div>

        </div>

    @endif


</div>

</div>

</x-app-layout>