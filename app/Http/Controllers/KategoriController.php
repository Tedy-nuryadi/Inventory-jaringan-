<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $kategoris = Kategori::withCount('barangs')
            ->orderBy('nama_kategori', 'asc')
            ->get();

        return view(
            'kategori.index',
            compact('kategoris')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('kategori.create');
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                'unique:kategoris,nama_kategori',
            ],
        ]);


        $namaKategori =
            trim(
                $validated['nama_kategori']
            );


        DB::transaction(
            function () use (
                $request,
                $namaKategori
            ) {

                /*
                |--------------------------------------------------------------------------
                | SIMPAN KATEGORI
                |--------------------------------------------------------------------------
                */

                $kategori = Kategori::create([
                    'nama_kategori' =>
                        $namaKategori,
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
                        'kategori',

                    'subject_id' =>
                        $kategori->id,

                    'description' =>
                        'Menambahkan kategori: '
                        . $kategori->nama_kategori,

                    'old_values' =>
                        null,

                    'new_values' => [
                        'nama_kategori' =>
                            $kategori->nama_kategori,
                    ],

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);
            }
        );


        return redirect()
            ->route('kategori.index')
            ->with(
                'success',
                'Kategori berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Kategori $kategori)
    {
        return view(
            'kategori.edit',
            compact('kategori')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Kategori $kategori
    ) {

        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'kategoris',
                    'nama_kategori'
                )->ignore($kategori->id),
            ],
        ]);


        $namaKategoriBaru =
            trim(
                $validated['nama_kategori']
            );


        DB::transaction(
            function () use (
                $request,
                $kategori,
                $namaKategoriBaru
            ) {

                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SEBELUM UPDATE
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'nama_kategori' =>
                        $kategori->nama_kategori,
                ];


                /*
                |--------------------------------------------------------------------------
                | UPDATE KATEGORI
                |--------------------------------------------------------------------------
                */

                $kategori->update([
                    'nama_kategori' =>
                        $namaKategoriBaru,
                ]);


                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SESUDAH UPDATE
                |--------------------------------------------------------------------------
                */

                $newValues = [
                    'nama_kategori' =>
                        $kategori->nama_kategori,
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
                        'kategori',

                    'subject_id' =>
                        $kategori->id,

                    'description' =>
                        'Memperbarui kategori dari "'
                        . $oldValues['nama_kategori']
                        . '" menjadi "'
                        . $newValues['nama_kategori']
                        . '"',

                    'old_values' =>
                        $oldValues,

                    'new_values' =>
                        $newValues,

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);
            }
        );


        return redirect()
            ->route('kategori.index')
            ->with(
                'success',
                'Kategori berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Kategori $kategori
    ) {

        /*
        |--------------------------------------------------------------------------
        | CEK APAKAH KATEGORI MASIH DIPAKAI BARANG
        |--------------------------------------------------------------------------
        */

        if ($kategori->barangs()->exists()) {

            return redirect()
                ->route('kategori.index')
                ->with(
                    'error',
                    'Kategori "' .
                    $kategori->nama_kategori .
                    '" tidak dapat dihapus karena masih digunakan oleh barang.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN ID DAN NAMA SEBELUM DELETE
        |--------------------------------------------------------------------------
        */

        $kategoriId =
            $kategori->id;

        $namaKategori =
            $kategori->nama_kategori;


        /*
        |--------------------------------------------------------------------------
        | DELETE + AUDIT
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $kategori,
                $kategoriId,
                $namaKategori
            ) {

                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SEBELUM DELETE
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'nama_kategori' =>
                        $namaKategori,
                ];


                /*
                |--------------------------------------------------------------------------
                | AUDIT DELETE
                |--------------------------------------------------------------------------
                */

                ActivityLog::create([
                    'user_id' =>
                        auth()->id(),

                    'action' =>
                        'delete',

                    'module' =>
                        'kategori',

                    'subject_id' =>
                        $kategoriId,

                    'description' =>
                        'Menghapus kategori: '
                        . $namaKategori,

                    'old_values' =>
                        $oldValues,

                    'new_values' =>
                        null,

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | HAPUS KATEGORI
                |--------------------------------------------------------------------------
                */

                $kategori->delete();
            }
        );


        return redirect()
            ->route('kategori.index')
            ->with(
                'success',
                'Kategori "' .
                $namaKategori .
                '" berhasil dihapus.'
            );
    }
}