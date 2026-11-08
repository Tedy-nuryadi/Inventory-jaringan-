<x-app-layout>

    <div class="max-w-3xl mx-auto py-8 px-6">

        <h1 class="text-3xl font-bold mb-6">
            Tambah Barang Masuk
        </h1>


        {{-- ERROR VALIDASI --}}
        @if ($errors->any())

            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">

                <strong>Data belum dapat disimpan.</strong>

                <ul class="mt-2 list-disc pl-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="bg-white shadow rounded-lg p-6">

            <form
                action="{{ route('barang-masuk.store') }}"
                method="POST"
            >

                @csrf


                {{-- BARANG --}}
                <div class="mb-4">

                    <label
                        for="barang_id"
                        class="block font-semibold mb-2"
                    >
                        Barang
                    </label>


                    <select
                        id="barang_id"
                        name="barang_id"
                        required
                        class="w-full border rounded-lg px-3 py-2"
                    >

                        <option value="">
                            -- Pilih Barang --
                        </option>


                        @foreach($barangs as $b)

                            <option
                                value="{{ $b->id }}"
                                @selected(old('barang_id') == $b->id)
                            >
                                {{ $b->kode_barang }}
                                -
                                {{ $b->nama_barang }}
                                (Stok: {{ $b->stok }})
                            </option>

                        @endforeach

                    </select>

                </div>



                {{-- TANGGAL --}}
                <div class="mb-4">

                    <label
                        for="tanggal"
                        class="block font-semibold mb-2"
                    >
                        Tanggal
                    </label>


                    <input
                        type="date"
                        id="tanggal"
                        name="tanggal"
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        required
                        class="w-full border rounded-lg px-3 py-2"
                    >

                </div>



                {{-- JUMLAH --}}
                <div class="mb-4">

                    <label
                        for="jumlah"
                        class="block font-semibold mb-2"
                    >
                        Jumlah
                    </label>


                    <input
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        value="{{ old('jumlah') }}"
                        min="1"
                        required
                        class="w-full border rounded-lg px-3 py-2"
                    >

                </div>



                {{-- SUPPLIER --}}
                <div class="mb-4">

                    <label
                        for="supplier"
                        class="block font-semibold mb-2"
                    >
                        Supplier
                    </label>


                    <input
                        type="text"
                        id="supplier"
                        name="supplier"
                        value="{{ old('supplier') }}"
                        class="w-full border rounded-lg px-3 py-2"
                    >

                </div>



                {{-- KETERANGAN --}}
                <div class="mb-6">

                    <label
                        for="keterangan"
                        class="block font-semibold mb-2"
                    >
                        Keterangan
                    </label>


                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="3"
                        class="w-full border rounded-lg px-3 py-2"
                    >{{ old('keterangan') }}</textarea>

                </div>



                {{-- BUTTON --}}
                <div class="flex items-center gap-3">

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold"
                    >
                        Simpan
                    </button>


                    <a
                        href="{{ route('barang-masuk.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>