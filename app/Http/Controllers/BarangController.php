<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST BARANG
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Barang::with('kategori');


        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );


            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'kode_barang',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'nama_barang',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'merk',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'lokasi',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhereHas(
                        'kategori',
                        function ($kategoriQuery) use ($search) {

                            $kategoriQuery->where(
                                'nama_kategori',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                }
            );
        }


        $sort = $request->get(
            'sort',
            'terbaru'
        );


        switch ($sort) {

            case 'terlama':

                $query->orderBy(
                    'created_at',
                    'asc'
                );

                break;


            case 'nama_az':

                $query->orderBy(
                    'nama_barang',
                    'asc'
                );

                break;


            case 'nama_za':

                $query->orderBy(
                    'nama_barang',
                    'desc'
                );

                break;


            case 'stok_terbanyak':

                $query->orderBy(
                    'stok',
                    'desc'
                );

                break;


            case 'stok_tersedikit':

                $query->orderBy(
                    'stok',
                    'asc'
                );

                break;


            case 'kode_az':

                $query->orderBy(
                    'kode_barang',
                    'asc'
                );

                break;


            default:

                $query->latest();

                break;
        }


        $barangs = $query->get();


        return view(
            'barang.index',
            compact('barangs')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $kategoris = Kategori::orderBy(
            'nama_kategori',
            'asc'
        )->get();


        $kategori = $kategoris;


        return view(
            'barang.create',
            compact(
                'kategoris',
                'kategori'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN BARANG
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => [
                'required',
            ],

            'nama_barang' => [
                'required',
            ],

            'kategori_id' => [
                'required',
                'exists:kategoris,id',
            ],

            'stok' => [
                'required',
                'integer',
                'min:0',
            ],

            'stok_minimum' => [
                'required',
                'integer',
                'min:0',
            ],

            'satuan' => [
                'required',
            ],

            'merk' => [
                'nullable',
            ],

            'lokasi' => [
                'nullable',
            ],
        ]);


        $barang = Barang::create([
            'kode_barang' =>
                $validated['kode_barang'],

            'nama_barang' =>
                $validated['nama_barang'],

            'kategori_id' =>
                $validated['kategori_id'],

            'merk' =>
                $validated['merk'] ?? null,

            'stok' =>
                $validated['stok'],

            'satuan' =>
                $validated['satuan'],

            'lokasi' =>
                $validated['lokasi'] ?? null,

            'stok_minimum' =>
                $validated['stok_minimum'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | AUDIT CREATE
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([
            'user_id' =>
                auth()->id(),

            'action' =>
                'create',

            'module' =>
                'barang',

            'subject_id' =>
                $barang->id,

            'description' =>
                'Menambahkan barang: '
                . $barang->nama_barang,

            'old_values' =>
                null,

            'new_values' => [
                'kode_barang' =>
                    $barang->kode_barang,

                'nama_barang' =>
                    $barang->nama_barang,

                'kategori_id' =>
                    $barang->kategori_id,

                'merk' =>
                    $barang->merk,

                'stok' =>
                    $barang->stok,

                'stok_minimum' =>
                    $barang->stok_minimum,

                'satuan' =>
                    $barang->satuan,

                'lokasi' =>
                    $barang->lokasi,
            ],

            'ip_address' =>
                $request->ip(),

            'user_agent' =>
                $request->userAgent(),
        ]);


        return redirect()
            ->route('barang.index')
            ->with(
                'success',
                'Barang berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Barang $barang)
    {
        $kategoris = Kategori::orderBy(
            'nama_kategori',
            'asc'
        )->get();


        $kategori = $kategoris;


        return view(
            'barang.edit',
            compact(
                'barang',
                'kategoris',
                'kategori'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Barang $barang
    ) {
        $validated = $request->validate([
            'kode_barang' => [
                'required',
            ],

            'nama_barang' => [
                'required',
            ],

            'kategori_id' => [
                'required',
                'exists:kategoris,id',
            ],

            'stok' => [
                'required',
                'integer',
                'min:0',
            ],

            'stok_minimum' => [
                'required',
                'integer',
                'min:0',
            ],

            'satuan' => [
                'required',
            ],

            'merk' => [
                'nullable',
            ],

            'lokasi' => [
                'nullable',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT DATA LAMA
        |--------------------------------------------------------------------------
        */

        $oldValues = [
            'kode_barang' =>
                $barang->kode_barang,

            'nama_barang' =>
                $barang->nama_barang,

            'kategori_id' =>
                $barang->kategori_id,

            'merk' =>
                $barang->merk,

            'stok' =>
                $barang->stok,

            'stok_minimum' =>
                $barang->stok_minimum,

            'satuan' =>
                $barang->satuan,

            'lokasi' =>
                $barang->lokasi,
        ];


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $barang->update([
            'kode_barang' =>
                $validated['kode_barang'],

            'nama_barang' =>
                $validated['nama_barang'],

            'kategori_id' =>
                $validated['kategori_id'],

            'merk' =>
                $validated['merk'] ?? null,

            'stok' =>
                $validated['stok'],

            'satuan' =>
                $validated['satuan'],

            'lokasi' =>
                $validated['lokasi'] ?? null,

            'stok_minimum' =>
                $validated['stok_minimum'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT DATA BARU
        |--------------------------------------------------------------------------
        */

        $newValues = [
            'kode_barang' =>
                $barang->kode_barang,

            'nama_barang' =>
                $barang->nama_barang,

            'kategori_id' =>
                $barang->kategori_id,

            'merk' =>
                $barang->merk,

            'stok' =>
                $barang->stok,

            'stok_minimum' =>
                $barang->stok_minimum,

            'satuan' =>
                $barang->satuan,

            'lokasi' =>
                $barang->lokasi,
        ];


        /*
        |--------------------------------------------------------------------------
        | AUDIT UPDATE
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([
            'user_id' =>
                auth()->id(),

            'action' =>
                'update',

            'module' =>
                'barang',

            'subject_id' =>
                $barang->id,

            'description' =>
                'Memperbarui barang: '
                . $barang->nama_barang,

            'old_values' =>
                $oldValues,

            'new_values' =>
                $newValues,

            'ip_address' =>
                $request->ip(),

            'user_agent' =>
                $request->userAgent(),
        ]);


        return redirect()
            ->route('barang.index')
            ->with(
                'success',
                'Barang berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS BARANG
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Barang $barang
    ) {
        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT SEBELUM DIHAPUS
        |--------------------------------------------------------------------------
        */

        $oldValues = [
            'kode_barang' =>
                $barang->kode_barang,

            'nama_barang' =>
                $barang->nama_barang,

            'kategori_id' =>
                $barang->kategori_id,

            'merk' =>
                $barang->merk,

            'stok' =>
                $barang->stok,

            'stok_minimum' =>
                $barang->stok_minimum,

            'satuan' =>
                $barang->satuan,

            'lokasi' =>
                $barang->lokasi,
        ];


        $barangId =
            $barang->id;

        $barangNama =
            $barang->nama_barang;


        /*
        |--------------------------------------------------------------------------
        | DELETE + AUDIT DALAM SATU TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $barang,
                $barangId,
                $barangNama,
                $oldValues
            ) {

                ActivityLog::create([
                    'user_id' =>
                        auth()->id(),

                    'action' =>
                        'delete',

                    'module' =>
                        'barang',

                    'subject_id' =>
                        $barangId,

                    'description' =>
                        'Menghapus barang: '
                        . $barangNama,

                    'old_values' =>
                        $oldValues,

                    'new_values' =>
                        null,

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);


                $barang->delete();
            }
        );


        return redirect()
            ->route('barang.index')
            ->with(
                'success',
                'Barang berhasil dihapus.'
            );
    }
}