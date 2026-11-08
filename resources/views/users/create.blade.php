<x-app-layout>

    <div
        x-data="{
            role: '{{ old('role', 'teknisi') }}'
        }"
        style="
            min-height:calc(100vh - 70px);
            background:#f8fafc;
            padding:32px 20px;
        "
    >

        <div style="
            max-width:900px;
            margin:0 auto;
        ">

            {{-- HEADER --}}
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:flex-start;
                gap:20px;
                margin-bottom:24px;
            ">

                <div>

                    <h1 style="
                        margin:0;
                        color:#0f172a;
                        font-size:28px;
                        font-weight:800;
                    ">
                        Tambah User
                    </h1>

                    <p style="
                        margin:7px 0 0;
                        color:#64748b;
                        font-size:14px;
                    ">
                        Buat akun Admin atau hubungkan akun baru dengan profil Teknisi.
                    </p>

                </div>


                <a
                    href="{{ route('users.index') }}"
                    style="
                        display:inline-flex;
                        align-items:center;
                        gap:7px;
                        padding:10px 15px;
                        border:1px solid #cbd5e1;
                        border-radius:9px;
                        background:#ffffff;
                        color:#475569;
                        text-decoration:none;
                        font-size:13px;
                        font-weight:700;
                    "
                >
                    ← Kembali
                </a>

            </div>


            {{-- VALIDATION ERROR --}}
            @if ($errors->any())

                <div style="
                    margin-bottom:20px;
                    padding:15px 17px;
                    border:1px solid #fecaca;
                    border-radius:11px;
                    background:#fef2f2;
                    color:#991b1b;
                ">

                    <div style="
                        font-weight:800;
                        margin-bottom:7px;
                    ">
                        Data belum dapat disimpan.
                    </div>

                    <ul style="
                        margin:0;
                        padding-left:20px;
                        font-size:13px;
                    ">

                        @foreach ($errors->all() as $error)

                            <li style="margin:3px 0;">
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- CARD --}}
            <div style="
                overflow:hidden;
                background:#ffffff;
                border:1px solid #e2e8f0;
                border-radius:16px;
                box-shadow:0 4px 18px rgba(15,23,42,.06);
            ">

                {{-- CARD HEADER --}}
                <div style="
                    padding:18px 22px;
                    border-bottom:1px solid #e2e8f0;
                    background:#ffffff;
                ">

                    <div style="
                        color:#0f172a;
                        font-size:15px;
                        font-weight:800;
                    ">
                        Informasi Akun
                    </div>

                    <div style="
                        margin-top:4px;
                        color:#94a3b8;
                        font-size:12px;
                    ">
                        Semua field bertanda * wajib diisi.
                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route('users.store') }}"
                    style="padding:24px;"
                >

                    @csrf


                    {{-- NAMA --}}
                    <div style="margin-bottom:19px;">

                        <label
                            for="name"
                            style="
                                display:block;
                                margin-bottom:7px;
                                color:#334155;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            Nama User
                            <span style="color:#dc2626;">*</span>
                        </label>


                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="Contoh: Budi Santoso"
                            style="
                                width:100%;
                                padding:11px 13px;
                                border:1px solid #cbd5e1;
                                border-radius:9px;
                                background:#ffffff;
                                color:#0f172a;
                            "
                        >

                    </div>


                    {{-- EMAIL --}}
                    <div style="margin-bottom:19px;">

                        <label
                            for="email"
                            style="
                                display:block;
                                margin-bottom:7px;
                                color:#334155;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            Email
                            <span style="color:#dc2626;">*</span>
                        </label>


                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="nama@email.com"
                            style="
                                width:100%;
                                padding:11px 13px;
                                border:1px solid #cbd5e1;
                                border-radius:9px;
                                background:#ffffff;
                                color:#0f172a;
                            "
                        >

                    </div>


                    {{-- ROLE --}}
                    <div style="margin-bottom:19px;">

                        <label
                            for="role"
                            style="
                                display:block;
                                margin-bottom:7px;
                                color:#334155;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            Role
                            <span style="color:#dc2626;">*</span>
                        </label>


                        <select
                            id="role"
                            name="role"
                            x-model="role"
                            required
                            style="
                                width:100%;
                                padding:11px 13px;
                                border:1px solid #cbd5e1;
                                border-radius:9px;
                                background:#ffffff;
                                color:#0f172a;
                            "
                        >

                            <option value="teknisi">
                                Teknisi
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                        </select>

                    </div>


                    {{-- PROFIL TEKNISI --}}
                    <div
                        x-show="role === 'teknisi'"
                        x-transition
                        style="
                            margin-bottom:19px;
                            padding:17px;
                            border:1px solid #a5f3fc;
                            border-radius:11px;
                            background:#ecfeff;
                        "
                    >

                        <label
                            for="teknisi_id"
                            style="
                                display:block;
                                margin-bottom:7px;
                                color:#155e75;
                                font-size:13px;
                                font-weight:800;
                            "
                        >
                            Profil Teknisi
                            <span style="color:#dc2626;">*</span>
                        </label>


                        <select
                            id="teknisi_id"
                            name="teknisi_id"
                            :required="role === 'teknisi'"
                            :disabled="role !== 'teknisi'"
                            style="
                                width:100%;
                                padding:11px 13px;
                                border:1px solid #67e8f9;
                                border-radius:9px;
                                background:#ffffff;
                                color:#0f172a;
                            "
                        >

                            <option value="">
                                -- Pilih Profil Teknisi --
                            </option>


                            @forelse ($teknisis as $teknisi)

                                <option
                                    value="{{ $teknisi->id }}"
                                    @selected(old('teknisi_id') == $teknisi->id)
                                >
                                    {{ $teknisi->nama }}
                                </option>

                            @empty

                                <option value="" disabled>
                                    Tidak ada profil Teknisi yang tersedia
                                </option>

                            @endforelse

                        </select>


                        <p style="
                            margin:8px 0 0;
                            color:#0e7490;
                            font-size:12px;
                            line-height:1.5;
                        ">
                            Hanya profil Teknisi yang belum memiliki akun yang ditampilkan.
                        </p>

                    </div>


                    {{-- INFO ADMIN --}}
                    <div
                        x-show="role === 'admin'"
                        x-transition
                        style="
                            margin-bottom:19px;
                            padding:14px 16px;
                            border:1px solid #bfdbfe;
                            border-radius:10px;
                            background:#eff6ff;
                            color:#1e40af;
                            font-size:12px;
                            line-height:1.6;
                        "
                    >
                        Akun Admin mempunyai akses ke fungsi administrasi sistem.
                        Pastikan akun Admin hanya diberikan kepada pengguna yang berwenang.
                    </div>


                    {{-- PASSWORD GRID --}}
                    <div style="
                        display:grid;
                        grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
                        gap:18px;
                        margin-bottom:24px;
                    ">

                        {{-- PASSWORD --}}
                        <div>

                            <label
                                for="password"
                                style="
                                    display:block;
                                    margin-bottom:7px;
                                    color:#334155;
                                    font-size:13px;
                                    font-weight:700;
                                "
                            >
                                Password
                                <span style="color:#dc2626;">*</span>
                            </label>


                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Minimal 8 karakter"
                                style="
                                    width:100%;
                                    padding:11px 13px;
                                    border:1px solid #cbd5e1;
                                    border-radius:9px;
                                    background:#ffffff;
                                    color:#0f172a;
                                "
                            >

                        </div>


                        {{-- PASSWORD CONFIRMATION --}}
                        <div>

                            <label
                                for="password_confirmation"
                                style="
                                    display:block;
                                    margin-bottom:7px;
                                    color:#334155;
                                    font-size:13px;
                                    font-weight:700;
                                "
                            >
                                Konfirmasi Password
                                <span style="color:#dc2626;">*</span>
                            </label>


                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Ulangi password"
                                style="
                                    width:100%;
                                    padding:11px 13px;
                                    border:1px solid #cbd5e1;
                                    border-radius:9px;
                                    background:#ffffff;
                                    color:#0f172a;
                                "
                            >

                        </div>

                    </div>


                    {{-- ACTION --}}
                    <div style="
                        display:flex;
                        align-items:center;
                        justify-content:flex-end;
                        gap:10px;
                        padding-top:20px;
                        border-top:1px solid #e2e8f0;
                    ">

                        <a
                            href="{{ route('users.index') }}"
                            style="
                                padding:10px 18px;
                                border:1px solid #cbd5e1;
                                border-radius:9px;
                                background:#f8fafc;
                                color:#475569;
                                text-decoration:none;
                                font-size:13px;
                                font-weight:700;
                            "
                        >
                            Batal
                        </a>


                        <button
                            type="submit"
                            style="
                                display:inline-flex;
                                align-items:center;
                                justify-content:center;
                                gap:7px;
                                padding:11px 20px;
                                border:0;
                                border-radius:9px;
                                background:#0891b2;
                                color:#ffffff;
                                cursor:pointer;
                                font-size:13px;
                                font-weight:800;
                                box-shadow:0 4px 10px rgba(8,145,178,.20);
                            "
                        >

                            <svg
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 5v14"/>
                                <path d="M5 12h14"/>
                            </svg>

                            Simpan User

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>