<x-app-layout>

    <div style="
        min-height:calc(100vh - 70px);
        background:#f8fafc;
        padding:32px 20px;
    ">

        <div style="
            max-width:1280px;
            margin:0 auto;
        ">

            {{-- =====================================================
                 HEADER
            ====================================================== --}}
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:20px;
                margin-bottom:24px;
                flex-wrap:wrap;
            ">

                <div>

                    <h1 style="
                        margin:0;
                        color:#0f172a;
                        font-size:30px;
                        font-weight:800;
                        letter-spacing:-0.5px;
                    ">
                        Management User
                    </h1>

                    <p style="
                        margin:7px 0 0;
                        color:#64748b;
                        font-size:14px;
                    ">
                        Kelola akun pengguna, hak akses, dan status akun sistem.
                    </p>

                </div>


                {{-- TAMBAH USER --}}
                <a
                    href="{{ route('users.create') }}"
                    style="
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:8px;

                        min-height:42px;
                        padding:0 18px;

                        border:1px solid #0e7490;
                        border-radius:9px;

                        background:linear-gradient(
                            135deg,
                            #0891b2,
                            #0e7490
                        );

                        color:#ffffff;
                        text-decoration:none;

                        font-size:13px;
                        font-weight:800;

                        box-shadow:0 5px 12px rgba(8,145,178,.20);
                    "
                >

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 5v14"/>
                        <path d="M5 12h14"/>
                    </svg>

                    Tambah User

                </a>

            </div>



            {{-- =====================================================
                 SUCCESS
            ====================================================== --}}
            @if(session('success'))

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;

                    margin-bottom:20px;
                    padding:13px 16px;

                    border:1px solid #86efac;
                    border-radius:10px;

                    background:#f0fdf4;
                    color:#166534;

                    font-size:13px;
                    font-weight:600;
                ">

                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>

                    {{ session('success') }}

                </div>

            @endif



            {{-- =====================================================
                 ERROR
            ====================================================== --}}
            @if(session('error'))

                <div style="
                    margin-bottom:20px;
                    padding:13px 16px;

                    border:1px solid #fca5a5;
                    border-radius:10px;

                    background:#fef2f2;
                    color:#991b1b;

                    font-size:13px;
                    font-weight:600;
                ">

                    {{ session('error') }}

                </div>

            @endif



            {{-- =====================================================
                 TABLE CARD
            ====================================================== --}}
            <div style="
                overflow:hidden;

                background:#ffffff;

                border:1px solid #e2e8f0;
                border-radius:14px;

                box-shadow:0 4px 16px rgba(15,23,42,.06);
            ">

                {{-- TABLE HEADER --}}
                <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:15px;

                    padding:16px 20px;

                    border-bottom:1px solid #e2e8f0;
                    background:#ffffff;
                ">

                    <div>

                        <div style="
                            color:#0f172a;
                            font-size:14px;
                            font-weight:800;
                        ">
                            Daftar User
                        </div>

                        <div style="
                            margin-top:3px;
                            color:#94a3b8;
                            font-size:11px;
                        ">
                            Total {{ $users->count() }} akun terdaftar
                        </div>

                    </div>

                </div>


                <div style="overflow-x:auto;">

                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        background:#ffffff;
                        color:#0f172a;
                    ">

                        <thead>

                            <tr style="
                                background:#f8fafc;
                                color:#475569;
                            ">

                                <th style="
                                    width:70px;
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:center;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    No
                                </th>


                                <th style="
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:left;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    Nama
                                </th>


                                <th style="
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:left;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    Email
                                </th>


                                <th style="
                                    width:140px;
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:center;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    Role
                                </th>


                                <th style="
                                    width:140px;
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:center;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    Status
                                </th>


                                <th style="
                                    width:280px;
                                    padding:14px 16px;
                                    border-bottom:1px solid #e2e8f0;
                                    text-align:center;
                                    font-size:12px;
                                    font-weight:800;
                                    text-transform:uppercase;
                                    letter-spacing:.04em;
                                ">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($users as $user)

                                <tr style="
                                    border-bottom:1px solid #f1f5f9;
                                ">

                                    {{-- NO --}}
                                    <td style="
                                        padding:15px 16px;
                                        text-align:center;
                                        color:#64748b;
                                        font-size:13px;
                                    ">

                                        {{ $loop->iteration }}

                                    </td>


                                    {{-- NAMA --}}
                                    <td style="
                                        padding:15px 16px;
                                    ">

                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                        ">

                                            <div style="
                                                width:34px;
                                                height:34px;

                                                display:flex;
                                                align-items:center;
                                                justify-content:center;

                                                flex-shrink:0;

                                                border-radius:999px;

                                                background:#ecfeff;
                                                color:#0e7490;

                                                font-size:13px;
                                                font-weight:800;
                                            ">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>


                                            <div style="
                                                color:#0f172a;
                                                font-size:14px;
                                                font-weight:700;
                                            ">

                                                {{ $user->name }}


                                                @if($user->id === auth()->id())

                                                    <span style="
                                                        margin-left:5px;
                                                        color:#0891b2;
                                                        font-size:11px;
                                                        font-weight:700;
                                                    ">
                                                        (Anda)
                                                    </span>

                                                @endif

                                            </div>

                                        </div>

                                    </td>


                                    {{-- EMAIL --}}
                                    <td style="
                                        padding:15px 16px;
                                        color:#475569;
                                        font-size:13px;
                                    ">

                                        {{ $user->email }}

                                    </td>


                                    {{-- ROLE --}}
                                    <td style="
                                        padding:15px 16px;
                                        text-align:center;
                                    ">

                                        @if($user->role === 'admin')

                                            <span style="
                                                display:inline-flex;
                                                align-items:center;
                                                justify-content:center;

                                                padding:6px 12px;

                                                border:1px solid #93c5fd;
                                                border-radius:999px;

                                                background:#eff6ff;
                                                color:#1d4ed8;

                                                font-size:11px;
                                                font-weight:800;
                                                letter-spacing:.04em;
                                            ">
                                                ADMIN
                                            </span>

                                        @else

                                            <span style="
                                                display:inline-flex;
                                                align-items:center;
                                                justify-content:center;

                                                padding:6px 12px;

                                                border:1px solid #86efac;
                                                border-radius:999px;

                                                background:#f0fdf4;
                                                color:#15803d;

                                                font-size:11px;
                                                font-weight:800;
                                                letter-spacing:.04em;
                                            ">
                                                TEKNISI
                                            </span>

                                        @endif

                                    </td>


                                    {{-- STATUS --}}
                                    <td style="
                                        padding:15px 16px;
                                        text-align:center;
                                    ">

                                        @if($user->is_active)

                                            <span style="
                                                display:inline-flex;
                                                align-items:center;
                                                justify-content:center;
                                                gap:6px;

                                                padding:6px 11px;

                                                border:1px solid #86efac;
                                                border-radius:999px;

                                                background:#f0fdf4;
                                                color:#15803d;

                                                font-size:11px;
                                                font-weight:800;
                                                letter-spacing:.04em;
                                            ">

                                                <span style="
                                                    width:7px;
                                                    height:7px;
                                                    border-radius:999px;
                                                    background:#22c55e;
                                                "></span>

                                                AKTIF

                                            </span>

                                        @else

                                            <span style="
                                                display:inline-flex;
                                                align-items:center;
                                                justify-content:center;
                                                gap:6px;

                                                padding:6px 11px;

                                                border:1px solid #fecaca;
                                                border-radius:999px;

                                                background:#fef2f2;
                                                color:#b91c1c;

                                                font-size:11px;
                                                font-weight:800;
                                                letter-spacing:.04em;
                                            ">

                                                <span style="
                                                    width:7px;
                                                    height:7px;
                                                    border-radius:999px;
                                                    background:#ef4444;
                                                "></span>

                                                NONAKTIF

                                            </span>

                                        @endif

                                    </td>


                                    {{-- AKSI --}}
                                    <td style="
                                        padding:15px 16px;
                                        text-align:center;
                                    ">

                                        @if($user->id !== auth()->id())

                                            <div style="
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                gap:8px;
                                                flex-wrap:wrap;
                                            ">

                                                {{-- EDIT USER --}}
                                                <a
                                                    href="{{ route('users.edit', $user->id) }}"
                                                    style="
                                                        display:inline-flex;
                                                        align-items:center;
                                                        justify-content:center;
                                                        gap:6px;

                                                        min-height:36px;
                                                        padding:8px 13px;

                                                        border:1px solid #fde68a;
                                                        border-radius:8px;

                                                        background:#fffbeb;
                                                        color:#b45309;

                                                        text-decoration:none;
                                                        font-size:12px;
                                                        font-weight:800;
                                                    "
                                                >

                                                    <svg
                                                        width="15"
                                                        height="15"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path d="M12 20h9"/>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                                    </svg>

                                                    Edit User

                                                </a>


                                                {{-- AKTIF / NONAKTIF --}}
                                                <form
                                                    method="POST"
                                                    action="{{ route('users.toggle-active', $user) }}"
                                                    style="margin:0;"
                                                    onsubmit="return confirm(
                                                        '{{ $user->is_active
                                                            ? 'Nonaktifkan akun '.$user->name.'? User tidak akan dapat menggunakan akun ini.'
                                                            : 'Aktifkan kembali akun '.$user->name.'?'
                                                        }}'
                                                    );"
                                                >

                                                    @csrf
                                                    @method('PATCH')


                                                    @if($user->is_active)

                                                        <button
                                                            type="submit"
                                                            style="
                                                                display:inline-flex;
                                                                align-items:center;
                                                                justify-content:center;
                                                                gap:6px;

                                                                min-height:36px;
                                                                padding:8px 13px;

                                                                border:1px solid #fecaca;
                                                                border-radius:8px;

                                                                background:#fef2f2;
                                                                color:#b91c1c;

                                                                cursor:pointer;
                                                                font-size:12px;
                                                                font-weight:800;
                                                            "
                                                        >

                                                            <svg
                                                                width="15"
                                                                height="15"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                            >
                                                                <circle cx="12" cy="12" r="9"/>
                                                                <path d="m9 9 6 6"/>
                                                                <path d="m15 9-6 6"/>
                                                            </svg>

                                                            Nonaktifkan

                                                        </button>

                                                    @else

                                                        <button
                                                            type="submit"
                                                            style="
                                                                display:inline-flex;
                                                                align-items:center;
                                                                justify-content:center;
                                                                gap:6px;

                                                                min-height:36px;
                                                                padding:8px 13px;

                                                                border:1px solid #86efac;
                                                                border-radius:8px;

                                                                background:#f0fdf4;
                                                                color:#15803d;

                                                                cursor:pointer;
                                                                font-size:12px;
                                                                font-weight:800;
                                                            "
                                                        >

                                                            <svg
                                                                width="15"
                                                                height="15"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                            >
                                                                <path d="M20 6 9 17l-5-5"/>
                                                            </svg>

                                                            Aktifkan

                                                        </button>

                                                    @endif

                                                </form>

                                            </div>

                                        @else

                                            <span style="
                                                display:inline-flex;
                                                align-items:center;
                                                gap:6px;

                                                padding:7px 10px;

                                                border:1px solid #e2e8f0;
                                                border-radius:8px;

                                                background:#f8fafc;
                                                color:#64748b;

                                                font-size:12px;
                                                font-weight:700;
                                            ">

                                                <svg
                                                    width="14"
                                                    height="14"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path d="M12 2a5 5 0 0 0-5 5v3"/>
                                                    <rect
                                                        x="5"
                                                        y="10"
                                                        width="14"
                                                        height="10"
                                                        rx="2"
                                                    />
                                                </svg>

                                                Akun Anda

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        style="
                                            padding:45px 20px;
                                            text-align:center;
                                            color:#94a3b8;
                                            font-size:13px;
                                        "
                                    >

                                        Belum ada user.

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