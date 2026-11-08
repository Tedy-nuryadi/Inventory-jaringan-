<x-app-layout>

<style>

.kategori-page {
    background:#f8fafc;
    min-height:calc(100vh - 64px);
    padding:28px 20px 50px;
}

.kategori-container {
    max-width:1200px;
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
    font-weight:800;
    color:#0f172a;
}

.page-subtitle {
    margin:6px 0 0;
    font-size:14px;
    color:#64748b;
}

.btn-add {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    background:#2563eb;
    color:#ffffff;
    padding:11px 18px;
    border-radius:9px;
    text-decoration:none;
    font-size:13px;
    font-weight:700;
    box-shadow:0 2px 6px rgba(37,99,235,.20);
    white-space:nowrap;
}

.btn-add:hover {
    background:#1d4ed8;
}

.message-success {
    background:#dcfce7;
    border:1px solid #86efac;
    color:#166534;
    padding:13px 15px;
    border-radius:10px;
    margin-bottom:18px;
    font-size:13px;
}

.message-error {
    background:#fff1f2;
    border:1px solid #fecdd3;
    color:#9f1239;
    padding:13px 15px;
    border-radius:10px;
    margin-bottom:18px;
    font-size:13px;
}

.summary-card {
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:13px;
    padding:16px 18px;
    margin-bottom:18px;
    box-shadow:0 2px 7px rgba(15,23,42,.035);
}

.summary-label {
    font-size:11px;
    font-weight:800;
    color:#64748b;
    text-transform:uppercase;
}

.summary-value {
    margin-top:3px;
    font-size:25px;
    font-weight:800;
    color:#0f172a;
}

.main-card {
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    box-shadow:0 3px 12px rgba(15,23,42,.05);
    overflow:hidden;
}

.kategori-table {
    width:100%;
    border-collapse:collapse;
}

.kategori-table thead {
    background:#f8fafc;
}

.kategori-table th {
    padding:14px 18px;
    font-size:10px;
    font-weight:800;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:1px solid #e2e8f0;
}

.kategori-table td {
    padding:16px 18px;
    border-bottom:1px solid #f1f5f9;
    font-size:13px;
    color:#334155;
}

.kategori-table tbody tr:hover {
    background:#f8fafc;
}

.kategori-name {
    font-size:14px;
    font-weight:750;
    color:#0f172a;
}

.count-badge {
    display:inline-block;
    min-width:38px;
    background:#eef2ff;
    color:#4338ca;
    border:1px solid #e0e7ff;
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
}

.count-empty {
    background:#f1f5f9;
    color:#64748b;
    border-color:#e2e8f0;
}

.status-used {
    display:inline-block;
    background:#fff7ed;
    color:#c2410c;
    padding:5px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:750;
}

.status-empty {
    display:inline-block;
    background:#ecfdf5;
    color:#047857;
    padding:5px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:750;
}

.action-wrap {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
}

.btn-edit {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#f59e0b;
    color:#ffffff;
    text-decoration:none;
    border-radius:7px;
    padding:7px 12px;
    font-size:11px;
    font-weight:750;
}

.btn-edit:hover {
    background:#d97706;
}

.btn-delete {
    background:#dc2626;
    color:#ffffff;
    border:none;
    border-radius:7px;
    padding:7px 12px;
    font-size:11px;
    font-weight:750;
    cursor:pointer;
}

.btn-delete:hover {
    background:#b91c1c;
}

.btn-disabled {
    background:#e2e8f0;
    color:#94a3b8;
    border:none;
    border-radius:7px;
    padding:7px 12px;
    font-size:11px;
    font-weight:750;
    cursor:not-allowed;
}

.empty-state {
    text-align:center;
    padding:50px 20px !important;
    color:#94a3b8 !important;
}

@media(max-width:700px) {

    .page-header {
        flex-direction:column;
        align-items:flex-start;
    }

    .btn-add {
        width:100%;
    }

}

</style>


<div class="kategori-page">

<div class="kategori-container">


    {{-- HEADER --}}
    <div class="page-header">

        <div>

            <h1 class="page-title">
                Kategori Barang
            </h1>

            <p class="page-subtitle">
                Kelola kategori perangkat inventaris jaringan.
            </p>

        </div>


        <a
            href="{{ route('kategori.create') }}"
            class="btn-add"
        >
            <span style="font-size:18px;line-height:1;">
                +
            </span>

            Tambah Kategori
        </a>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="message-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- ERROR --}}
    @if(session('error'))

        <div class="message-error">
            {{ session('error') }}
        </div>

    @endif


    {{-- SUMMARY --}}
    <div class="summary-card">

        <div class="summary-label">
            Total Kategori
        </div>

        <div class="summary-value">
            {{ number_format($kategoris->count()) }}
        </div>

    </div>


    {{-- TABLE --}}
    <div class="main-card">

        <div style="overflow-x:auto;">

            <table class="kategori-table">

                <thead>

                    <tr>

                        <th style="text-align:left;">
                            Nama Kategori
                        </th>

                        <th style="text-align:center;">
                            Jumlah Barang
                        </th>

                        <th style="text-align:center;">
                            Status
                        </th>

                        <th style="text-align:center;">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($kategoris as $kategori)

                        <tr>

                            {{-- NAMA --}}
                            <td>

                                <div class="kategori-name">
                                    {{ $kategori->nama_kategori }}
                                </div>

                            </td>


                            {{-- JUMLAH BARANG --}}
                            <td style="text-align:center;">

                                @if($kategori->barangs_count > 0)

                                    <span class="count-badge">
                                        {{ $kategori->barangs_count }}
                                    </span>

                                @else

                                    <span class="count-badge count-empty">
                                        0
                                    </span>

                                @endif

                            </td>


                            {{-- STATUS --}}
                            <td style="text-align:center;">

                                @if($kategori->barangs_count > 0)

                                    <span class="status-used">
                                        Digunakan
                                    </span>

                                @else

                                    <span class="status-empty">
                                        Kosong
                                    </span>

                                @endif

                            </td>


                            {{-- AKSI --}}
                            <td>

                                <div class="action-wrap">


                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('kategori.edit', $kategori->id) }}"
                                        class="btn-edit"
                                    >
                                        Edit
                                    </a>


                                    {{-- HAPUS --}}
                                    @if($kategori->barangs_count == 0)

                                        <form
                                            action="{{ route('kategori.destroy', $kategori->id) }}"
                                            method="POST"
                                            style="margin:0;"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="btn-delete"
                                                onclick="return confirm('Yakin ingin menghapus kategori {{ $kategori->nama_kategori }}?')"
                                            >
                                                Hapus
                                            </button>

                                        </form>

                                    @else

                                        <button
                                            type="button"
                                            class="btn-disabled"
                                            disabled
                                            title="Kategori masih digunakan oleh barang"
                                        >
                                            Hapus
                                        </button>

                                    @endif


                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="empty-state"
                            >

                                <div style="
                                    font-size:15px;
                                    font-weight:700;
                                    color:#475569;
                                ">
                                    Belum ada kategori
                                </div>


                                <div style="
                                    margin-top:5px;
                                    font-size:12px;
                                ">
                                    Klik Tambah Kategori untuk membuat kategori baru.
                                </div>

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