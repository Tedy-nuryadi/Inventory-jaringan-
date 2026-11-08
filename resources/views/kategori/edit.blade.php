<x-app-layout>

<div style="
    background:#f8fafc;
    min-height:calc(100vh - 64px);
    padding:30px 20px 50px;
">

    <div style="
        max-width:750px;
        margin:auto;
    ">

        <h1 style="
            margin:0;
            font-size:27px;
            font-weight:800;
            color:#0f172a;
        ">
            Edit Kategori
        </h1>


        <p style="
            margin:6px 0 22px;
            color:#64748b;
            font-size:14px;
        ">
            Perbarui nama kategori perangkat.
        </p>


        @if ($errors->any())

            <div style="
                background:#fff1f2;
                border:1px solid #fecdd3;
                color:#9f1239;
                border-radius:10px;
                padding:13px 15px;
                margin-bottom:18px;
            ">

                <strong>
                    Data belum dapat disimpan.
                </strong>


                <ul style="
                    margin:7px 0 0;
                    padding-left:20px;
                ">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            action="{{ route('kategori.update', $kategori->id) }}"
            method="POST"
            style="
                background:#fff;
                border:1px solid #e2e8f0;
                border-radius:16px;
                padding:25px;
                box-shadow:0 3px 12px rgba(15,23,42,.05);
            "
        >

            @csrf
            @method('PUT')


            <label
                for="nama_kategori"
                style="
                    display:block;
                    margin-bottom:8px;
                    font-size:13px;
                    font-weight:700;
                    color:#334155;
                "
            >
                Nama Kategori

                <span style="color:#dc2626;">
                    *
                </span>
            </label>


            <input
                type="text"
                id="nama_kategori"
                name="nama_kategori"
                value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                required
                autofocus
                style="
                    width:100%;
                    border:1px solid #cbd5e1;
                    border-radius:9px;
                    padding:11px 13px;
                    font-size:14px;
                    color:#0f172a;
                    outline:none;
                "
            >


            <div style="
                margin-top:25px;
                padding-top:18px;
                border-top:1px solid #e2e8f0;
                display:flex;
                justify-content:flex-end;
                gap:10px;
            ">

                <a
                    href="{{ route('kategori.index') }}"
                    style="
                        text-decoration:none;
                        background:#fff;
                        border:1px solid #cbd5e1;
                        color:#475569;
                        padding:10px 17px;
                        border-radius:9px;
                        font-size:13px;
                        font-weight:700;
                    "
                >
                    Batal
                </a>


                <button
                    type="submit"
                    style="
                        border:none;
                        background:#2563eb;
                        color:#fff;
                        padding:10px 18px;
                        border-radius:9px;
                        font-size:13px;
                        font-weight:700;
                        cursor:pointer;
                    "
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

</x-app-layout>