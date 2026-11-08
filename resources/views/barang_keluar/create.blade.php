<x-app-layout>

<style>

.keluar-page {
    min-height:calc(100vh - 64px);
    background:#f8fafc;
    padding:30px 20px 50px;
}

.keluar-container {
    max-width:1050px;
    margin:auto;
}

.page-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
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

.role-badge {
    display:inline-flex;
    align-items:center;
    background:#eef2ff;
    color:#4338ca;
    border:1px solid #c7d2fe;
    padding:7px 11px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
}

.form-card {
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    box-shadow:0 3px 12px rgba(15,23,42,.05);
    overflow:hidden;
}

.form-section {
    padding:25px;
}

.form-section + .form-section {
    border-top:1px solid #e2e8f0;
}

.section-title {
    font-size:14px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:18px;
}

.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.form-group {
    margin-bottom:18px;
}

.form-label {
    display:block;
    font-size:12px;
    font-weight:750;
    color:#334155;
    margin-bottom:7px;
}

.required {
    color:#dc2626;
}

.form-control {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:9px;
    padding:11px 13px;
    color:#0f172a;
    background:#fff;
    outline:none;
    font-size:13px;
}

.form-control:focus {
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

textarea.form-control {
    min-height:100px;
    resize:vertical;
}

.technician-auto {
    background:#ecfdf5;
    border:1px solid #a7f3d0;
    border-radius:10px;
    padding:14px 15px;
}

.technician-name {
    font-size:15px;
    font-weight:800;
    color:#065f46;
}

.technician-info {
    margin-top:3px;
    font-size:11px;
    color:#047857;
}

.stock-box {
    margin-top:8px;
    display:flex;
    align-items:center;
    gap:6px;
    font-size:12px;
    color:#64748b;
}

.stock-number {
    font-weight:800;
    color:#0f172a;
}

.photo-info {
    margin-top:6px;
    color:#94a3b8;
    font-size:11px;
}

.error-box {
    background:#fff1f2;
    border:1px solid #fecdd3;
    color:#9f1239;
    border-radius:11px;
    padding:14px 17px;
    margin-bottom:18px;
    font-size:13px;
}

.error-box ul {
    margin:7px 0 0;
    padding-left:20px;
}

.form-footer {
    padding:18px 25px;
    border-top:1px solid #e2e8f0;
    background:#f8fafc;
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.btn-cancel {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    background:#fff;
    border:1px solid #cbd5e1;
    color:#475569;
    border-radius:9px;
    padding:10px 18px;
    font-size:13px;
    font-weight:750;
}

.btn-save {
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:9px;
    padding:11px 20px;
    font-size:13px;
    font-weight:750;
    cursor:pointer;
    box-shadow:0 2px 6px rgba(37,99,235,.20);
}

.btn-save:hover {
    background:#1d4ed8;
}

@media(max-width:700px) {

    .form-grid {
        grid-template-columns:1fr;
    }

    .page-header {
        flex-direction:column;
    }

    .form-footer {
        flex-direction:column-reverse;
    }

    .btn-cancel,
    .btn-save {
        width:100%;
    }

}

</style>


<div class="keluar-page">

<div class="keluar-container">


    {{-- HEADER --}}
    <div class="page-header">

        <div>

            <h1 class="page-title">
                Tambah Barang Keluar
            </h1>

            <p class="page-subtitle">
                Catat pengambilan dan penggunaan perangkat jaringan.
            </p>

        </div>


        <div class="role-badge">

            {{ strtoupper(auth()->user()->role) }}

        </div>

    </div>



    {{-- VALIDATION ERROR --}}
    @if ($errors->any())

        <div class="error-box">

            <strong>
                Data belum dapat disimpan.
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif



    <form
        action="{{ route('barang-keluar.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >

        @csrf



        {{-- IDENTITAS --}}
        <div class="form-section">

            <div class="section-title">
                Informasi Pengambilan
            </div>


            <div class="form-grid">


                {{-- TANGGAL --}}
                <div class="form-group">

                    <label
                        for="tanggal"
                        class="form-label"
                    >
                        Tanggal
                        <span class="required">*</span>
                    </label>


                    <input
                        type="date"
                        name="tanggal"
                        id="tanggal"
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        required
                        class="form-control"
                    >

                </div>



                {{-- TEKNISI --}}
                <div class="form-group">

                    <label class="form-label">

                        Teknisi
                        <span class="required">*</span>

                    </label>


                    @if(auth()->user()->role === 'admin')


                        <select
                            name="teknisi_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Teknisi --
                            </option>


                            @foreach($teknisis as $teknisi)

                                <option
                                    value="{{ $teknisi->id }}"
                                    {{ old('teknisi_id') == $teknisi->id ? 'selected' : '' }}
                                >

                                    {{ $teknisi->nama }}

                                </option>

                            @endforeach

                        </select>


                    @else


                        <div class="technician-auto">

                            <div class="technician-name">

                                {{ $teknisiLogin->nama }}

                            </div>

                            <div class="technician-info">

                                Otomatis berdasarkan akun yang sedang login.

                            </div>

                        </div>


                    @endif

                </div>

            </div>

        </div>



        {{-- BARANG --}}
        <div class="form-section">

            <div class="section-title">
                Detail Barang
            </div>


            <div class="form-grid">


                {{-- BARANG --}}
                <div class="form-group">

                    <label
                        for="barang_id"
                        class="form-label"
                    >
                        Barang / Perangkat
                        <span class="required">*</span>
                    </label>


                    <select
                        name="barang_id"
                        id="barang_id"
                        class="form-control"
                        required
                    >

                        <option
                            value=""
                            data-stok="0"
                        >
                            -- Pilih Barang --
                        </option>


                        @foreach($barangs as $barang)

                            <option
                                value="{{ $barang->id }}"
                                data-stok="{{ $barang->stok }}"
                                data-satuan="{{ $barang->satuan }}"
                                {{ old('barang_id') == $barang->id ? 'selected' : '' }}
                            >

                                {{ $barang->kode_barang }}
                                -
                                {{ $barang->nama_barang }}
                                (Stok: {{ $barang->stok }} {{ $barang->satuan }})

                            </option>

                        @endforeach

                    </select>


                    <div class="stock-box">

                        Stok tersedia:

                        <span
                            id="stokTersedia"
                            class="stock-number"
                        >
                            -
                        </span>

                    </div>

                </div>



                {{-- JUMLAH --}}
                <div class="form-group">

                    <label
                        for="jumlah"
                        class="form-label"
                    >
                        Jumlah Keluar
                        <span class="required">*</span>
                    </label>


                    <input
                        type="number"
                        name="jumlah"
                        id="jumlah"
                        value="{{ old('jumlah', 1) }}"
                        min="1"
                        required
                        class="form-control"
                    >

                </div>

            </div>



            {{-- KEPERLUAN --}}
            <div class="form-group">

                <label
                    for="keperluan"
                    class="form-label"
                >
                    Keperluan / Penggunaan
                    <span class="required">*</span>
                </label>


                <textarea
                    name="keperluan"
                    id="keperluan"
                    class="form-control"
                    required
                    placeholder="Contoh: Instalasi pelanggan baru, penggantian modem rusak, maintenance jaringan..."
                >{{ old('keperluan') }}</textarea>

            </div>

        </div>



        {{-- FOTO --}}
        <div class="form-section">

            <div class="section-title">
                Dokumentasi
            </div>


            <div class="form-group">

                <label
                    for="foto"
                    class="form-label"
                >
                    Foto Bukti Pengambilan
                </label>


                <input
                    type="file"
                    name="foto"
                    id="foto"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="form-control"
                >


                <div class="photo-info">

                    JPG, JPEG, PNG atau WEBP. Maksimal 2 MB.

                </div>

            </div>

        </div>



        {{-- FOOTER --}}
        <div class="form-footer">


            <a
                href="{{ route('barang-keluar.index') }}"
                class="btn-cancel"
            >
                Batal
            </a>


            <button
                type="submit"
                class="btn-save"
            >
                Simpan Barang Keluar
            </button>


        </div>

    </form>


</div>

</div>



<script>

document.addEventListener('DOMContentLoaded', function () {

    const barangSelect =
        document.getElementById('barang_id');

    const stokTersedia =
        document.getElementById('stokTersedia');


    function updateStok()
    {
        const option =
            barangSelect.options[
                barangSelect.selectedIndex
            ];


        const stok =
            option.getAttribute('data-stok');

        const satuan =
            option.getAttribute('data-satuan');


        if (
            barangSelect.value
            &&
            stok !== null
        ) {

            stokTersedia.textContent =
                stok + ' ' + (satuan ?? '');

        } else {

            stokTersedia.textContent =
                '-';

        }
    }


    barangSelect.addEventListener(
        'change',
        updateStok
    );


    updateStok();

});

</script>

</x-app-layout>