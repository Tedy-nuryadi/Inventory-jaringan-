<x-app-layout>

    <div class="max-w-3xl mx-auto py-8 px-6">

        {{-- HEADER --}}
        <div class="mb-6">

            <h1 class="text-3xl font-bold text-gray-800">
                Edit Teknisi
            </h1>

            <p class="text-gray-500 mt-1">
                Perbarui informasi data teknisi.
            </p>

        </div>


        {{-- ERROR VALIDATION --}}
        @if ($errors->any())

            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5">

                <div class="font-semibold mb-1">
                    Terdapat kesalahan:
                </div>

                <ul class="list-disc ml-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- FORM --}}
        <div class="bg-white shadow rounded-xl p-6">

            <form
                action="{{ route('teknisi.update', $teknisi->id) }}"
                method="POST">

                @csrf

                @method('PUT')


                {{-- NAMA --}}
                <div class="mb-5">

                    <label
                        for="nama"
                        class="block text-sm font-semibold text-gray-700 mb-2">

                        Nama Teknisi
                        <span class="text-red-500">*</span>

                    </label>


                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama', $teknisi->nama) }}"
                        placeholder="Masukkan nama teknisi"
                        required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                </div>


                {{-- TELEPON --}}
                <div class="mb-5">

                    <label
                        for="telepon"
                        class="block text-sm font-semibold text-gray-700 mb-2">

                        Nomor Telepon

                    </label>


                    <input
                        type="text"
                        id="telepon"
                        name="telepon"
                        value="{{ old('telepon', $teknisi->telepon) }}"
                        placeholder="Contoh: 081234567890"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                </div>


                {{-- ALAMAT --}}
                <div class="mb-6">

                    <label
                        for="alamat"
                        class="block text-sm font-semibold text-gray-700 mb-2">

                        Alamat

                    </label>


                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="4"
                        placeholder="Masukkan alamat teknisi"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat', $teknisi->alamat) }}</textarea>

                </div>


                {{-- BUTTON --}}
                <div class="flex items-center">

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium">

                        Simpan Perubahan

                    </button>


                    <a
                        href="{{ route('teknisi.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg font-medium ml-3">

                        Kembali

                    </a>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>