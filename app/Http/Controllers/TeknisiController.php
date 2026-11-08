<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Teknisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeknisiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $teknisi = Teknisi::latest()->get();

        return view(
            'teknisi.index',
            compact('teknisi')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('teknisi.create');
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' =>
                'required',

            'telepon' =>
                'nullable',

            'alamat' =>
                'nullable',
        ]);


        DB::transaction(
            function () use (
                $request,
                $validated
            ) {

                $teknisi = Teknisi::create([
                    'nama' =>
                        $validated['nama'],

                    'telepon' =>
                        $validated['telepon'] ?? null,

                    'alamat' =>
                        $validated['alamat'] ?? null,
                ]);


                ActivityLog::create([
                    'user_id' =>
                        auth()->id(),

                    'action' =>
                        'create',

                    'module' =>
                        'teknisi',

                    'subject_id' =>
                        $teknisi->id,

                    'description' =>
                        'Menambahkan teknisi: '
                        . $teknisi->nama,

                    'old_values' =>
                        null,

                    'new_values' => [
                        'nama' =>
                            $teknisi->nama,

                        'telepon' =>
                            $teknisi->telepon,

                        'alamat' =>
                            $teknisi->alamat,
                    ],

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);
            }
        );


        return redirect('/teknisi')
            ->with(
                'success',
                'Data teknisi berhasil ditambahkan'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Teknisi $teknisi)
    {
        return view(
            'teknisi.edit',
            compact('teknisi')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Teknisi $teknisi
    ) {

        $validated = $request->validate([
            'nama' =>
                'required',

            'telepon' =>
                'nullable',

            'alamat' =>
                'nullable',
        ]);


        DB::transaction(
            function () use (
                $request,
                $validated,
                $teknisi
            ) {

                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SEBELUM UPDATE
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'nama' =>
                        $teknisi->nama,

                    'telepon' =>
                        $teknisi->telepon,

                    'alamat' =>
                        $teknisi->alamat,
                ];


                /*
                |--------------------------------------------------------------------------
                | UPDATE TEKNISI
                |--------------------------------------------------------------------------
                */

                $teknisi->update([
                    'nama' =>
                        $validated['nama'],

                    'telepon' =>
                        $validated['telepon'] ?? null,

                    'alamat' =>
                        $validated['alamat'] ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SESUDAH UPDATE
                |--------------------------------------------------------------------------
                */

                $newValues = [
                    'nama' =>
                        $teknisi->nama,

                    'telepon' =>
                        $teknisi->telepon,

                    'alamat' =>
                        $teknisi->alamat,
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
                        'teknisi',

                    'subject_id' =>
                        $teknisi->id,

                    'description' =>
                        'Memperbarui data teknisi: '
                        . $teknisi->nama,

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


        return redirect('/teknisi')
            ->with(
                'success',
                'Data teknisi berhasil diperbarui'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Teknisi $teknisi
    ) {

        /*
        |--------------------------------------------------------------------------
        | GUARD 1: MASIH TERHUBUNG USER
        |--------------------------------------------------------------------------
        */

        if (!is_null($teknisi->user_id)) {

            return redirect('/teknisi')
                ->with(
                    'error',
                    'Teknisi "' .
                    $teknisi->nama .
                    '" tidak dapat dihapus karena masih terhubung dengan akun pengguna.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GUARD 2: MASIH MEMILIKI RIWAYAT BARANG KELUAR
        |--------------------------------------------------------------------------
        |
        | Jangan hapus teknisi yang sudah dipakai dalam transaksi.
        | Kalau teknisi dihapus, teknisi_id pada riwayat barang keluar
        | akan menjadi NULL karena foreign key menggunakan nullOnDelete().
        |
        */

        if ($teknisi->barangKeluar()->exists()) {

            return redirect('/teknisi')
                ->with(
                    'error',
                    'Teknisi "' .
                    $teknisi->nama .
                    '" tidak dapat dihapus karena sudah memiliki riwayat barang keluar.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN ID SEBELUM DELETE
        |--------------------------------------------------------------------------
        */

        $teknisiId =
            $teknisi->id;


        /*
        |--------------------------------------------------------------------------
        | DELETE + AUDIT
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $teknisi,
                $teknisiId
            ) {

                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT SEBELUM DELETE
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'nama' =>
                        $teknisi->nama,

                    'telepon' =>
                        $teknisi->telepon,

                    'alamat' =>
                        $teknisi->alamat,
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
                        'teknisi',

                    'subject_id' =>
                        $teknisiId,

                    'description' =>
                        'Menghapus teknisi: '
                        . $teknisi->nama,

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
                | HAPUS TEKNISI
                |--------------------------------------------------------------------------
                */

                $teknisi->delete();
            }
        );


        return redirect('/teknisi')
            ->with(
                'success',
                'Data teknisi berhasil dihapus'
            );
    }
}