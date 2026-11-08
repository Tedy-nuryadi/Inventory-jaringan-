<x-app-layout>

<style>

.kategori-page {
    background:#f8fafc;
    min-height:calc(100vh - 64px);
    padding:30px 20px 50px;
}

.kategori-container {
    max-width:750px;
    margin:auto;
}

.page-title {
    margin:0;
    font-size:27px;
    font-weight:800;
    color:#0f172a;
}

.page-subtitle {
    margin:6px 0 22px;
    color:#64748b;
    font-size:14px;
}

.form-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:25px;
    box-shadow:0 3px 12px rgba(15,23,42,.05);
}

.form-label {
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
    color:#334155;
}

.form-control {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:9px;
    padding:11px 13px;
    font-size:14px;
    color:#0f172a;
    outline:none;
}

.form-control:focus {
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

.error-box {
    background:#fff1f2;
    border:1px solid #fecdd3;
    color:#9f1239;
    border-radius:10px;
    padding:13px 15px;
    margin-bottom:18px;
}

.form-footer {
    margin-top:25px;
    padding-top:18px;
    border-top:1px solid #e2e8f0;
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.btn-cancel {
    text-decoration:none;
    background:#fff;
    border:1px solid #cbd5e1;
    color:#475569;
    padding:10px 17px;
    border-radius:9px;
    font-size:13px;
    font-weight:700;
}

.btn-save {
    border:none;
    background:#2563eb;
    color:#fff;
    padding:10px 18px;
    border-radius:9px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
}

</style>


<div class="kategori-page">

    <div class="kategori-container">

        <h1 class="page-title">
            Tambah Kategori
        </h1>

        <p class="page-subtitle">
            Tambahkan kategori baru untuk perangkat inventaris.
        </p>


        @if ($errors->any())

            <div class="error-box">

                <strong>
                    Data belum dapat disimpan.
                </strong>

                <ul style="margin:7px 0 0;padding-left:20px;">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            action="{{ route('kategori.store') }}"
            method="POST"
            class="form-card"
        >

            @csrf


            <label
                for="nama_kategori"
                class="form-label"
            >
                Nama Kategori
                <span style="color:#dc2626;">*</span>
            </label>


            <input
                type="text"
                id="nama_kategori"
                name="nama_kategori"
                value="{{ old('nama_kategori') }}"
                placeholder="Contoh: Modem, Router, Kabel, Patchcord..."
                required
                autofocus
                class="form-control"
            >


            <div class="form-footer">

                <a
                    href="{{ route('kategori.index') }}"
                    class="btn-cancel"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    class="btn-save"
                >
                    Simpan Kategori
                </button>

            </div>

        </form>

    </div>

</div>

</x-app-layout>