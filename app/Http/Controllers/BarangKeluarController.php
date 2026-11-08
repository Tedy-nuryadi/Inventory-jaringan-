<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Teknisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BarangKeluarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = auth()->user();

        $query = BarangKeluar::with([
            'barang',
            'teknisi'
        ])->latest();


        if ($user->role === 'teknisi') {

            $teknisi = $user->teknisi;

            if (!$teknisi) {

                abort(
                    403,
                    'Akun teknisi belum terhubung dengan data teknisi.'
                );
            }

            $query->where(
                'teknisi_id',
                $teknisi->id
            );
        }


        $barangKeluar = $query->get();


        return view(
            'barang_keluar.index',
            compact('barangKeluar')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $user = auth()->user();


        $barangs = Barang::orderBy(
            'nama_barang',
            'asc'
        )->get();


        if ($user->role === 'admin') {

            $teknisis = Teknisi::orderBy(
                'nama',
                'asc'
            )->get();

            $teknisiLogin = null;

        } else {

            $teknisiLogin = $user->teknisi;


            if (!$teknisiLogin) {

                abort(
                    403,
                    'Akun ini belum terhubung dengan data teknisi.'
                );
            }


            $teknisis = collect();
        }


        return view(
            'barang_keluar.create',
            compact(
                'barangs',
                'teknisis',
                'teknisiLogin'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $user = auth()->user();


        $rules = [

            'barang_id' =>
                'required|exists:barangs,id',

            'tanggal' =>
                'required|date',

            'jumlah' =>
                'required|integer|min:1',

            'keperluan' =>
                'required|string|max:255',

            'foto' =>
                'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];


        if ($user->role === 'admin') {

            $rules['teknisi_id'] =
                'required|exists:teknisis,id';
        }


        $validated =
            $request->validate($rules);


        /*
        |--------------------------------------------------------------------------
        | TENTUKAN TEKNISI
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            $teknisiId =
                $validated['teknisi_id'];

        } else {

            $teknisi =
                $user->teknisi;


            if (!$teknisi) {

                abort(
                    403,
                    'Akun teknisi belum terhubung.'
                );
            }


            $teknisiId =
                $teknisi->id;
        }


        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        $fotoPath = null;


        if ($request->hasFile('foto')) {

            $fotoPath =
                $request
                    ->file('foto')
                    ->store(
                        'barang_keluar',
                        'public'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI STORE
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use (
                $request,
                $validated,
                $teknisiId,
                $fotoPath,
                $user
            ) {

                $barang =
                    Barang::where(
                        'id',
                        $validated['barang_id']
                    )
                    ->lockForUpdate()
                    ->firstOrFail();


                if (
                    $barang->stok
                    <
                    $validated['jumlah']
                ) {

                    throw ValidationException::withMessages([

                        'jumlah' =>
                            'Stok tidak mencukupi. Stok tersedia: '
                            . $barang->stok

                    ]);
                }


                $stokSebelum =
                    $barang->stok;


                $barang->stok =
                    $barang->stok
                    -
                    $validated['jumlah'];

                $barang->save();


                $stokSesudah =
                    $barang->stok;


                $barangKeluar =
                    BarangKeluar::create([

                        'barang_id' =>
                            $validated['barang_id'],

                        'teknisi_id' =>
                            $teknisiId,

                        'tanggal' =>
                            $validated['tanggal'],

                        'jumlah' =>
                            $validated['jumlah'],

                        'pengguna' =>
                            $user->name,

                        'keperluan' =>
                            $validated['keperluan'],

                        'foto' =>
                            $fotoPath,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | AUDIT CREATE
                |--------------------------------------------------------------------------
                */

                ActivityLog::create([

                    'user_id' =>
                        $user->id,

                    'action' =>
                        'create',

                    'module' =>
                        'barang_keluar',

                    'subject_id' =>
                        $barangKeluar->id,

                    'description' =>
                        'Menambahkan barang keluar: '
                        . $barang->nama_barang
                        . ' sebanyak '
                        . $validated['jumlah']
                        . ' '
                        . $barang->satuan,

                    'old_values' =>
                        null,

                    'new_values' => [

                        'barang_id' =>
                            $barang->id,

                        'kode_barang' =>
                            $barang->kode_barang,

                        'nama_barang' =>
                            $barang->nama_barang,

                        'teknisi_id' =>
                            $teknisiId,

                        'tanggal' =>
                            $validated['tanggal'],

                        'jumlah' =>
                            $validated['jumlah'],

                        'pengguna' =>
                            $user->name,

                        'keperluan' =>
                            $validated['keperluan'],

                        'foto' =>
                            $fotoPath,

                        'stok_sebelum' =>
                            $stokSebelum,

                        'stok_sesudah' =>
                            $stokSesudah,
                    ],

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);
            });

        } catch (\Throwable $e) {

            if ($fotoPath) {

                Storage::disk('public')
                    ->delete($fotoPath);
            }


            throw $e;
        }


        return redirect()
            ->route('barang-keluar.index')
            ->with(
                'success',
                'Barang keluar berhasil dicatat.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        BarangKeluar $barangKeluar
    ) {

        $user =
            auth()->user();


        if ($user->role === 'teknisi') {

            $teknisi =
                $user->teknisi;


            if (
                !$teknisi
                ||
                $barangKeluar->teknisi_id
                !==
                $teknisi->id
            ) {

                abort(403);
            }
        }


        $barangs =
            Barang::orderBy(
                'nama_barang',
                'asc'
            )->get();


        if ($user->role === 'admin') {

            $teknisis =
                Teknisi::orderBy(
                    'nama',
                    'asc'
                )->get();

            $teknisiLogin = null;

        } else {

            $teknisis =
                collect();

            $teknisiLogin =
                $user->teknisi;
        }


        return view(
            'barang_keluar.edit',
            compact(
                'barangKeluar',
                'barangs',
                'teknisis',
                'teknisiLogin'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        BarangKeluar $barangKeluar
    ) {

        $user =
            auth()->user();


        if ($user->role === 'teknisi') {

            $teknisi =
                $user->teknisi;


            if (
                !$teknisi
                ||
                $barangKeluar->teknisi_id
                !==
                $teknisi->id
            ) {

                abort(403);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $rules = [

            'barang_id' =>
                'required|exists:barangs,id',

            'tanggal' =>
                'required|date',

            'jumlah' =>
                'required|integer|min:1',

            'keperluan' =>
                'required|string|max:255',

            'foto' =>
                'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];


        if ($user->role === 'admin') {

            $rules['teknisi_id'] =
                'required|exists:teknisis,id';
        }


        $validated =
            $request->validate($rules);


        /*
        |--------------------------------------------------------------------------
        | TENTUKAN TEKNISI
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            $teknisiId =
                $validated['teknisi_id'];

        } else {

            $teknisi =
                $user->teknisi;


            if (!$teknisi) {

                abort(
                    403,
                    'Akun teknisi belum terhubung.'
                );
            }


            $teknisiId =
                $teknisi->id;
        }


        /*
        |--------------------------------------------------------------------------
        | FOTO BARU
        |--------------------------------------------------------------------------
        */

        $fotoBaru = null;


        if ($request->hasFile('foto')) {

            $fotoBaru =
                $request
                    ->file('foto')
                    ->store(
                        'barang_keluar',
                        'public'
                    );
        }


        $fotoLama =
            $barangKeluar->foto;


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI UPDATE
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use (
                $request,
                $validated,
                $teknisiId,
                $barangKeluar,
                $fotoBaru,
                $fotoLama,
                $user
            ) {

                $oldBarangId =
                    $barangKeluar->barang_id;

                $oldJumlah =
                    $barangKeluar->jumlah;


                $barangLama =
                    Barang::where(
                        'id',
                        $oldBarangId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT LAMA
                |--------------------------------------------------------------------------
                */

                $oldValues = [

                    'barang_id' =>
                        $barangKeluar->barang_id,

                    'kode_barang' =>
                        $barangLama->kode_barang,

                    'nama_barang' =>
                        $barangLama->nama_barang,

                    'teknisi_id' =>
                        $barangKeluar->teknisi_id,

                    'tanggal' =>
                        $barangKeluar->tanggal,

                    'jumlah' =>
                        $barangKeluar->jumlah,

                    'pengguna' =>
                        $barangKeluar->pengguna,

                    'keperluan' =>
                        $barangKeluar->keperluan,

                    'foto' =>
                        $barangKeluar->foto,

                    'stok_barang' =>
                        $barangLama->stok,
                ];


                /*
                |--------------------------------------------------------------------------
                | BARANG TIDAK BERUBAH
                |--------------------------------------------------------------------------
                */

                if (
                    $oldBarangId
                    ==
                    $validated['barang_id']
                ) {

                    $stokTersedia =
                        $barangLama->stok
                        +
                        $oldJumlah;


                    if (
                        $stokTersedia
                        <
                        $validated['jumlah']
                    ) {

                        throw ValidationException::withMessages([

                            'jumlah' =>
                                'Stok tidak mencukupi. Stok tersedia: '
                                . $stokTersedia

                        ]);
                    }


                    $barangLama->stok =
                        $stokTersedia
                        -
                        $validated['jumlah'];

                    $barangLama->save();


                    $barangBaru =
                        $barangLama;
                }


                /*
                |--------------------------------------------------------------------------
                | BARANG DIGANTI
                |--------------------------------------------------------------------------
                */

                else {

                    $barangLama->stok =
                        $barangLama->stok
                        +
                        $oldJumlah;

                    $barangLama->save();


                    $barangBaru =
                        Barang::where(
                            'id',
                            $validated['barang_id']
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                    if (
                        $barangBaru->stok
                        <
                        $validated['jumlah']
                    ) {

                        throw ValidationException::withMessages([

                            'jumlah' =>
                                'Stok barang baru tidak mencukupi. Stok tersedia: '
                                . $barangBaru->stok

                        ]);
                    }


                    $barangBaru->stok =
                        $barangBaru->stok
                        -
                        $validated['jumlah'];

                    $barangBaru->save();
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $data = [

                    'barang_id' =>
                        $validated['barang_id'],

                    'teknisi_id' =>
                        $teknisiId,

                    'tanggal' =>
                        $validated['tanggal'],

                    'jumlah' =>
                        $validated['jumlah'],

                    'pengguna' =>
                        $user->name,

                    'keperluan' =>
                        $validated['keperluan'],
                ];


                if ($fotoBaru) {

                    $data['foto'] =
                        $fotoBaru;
                }


                $barangKeluar->update(
                    $data
                );


                $fotoFinal =
                    $fotoBaru
                    ??
                    $fotoLama;


                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT BARU
                |--------------------------------------------------------------------------
                */

                $newValues = [

                    'barang_id' =>
                        $barangBaru->id,

                    'kode_barang' =>
                        $barangBaru->kode_barang,

                    'nama_barang' =>
                        $barangBaru->nama_barang,

                    'teknisi_id' =>
                        $teknisiId,

                    'tanggal' =>
                        $validated['tanggal'],

                    'jumlah' =>
                        $validated['jumlah'],

                    'pengguna' =>
                        $user->name,

                    'keperluan' =>
                        $validated['keperluan'],

                    'foto' =>
                        $fotoFinal,

                    'stok_barang' =>
                        $barangBaru->stok,
                ];


                /*
                |--------------------------------------------------------------------------
                | AUDIT UPDATE
                |--------------------------------------------------------------------------
                */

                ActivityLog::create([

                    'user_id' =>
                        $user->id,

                    'action' =>
                        'update',

                    'module' =>
                        'barang_keluar',

                    'subject_id' =>
                        $barangKeluar->id,

                    'description' =>
                        'Memperbarui barang keluar: '
                        . $barangBaru->nama_barang,

                    'old_values' =>
                        $oldValues,

                    'new_values' =>
                        $newValues,

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        $request->userAgent(),
                ]);
            });

        } catch (\Throwable $e) {


            if ($fotoBaru) {

                Storage::disk('public')
                    ->delete($fotoBaru);
            }


            throw $e;
        }


        /*
        |--------------------------------------------------------------------------
        | HAPUS FOTO LAMA
        |--------------------------------------------------------------------------
        */

        if (
            $fotoBaru
            &&
            $fotoLama
        ) {

            Storage::disk('public')
                ->delete($fotoLama);
        }


        return redirect()
            ->route('barang-keluar.index')
            ->with(
                'success',
                'Data barang keluar berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        BarangKeluar $barangKeluar
    ) {

        $user =
            auth()->user();


        /*
        |--------------------------------------------------------------------------
        | KEAMANAN TEKNISI
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'teknisi') {

            $teknisi =
                $user->teknisi;


            if (
                !$teknisi
                ||
                $barangKeluar->teknisi_id
                !==
                $teknisi->id
            ) {

                abort(403);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        $foto =
            $barangKeluar->foto;


        /*
        |--------------------------------------------------------------------------
        | STOK + AUDIT + DELETE
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $barangKeluar,
            $user
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK BARANG
            |--------------------------------------------------------------------------
            */

            $barang =
                Barang::where(
                    'id',
                    $barangKeluar->barang_id
                )
                ->lockForUpdate()
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA SEBELUM DELETE
            |--------------------------------------------------------------------------
            */

            $barangKeluarId =
                $barangKeluar->id;


            $jumlahDihapus =
                $barangKeluar->jumlah;


            $stokSebelum =
                $barang->stok;


            $oldValues = [

                'barang_id' =>
                    $barangKeluar->barang_id,

                'kode_barang' =>
                    $barang->kode_barang,

                'nama_barang' =>
                    $barang->nama_barang,

                'teknisi_id' =>
                    $barangKeluar->teknisi_id,

                'tanggal' =>
                    $barangKeluar->tanggal,

                'jumlah' =>
                    $barangKeluar->jumlah,

                'pengguna' =>
                    $barangKeluar->pengguna,

                'keperluan' =>
                    $barangKeluar->keperluan,

                'foto' =>
                    $barangKeluar->foto,

                'stok_barang' =>
                    $stokSebelum,
            ];


            /*
            |--------------------------------------------------------------------------
            | KEMBALIKAN STOK
            |--------------------------------------------------------------------------
            */

            $barang->stok =
                $barang->stok
                +
                $jumlahDihapus;

            $barang->save();


            /*
            |--------------------------------------------------------------------------
            | AUDIT DELETE
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([

                'user_id' =>
                    $user->id,

                'action' =>
                    'delete',

                'module' =>
                    'barang_keluar',

                'subject_id' =>
                    $barangKeluarId,

                'description' =>
                    'Menghapus barang keluar: '
                    . $barang->nama_barang
                    . ' sebanyak '
                    . $jumlahDihapus
                    . ' '
                    . $barang->satuan,

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
            | HAPUS TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $barangKeluar->delete();
        });


        /*
        |--------------------------------------------------------------------------
        | HAPUS FOTO SETELAH DATABASE SUKSES
        |--------------------------------------------------------------------------
        */

        if ($foto) {

            Storage::disk('public')
                ->delete($foto);
        }


        return redirect()
            ->route('barang-keluar.index')
            ->with(
                'success',
                'Data barang keluar berhasil dihapus.'
            );
    }
}