<x-app-layout>

    <div class="max-w-7xl mx-auto py-8 px-6">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-6">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Data Barang Keluar
                </h1>

                <p class="text-gray-500 mt-1">
                    Riwayat pengambilan barang oleh teknisi.
                </p>
            </div>


            <a href="{{ route('barang-keluar.create') }}"
               style="
                    display:inline-block;
                    background:#2563eb;
                    color:#ffffff;
                    padding:10px 20px;
                    border-radius:8px;
                    text-decoration:none;
                    font-weight:600;
               ">

                + Tambah Barang Keluar

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


        {{-- VALIDATION ERROR --}}
        @if ($errors->any())

            <div style="
                background:#fee2e2;
                border:1px solid #fca5a5;
                color:#991b1b;
                padding:12px 16px;
                border-radius:8px;
                margin-bottom:20px;
            ">

                <strong>Terjadi kesalahan:</strong>

                <ul style="margin-top:8px; padding-left:20px;">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

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
                    background:#ffffff;
                    color:#111827;
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
                                white-space:nowrap;
                            ">
                                No
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                white-space:nowrap;
                            ">
                                Barang
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                white-space:nowrap;
                            ">
                                Teknisi
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                white-space:nowrap;
                            ">
                                Tanggal
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                white-space:nowrap;
                            ">
                                Jumlah
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:left;
                                white-space:nowrap;
                            ">
                                Keperluan
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                white-space:nowrap;
                            ">
                                Foto
                            </th>


                            <th style="
                                border:1px solid #d1d5db;
                                padding:13px;
                                text-align:center;
                                white-space:nowrap;
                            ">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($barangKeluar as $bk)

                            <tr style="
                                background:#ffffff;
                            ">


                                {{-- NO --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    text-align:center;
                                ">

                                    {{ $loop->iteration }}

                                </td>


                                {{-- BARANG --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    font-weight:600;
                                ">

                                    {{ $bk->barang->nama_barang ?? '-' }}

                                </td>


                                {{-- TEKNISI --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                ">

                                    @if($bk->teknisi)

                                        <div style="
                                            font-weight:600;
                                            color:#111827;
                                        ">

                                            {{ $bk->teknisi->nama }}

                                        </div>


                                        @if($bk->teknisi->telepon)

                                            <div style="
                                                font-size:12px;
                                                color:#6b7280;
                                                margin-top:3px;
                                            ">

                                                {{ $bk->teknisi->telepon }}

                                            </div>

                                        @endif

                                    @else

                                        <span style="color:#9ca3af;">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- TANGGAL --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    text-align:center;
                                    white-space:nowrap;
                                ">

                                    {{ $bk->tanggal ? $bk->tanggal->format('d/m/Y') : '-' }}

                                </td>


                                {{-- JUMLAH --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    text-align:center;
                                    font-weight:700;
                                ">

                                    {{ $bk->jumlah }}

                                </td>


                                {{-- KEPERLUAN --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:13px;
                                    min-width:220px;
                                ">

                                    {{ $bk->keperluan }}

                                </td>


                                {{-- FOTO --}}
                                <td style="
                                    border:1px solid #d1d5db;
                                    padding:10px;
                                    text-align:center;
                                ">

                                    @if($bk->foto)

                                        <a
                                            href="{{ asset('storage/' . $bk->foto) }}"
                                            target="_blank"
                                            title="Lihat foto">

                                            <img
                                                src="{{ asset('storage/' . $bk->foto) }}"
                                                alt="Foto pengambilan"
                                                style="
                                                    width:70px;
                                                    height:70px;
                                                    object-fit:cover;
                                                    border-radius:8px;
                                                    border:1px solid #d1d5db;
                                                    display:block;
                                                    margin:auto;
                                                "
                                            >

                                        </a>

                                        <div style="
                                            font-size:11px;
                                            color:#6b7280;
                                            margin-top:5px;
                                        ">
                                            Klik foto
                                        </div>

                                    @else

                                        <span style="
                                            color:#9ca3af;
                                            font-size:13px;
                                        ">
                                            Tidak ada foto
                                        </span>

                                    @endif

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
                                        href="{{ route('barang-keluar.edit', $bk->id) }}"
                                        style="
                                            display:inline-block;
                                            background:#f59e0b;
                                            color:#ffffff;
                                            padding:9px 15px;
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
                                        action="{{ route('barang-keluar.destroy', $bk->id) }}"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Yakin ingin menghapus transaksi barang keluar ini?')"
                                            style="
                                                background:#dc2626;
                                                color:#ffffff;
                                                padding:9px 15px;
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
                                    colspan="8"
                                    style="
                                        border:1px solid #d1d5db;
                                        padding:35px;
                                        text-align:center;
                                        color:#6b7280;
                                    "
                                >

                                    Belum ada data barang keluar.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-app-layout>