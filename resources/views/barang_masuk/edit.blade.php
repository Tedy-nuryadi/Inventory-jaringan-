<x-app-layout>

<div class="max-w-3xl mx-auto py-8 px-6">


<h1 class="text-3xl font-bold mb-6">
    Edit Barang Masuk
</h1>



<div class="bg-white shadow rounded-lg p-6">


<form action="{{ route('barang-masuk.update',$barangMasuk->id) }}"
method="POST">


@csrf

@method('PUT')



<div class="mb-4">

<label class="block font-semibold mb-2">
Barang
</label>


<select name="barang_id"
class="w-full border rounded-lg px-3 py-2">


@foreach($barang as $b)


<option value="{{ $b->id }}"
@if($b->id == $barangMasuk->barang_id)
selected
@endif
>

{{ $b->nama_barang }}

</option>


@endforeach


</select>


</div>





<div class="mb-4">

<label class="block font-semibold mb-2">
Tanggal
</label>


<input type="date"
name="tanggal"
value="{{ $barangMasuk->tanggal }}"
class="w-full border rounded-lg px-3 py-2">


</div>





<div class="mb-4">

<label class="block font-semibold mb-2">
Jumlah
</label>


<input type="number"
name="jumlah"
value="{{ $barangMasuk->jumlah }}"
class="w-full border rounded-lg px-3 py-2">


</div>





<div class="mb-4">

<label class="block font-semibold mb-2">
Supplier
</label>


<input type="text"
name="supplier"
value="{{ $barangMasuk->supplier }}"
class="w-full border rounded-lg px-3 py-2">


</div>





<div class="mb-4">

<label class="block font-semibold mb-2">
Keterangan
</label>


<textarea
name="keterangan"
rows="3"
class="w-full border rounded-lg px-3 py-2">{{ $barangMasuk->keterangan }}</textarea>


</div>





<button
type="submit"
class="bg-blue-600 text-white px-5 py-2 rounded-lg">

Update

</button>



<a href="{{ route('barang-masuk.index') }}"
class="bg-gray-500 text-white px-5 py-2 rounded-lg ml-2">

Kembali

</a>



</form>


</div>


</div>


</x-app-layout>