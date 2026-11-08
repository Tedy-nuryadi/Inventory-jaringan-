<x-app-layout>

<div class="max-w-7xl mx-auto py-8 px-6">


    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">


        <h1 style="font-size:30px;font-weight:bold;">
            Data Barang Masuk
        </h1>


        <a href="{{ route('barang-masuk.create') }}"
        style="background:#2563eb;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;">

            + Tambah Barang Masuk

        </a>


    </div>




    @if(session('success'))

    <div style="background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:20px;">

        {{ session('success') }}

    </div>

    @endif





    <div style="background:white;border-radius:10px;box-shadow:0 2px 10px #ddd;overflow:hidden;">


        <table style="width:100%;border-collapse:collapse;">


            <thead style="background:#f3f4f6;">


                <tr>


                    <th style="border:1px solid #ddd;padding:12px;">
                        No
                    </th>


                    <th style="border:1px solid #ddd;padding:12px;">
                        Barang
                    </th>


                    <th style="border:1px solid #ddd;padding:12px;">
                        Tanggal
                    </th>


                    <th style="border:1px solid #ddd;padding:12px;">
                        Jumlah
                    </th>


                    <th style="border:1px solid #ddd;padding:12px;">
                        Supplier
                    </th>


                    <th style="border:1px solid #ddd;padding:12px;">
                        Aksi
                    </th>


                </tr>


            </thead>





            <tbody>


            @foreach($barangMasuk as $bm)


            <tr>


                <td style="border:1px solid #ddd;padding:12px;text-align:center;">

                    {{ $loop->iteration }}

                </td>




                <td style="border:1px solid #ddd;padding:12px;">

                    {{ $bm->barang->nama_barang ?? '-' }}

                </td>




                <td style="border:1px solid #ddd;padding:12px;text-align:center;">

                    {{ $bm->tanggal }}

                </td>




                <td style="border:1px solid #ddd;padding:12px;text-align:center;">

                    {{ $bm->jumlah }}

                </td>




                <td style="border:1px solid #ddd;padding:12px;">

                    {{ $bm->supplier ?? '-' }}

                </td>





                <td style="border:1px solid #ddd;padding:12px;text-align:center;">



                    <a href="{{ route('barang-masuk.edit',$bm->id) }}"
                    style="
                    background:#eab308;
                    color:white;
                    padding:8px 15px;
                    border-radius:6px;
                    text-decoration:none;
                    display:inline-block;
                    margin-right:5px;
                    ">

                        EDIT

                    </a>





                    <form action="{{ route('barang-masuk.destroy',$bm->id) }}"
                    method="POST"
                    style="display:inline;">


                        @csrf

                        @method('DELETE')



                        <button
                        type="submit"
                        onclick="return confirm('Yakin hapus data ini?')"
                        style="
                        background:#dc2626;
                        color:white;
                        padding:8px 15px;
                        border:none;
                        border-radius:6px;
                        cursor:pointer;
                        ">

                            HAPUS

                        </button>



                    </form>



                </td>



            </tr>


            @endforeach



            </tbody>


        </table>


    </div>


</div>


</x-app-layout>