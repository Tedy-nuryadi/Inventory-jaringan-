<x-app-layout>

    <div class="max-w-7xl mx-auto py-8 px-6">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Data Teknisi
                </h1>

                <p class="text-gray-500 mt-1">
                    Kelola data teknisi yang menggunakan inventaris jaringan.
                </p>
            </div>

            <a href="{{ route('teknisi.create') }}"
               style="
                    display:inline-block;
                    background:#2563eb;
                    color:#ffffff;
                    padding:10px 20px;
                    border-radius:8px;
                    text-decoration:none;
                    font-weight:600;
               ">

                + Tambah Teknisi

            </a>

        </div>


        {{-- SUCCESS --}}
        @if(session('success'))

            <div style="
                background:#dcfce7;
                border:1px solid #86efac;
                color:#166534;
                padding:12px 16px;
                border-radius:8px;
                margin-bottom:20px;
            ">

                {{ session('success') }}

            </div>

        @endif


        {{-- ERROR --}}
        @if(session('error'))

            <div style="
                background:#fee2e2;
                border:1px solid #fca5a5;
                color:#991b1b;
                padding:12px 16px;
                border-radius:8px;
                margin-bottom:20px;
            ">

                {{ session('error') }}

            </div>

        @endif


        {{-- TABLE --}}
        <div style="
            background:#ffffff;
            border-radius:10px;
            box-shadow:0 2px 10px rgba(0,0,0,0.10);
            overflow:hidden;
        ">

            <div style="overflow-x:auto;">

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    color:#111827;
                    background:#ffffff;
                ">

                    <thead style="
                        background:#f3f4f6;
                        color:#111827;
                    ">

                        <tr>

                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                font-weight:700;
                            ">
                                No
                            </th>

                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                font-weight:700;
                            ">
                                Nama Teknisi
                            </th>

                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                font-weight:700;
                            ">
                                No. Telepon
                            </th>

                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                font-weight:700;
                            ">
                                Alamat
                            </th>

                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                font-weight:700;
                            ">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($teknisi as $t)

                            <tr style="
                                background:#ffffff;
                            ">

                                {{-- NO --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    text-align:center;
                                    color:#111827;
                                ">

                                    {{ $loop->iteration }}

                                </td>


                                {{-- NAMA --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    color:#111827;
                                    font-weight:600;
                                ">

                                    {{ $t->nama }}

                                </td>


                                {{-- TELEPON --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    color:#111827;
                                ">

                                    {{ $t->telepon ?? '-' }}

                                </td>


                                {{-- ALAMAT --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    color:#111827;
                                ">

                                    {{ $t->alamat ?? '-' }}

                                </td>


                                {{-- AKSI --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    text-align:center;
                                    white-space:nowrap;
                                ">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('teknisi.edit', $t->id) }}"
                                        style="
                                            display:inline-block;
                                            background:#f59e0b;
                                            color:#ffffff;
                                            padding:9px 16px;
                                            border-radius:6px;
                                            text-decoration:none;
                                            font-weight:700;
                                            font-size:14px;
                                            border:1px solid #d97706;
                                            margin-right:5px;
                                        "
                                    >
                                        Edit
                                    </a>


                                    {{-- HAPUS --}}
                                    <form
                                        action="{{ route('teknisi.destroy', $t->id) }}"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Yakin ingin menghapus teknisi ini?')"
                                            style="
                                                background:#dc2626;
                                                color:#ffffff;
                                                padding:9px 16px;
                                                border-radius:6px;
                                                border:1px solid #b91c1c;
                                                font-weight:700;
                                                font-size:14px;
                                                cursor:pointer;
                                            "
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    style="
                                        border:1px solid #d1d5db;
                                        padding:30px;
                                        text-align:center;
                                        color:#6b7280;
                                    "
                                >

                                    Belum ada data teknisi.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-app-layout>