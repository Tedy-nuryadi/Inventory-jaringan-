<x-app-layout>

    <div style="
        background:#f8fafc;
        min-height:calc(100vh - 64px);
        padding:30px 20px;
    ">

        <div style="
            max-width:1000px;
            margin:auto;
        ">

            {{-- HEADER --}}
            <div style="margin-bottom:22px;">

                <h1 style="
                    font-size:26px;
                    font-weight:800;
                    color:#0f172a;
                    margin:0;
                ">
                    Tambah Perangkat Baru
                </h1>

                <p style="
                    margin-top:6px;
                    color:#64748b;
                    font-size:14px;
                ">
                    Tambahkan perangkat baru ke dalam inventaris jaringan.
                </p>

            </div>


            {{-- ERROR --}}
            @if ($errors->any())

                <div style="
                    background:#fee2e2;
                    border:1px solid #fecaca;
                    color:#991b1b;
                    padding:14px 16px;
                    border-radius:10px;
                    margin-bottom:20px;
                ">

                    <strong>
                        Data belum lengkap:
                    </strong>

                    <ul style="
                        margin-top:8px;
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


            {{-- FORM CARD --}}
            <div style="
                background:#ffffff;
                border:1px solid #e2e8f0;
                border-radius:16px;
                box-shadow:0 2px 10px rgba(15,23,42,.05);
                padding:26px;
            ">

                <form
                    action="{{ route('barang.store') }}"
                    method="POST"
                >

                    @csrf


                    {{-- ROW 1 --}}
                    <div class="form-grid">

                        {{-- KODE BARANG --}}
                        <div>

                            <label
                                for="kode_barang"
                                class="form-label"
                            >
                                Kode Barang
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="kode_barang"
                                name="kode_barang"
                                value="{{ old('kode_barang') }}"
                                placeholder="Contoh: MD-001"
                                required
                                class="form-control"
                            >

                            @error('kode_barang')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- NAMA BARANG --}}
                        <div>

                            <label
                                for="nama_barang"
                                class="form-label"
                            >
                                Nama Perangkat / Barang
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="nama_barang"
                                name="nama_barang"
                                value="{{ old('nama_barang') }}"
                                placeholder="Contoh: CDATA Dualband"
                                required
                                class="form-control"
                            >

                            @error('nama_barang')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ROW 2 --}}
                    <div class="form-grid">

                        {{-- KATEGORI --}}
                        <div>

                            <label
                                for="kategori_id"
                                class="form-label"
                            >
                                Kategori Perangkat
                                <span class="required">*</span>
                            </label>

                            <select
                                id="kategori_id"
                                name="kategori_id"
                                required
                                class="form-control"
                            >

                                <option value="">
                                    -- Pilih Kategori --
                                </option>

                                @foreach($kategoris as $kategori)

                                    <option
                                        value="{{ $kategori->id }}"
                                        @selected(
                                            old('kategori_id')
                                            == $kategori->id
                                        )
                                    >
                                        {{ $kategori->nama_kategori }}
                                    </option>

                                @endforeach

                            </select>

                            @error('kategori_id')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- MERK --}}
                        <div>

                            <label
                                for="merk"
                                class="form-label"
                            >
                                Merk
                            </label>

                            <input
                                type="text"
                                id="merk"
                                name="merk"
                                value="{{ old('merk') }}"
                                placeholder="Contoh: CDATA / Zimmlink / MikroTik"
                                class="form-control"
                            >

                            @error('merk')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ROW 3 --}}
                    <div class="form-grid">

                        {{-- STOK AWAL --}}
                        <div>

                            <label
                                for="stok"
                                class="form-label"
                            >
                                Stok Awal
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="stok"
                                name="stok"
                                value="{{ old('stok', 0) }}"
                                min="0"
                                required
                                class="form-control"
                            >

                            <div class="form-help">
                                Jumlah stok yang tersedia saat perangkat dibuat.
                            </div>

                            @error('stok')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- STOK MINIMUM --}}
                        <div>

                            <label
                                for="stok_minimum"
                                class="form-label"
                            >
                                Stok Minimum
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="stok_minimum"
                                name="stok_minimum"
                                value="{{ old('stok_minimum', 5) }}"
                                min="0"
                                required
                                class="form-control"
                            >

                            <div class="form-help">
                                Peringatan muncul jika stok sama dengan
                                atau lebih kecil dari nilai minimum ini.
                            </div>

                            @error('stok_minimum')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- ROW 4 --}}
                    <div class="form-grid">

                        {{-- SATUAN --}}
                        <div>

                            <label
                                for="satuan"
                                class="form-label"
                            >
                                Satuan
                                <span class="required">*</span>
                            </label>

                            <select
                                id="satuan"
                                name="satuan"
                                required
                                class="form-control"
                            >

                                <option value="">
                                    -- Pilih Satuan --
                                </option>

                                <option
                                    value="pcs"
                                    @selected(old('satuan') === 'pcs')
                                >
                                    PCS
                                </option>

                                <option
                                    value="unit"
                                    @selected(old('satuan') === 'unit')
                                >
                                    Unit
                                </option>

                                <option
                                    value="meter"
                                    @selected(old('satuan') === 'meter')
                                >
                                    Meter
                                </option>

                                <option
                                    value="roll"
                                    @selected(old('satuan') === 'roll')
                                >
                                    Roll
                                </option>

                                <option
                                    value="box"
                                    @selected(old('satuan') === 'box')
                                >
                                    Box
                                </option>

                            </select>

                            @error('satuan')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- LOKASI --}}
                        <div>

                            <label
                                for="lokasi"
                                class="form-label"
                            >
                                Lokasi Penyimpanan
                            </label>

                            <input
                                type="text"
                                id="lokasi"
                                name="lokasi"
                                value="{{ old('lokasi') }}"
                                placeholder="Contoh: Gudang A - Rak 2"
                                class="form-control"
                            >

                            @error('lokasi')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- BUTTON --}}
                    <div class="form-actions">

                        <a
                            href="{{ route('barang.index') }}"
                            class="btn-cancel"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn-save"
                        >
                            Simpan Perangkat
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <style>

        .form-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
            margin-bottom:20px;
        }

        .form-label {
            display:block;
            font-size:13px;
            font-weight:700;
            color:#334155;
            margin-bottom:7px;
        }

        .required {
            color:#dc2626;
        }

        .form-control {
            width:100%;
            min-height:44px;
            border:1px solid #cbd5e1;
            border-radius:9px;
            padding:11px 13px;
            color:#0f172a;
            background:#ffffff;
            box-sizing:border-box;
        }

        .form-control:focus {
            outline:none;
            border-color:#2563eb;
            box-shadow:0 0 0 3px rgba(37,99,235,.10);
        }

        .form-help {
            margin-top:6px;
            font-size:12px;
            line-height:1.5;
            color:#64748b;
        }

        .field-error {
            margin-top:6px;
            color:#dc2626;
            font-size:12px;
        }

        .form-actions {
            border-top:1px solid #e2e8f0;
            padding-top:20px;
            display:flex;
            justify-content:flex-end;
            align-items:center;
            gap:10px;
        }

        .btn-cancel {
            display:inline-block;
            background:#f1f5f9;
            color:#475569;
            border:1px solid #cbd5e1;
            padding:10px 20px;
            border-radius:8px;
            text-decoration:none;
            font-weight:700;
        }

        .btn-save {
            background:#2563eb;
            color:#ffffff;
            border:none;
            padding:11px 22px;
            border-radius:8px;
            font-weight:700;
            cursor:pointer;
            box-shadow:0 2px 5px rgba(37,99,235,.25);
        }

        .btn-save:hover {
            background:#1d4ed8;
        }

        @media (max-width:700px) {

            .form-grid {
                grid-template-columns:1fr;
            }

            .form-actions {
                flex-direction:column-reverse;
            }

            .btn-cancel,
            .btn-save {
                width:100%;
                text-align:center;
            }

        }

    </style>

</x-app-layout>