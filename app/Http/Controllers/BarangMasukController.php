<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BarangMasukController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $barangMasuks = BarangMasuk::with('barang')
            ->latest()
            ->get();

        /*
        | Kompatibilitas jika view lama
        | masih menggunakan $barangMasuk.
        */
        $barangMasuk = $barangMasuks;

        return view(
            'barang_masuk.index',
            compact(
                'barangMasuks',
                'barangMasuk'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $barangs = Barang::orderBy(
            'nama_barang',
            'asc'
        )->get();

        return view(
            'barang_masuk.create',
            compact('barangs')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => [
                'required',
                'integer',
                'exists:barangs,id',
            ],

            'tanggal' => [
                'required',
                'date',
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:255',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        DB::transaction(function () use (
            $validated,
            $request
        ) {
            /*
            |--------------------------------------------------------------------------
            | LOCK BARANG
            |--------------------------------------------------------------------------
            */

            $barang = Barang::where(
                'id',
                $validated['barang_id']
            )
                ->lockForUpdate()
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT STOK SEBELUM
            |--------------------------------------------------------------------------
            */

            $stokSebelum =
                (int) $barang->stok;


            /*
            |--------------------------------------------------------------------------
            | HITUNG STOK SESUDAH
            |--------------------------------------------------------------------------
            */

            $stokSesudah =
                $stokSebelum
                +
                (int) $validated['jumlah'];


            /*
            |--------------------------------------------------------------------------
            | UPDATE STOK
            |--------------------------------------------------------------------------
            */

            $barang->stok =
                $stokSesudah;

            $barang->save();


            /*
            |--------------------------------------------------------------------------
            | SIMPAN TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $barangMasuk = BarangMasuk::create([
                'barang_id' =>
                    $validated['barang_id'],

                'tanggal' =>
                    $validated['tanggal'],

                'jumlah' =>
                    $validated['jumlah'],

                'supplier' =>
                    $validated['supplier'] ?? null,

                'keterangan' =>
                    $validated['keterangan'] ?? null,
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
                    'barang_masuk',

                'subject_id' =>
                    $barangMasuk->id,

                'description' =>
                    'Menambahkan transaksi barang masuk.',

                'old_values' =>
                    null,

                'new_values' => [
                    'barang_id' =>
                        (int) $barangMasuk->barang_id,

                    'kode_barang' =>
                        $barang->kode_barang,

                    'nama_barang' =>
                        $barang->nama_barang,

                    'tanggal' =>
                        $barangMasuk->getRawOriginal('tanggal')
                        ?? $barangMasuk->tanggal,

                    'jumlah' =>
                        (int) $barangMasuk->jumlah,

                    'supplier' =>
                        $barangMasuk->supplier,

                    'keterangan' =>
                        $barangMasuk->keterangan,

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


        return redirect()
            ->route('barang-masuk.index')
            ->with(
                'success',
                'Barang masuk berhasil dicatat.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        BarangMasuk $barangMasuk
    ) {
        return redirect()
            ->route(
                'barang-masuk.edit',
                $barangMasuk
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        BarangMasuk $barangMasuk
    ) {
        $barangs = Barang::orderBy(
            'nama_barang',
            'asc'
        )->get();

        return view(
            'barang_masuk.edit',
            compact(
                'barangMasuk',
                'barangs'
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
        BarangMasuk $barangMasuk
    ) {
        $validated = $request->validate([
            'barang_id' => [
                'required',
                'integer',
                'exists:barangs,id',
            ],

            'tanggal' => [
                'required',
                'date',
            ],

            'jumlah' => [
                'required',
                'integer',
                'min:1',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:255',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        DB::transaction(function () use (
            $validated,
            $barangMasuk,
            $request
        ) {
            /*
            |--------------------------------------------------------------------------
            | DATA TRANSAKSI LAMA
            |--------------------------------------------------------------------------
            */

            $barangLamaId =
                (int) $barangMasuk->barang_id;

            $barangBaruId =
                (int) $validated['barang_id'];

            $jumlahLama =
                (int) $barangMasuk->jumlah;

            $jumlahBaru =
                (int) $validated['jumlah'];


            /*
            |--------------------------------------------------------------------------
            | BARANG TIDAK BERUBAH
            |--------------------------------------------------------------------------
            */

            if ($barangLamaId === $barangBaruId) {

                /*
                |--------------------------------------------------------------------------
                | LOCK BARANG
                |--------------------------------------------------------------------------
                */

                $barang = Barang::where(
                    'id',
                    $barangLamaId
                )
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | STOK SEBELUM
                |--------------------------------------------------------------------------
                */

                $stokSebelum =
                    (int) $barang->stok;


                /*
                |--------------------------------------------------------------------------
                | OLD VALUES
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'barang_id' =>
                        (int) $barangMasuk->barang_id,

                    'kode_barang' =>
                        $barang->kode_barang,

                    'nama_barang' =>
                        $barang->nama_barang,

                    'tanggal' =>
                        $barangMasuk->getRawOriginal('tanggal')
                        ?? $barangMasuk->tanggal,

                    'jumlah' =>
                        (int) $barangMasuk->jumlah,

                    'supplier' =>
                        $barangMasuk->supplier,

                    'keterangan' =>
                        $barangMasuk->keterangan,

                    'stok_barang' =>
                        $stokSebelum,
                ];


                /*
                |--------------------------------------------------------------------------
                | HITUNG STOK BARU
                |--------------------------------------------------------------------------
                |
                | stok baru =
                |
                | stok sekarang
                | - jumlah transaksi lama
                | + jumlah transaksi baru
                |
                */

                $stokSesudah =
                    $stokSebelum
                    -
                    $jumlahLama
                    +
                    $jumlahBaru;


                /*
                |--------------------------------------------------------------------------
                | PROTEKSI STOK NEGATIF
                |--------------------------------------------------------------------------
                */

                if ($stokSesudah < 0) {

                    throw ValidationException::withMessages([
                        'jumlah' =>
                            'Koreksi jumlah barang masuk tidak dapat dilakukan karena stok yang tersedia sudah terpakai. '
                            . 'Stok saat ini: '
                            . $barang->stok
                            . ', jumlah transaksi lama: '
                            . $jumlahLama
                            . '.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE STOK
                |--------------------------------------------------------------------------
                */

                $barang->stok =
                    $stokSesudah;

                $barang->save();


                /*
                |--------------------------------------------------------------------------
                | UPDATE TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $barangMasuk->update([
                    'barang_id' =>
                        $barangBaruId,

                    'tanggal' =>
                        $validated['tanggal'],

                    'jumlah' =>
                        $jumlahBaru,

                    'supplier' =>
                        $validated['supplier'] ?? null,

                    'keterangan' =>
                        $validated['keterangan'] ?? null,
                ]);


                $barangMasuk->refresh();


                /*
                |--------------------------------------------------------------------------
                | NEW VALUES
                |--------------------------------------------------------------------------
                */

                $newValues = [
                    'barang_id' =>
                        (int) $barangMasuk->barang_id,

                    'kode_barang' =>
                        $barang->kode_barang,

                    'nama_barang' =>
                        $barang->nama_barang,

                    'tanggal' =>
                        $barangMasuk->getRawOriginal('tanggal')
                        ?? $barangMasuk->tanggal,

                    'jumlah' =>
                        (int) $barangMasuk->jumlah,

                    'supplier' =>
                        $barangMasuk->supplier,

                    'keterangan' =>
                        $barangMasuk->keterangan,

                    'stok_barang' =>
                        $stokSesudah,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | BARANG BERUBAH
            |--------------------------------------------------------------------------
            */

            else {

                /*
                |--------------------------------------------------------------------------
                | LOCK BARANG LAMA + BARANG BARU
                |--------------------------------------------------------------------------
                |
                | Penguncian berdasarkan ID untuk mengurangi risiko deadlock.
                |
                */

                $barangs = Barang::whereIn(
                    'id',
                    [
                        $barangLamaId,
                        $barangBaruId,
                    ]
                )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');


                $barangLama =
                    $barangs->get(
                        $barangLamaId
                    );

                $barangBaru =
                    $barangs->get(
                        $barangBaruId
                    );


                if (
                    !$barangLama
                    ||
                    !$barangBaru
                ) {
                    throw ValidationException::withMessages([
                        'barang_id' =>
                            'Data barang tidak ditemukan.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | SNAPSHOT STOK
                |--------------------------------------------------------------------------
                */

                $stokBarangLamaSebelum =
                    (int) $barangLama->stok;

                $stokBarangBaruSebelum =
                    (int) $barangBaru->stok;


                /*
                |--------------------------------------------------------------------------
                | OLD VALUES
                |--------------------------------------------------------------------------
                */

                $oldValues = [
                    'barang_id' =>
                        (int) $barangMasuk->barang_id,

                    'kode_barang' =>
                        $barangLama->kode_barang,

                    'nama_barang' =>
                        $barangLama->nama_barang,

                    'tanggal' =>
                        $barangMasuk->getRawOriginal('tanggal')
                        ?? $barangMasuk->tanggal,

                    'jumlah' =>
                        (int) $barangMasuk->jumlah,

                    'supplier' =>
                        $barangMasuk->supplier,

                    'keterangan' =>
                        $barangMasuk->keterangan,

                    'stok_barang' =>
                        $stokBarangLamaSebelum,
                ];


                /*
                |--------------------------------------------------------------------------
                | KOREKSI BARANG LAMA
                |--------------------------------------------------------------------------
                */

                $stokBarangLamaSesudah =
                    $stokBarangLamaSebelum
                    -
                    $jumlahLama;


                /*
                |--------------------------------------------------------------------------
                | PROTEKSI STOK NEGATIF BARANG LAMA
                |--------------------------------------------------------------------------
                */

                if ($stokBarangLamaSesudah < 0) {

                    throw ValidationException::withMessages([
                        'jumlah' =>
                            'Transaksi tidak dapat dipindahkan karena stok dari transaksi lama sudah digunakan. '
                            . 'Stok barang lama saat ini: '
                            . $barangLama->stok
                            . ', jumlah transaksi lama: '
                            . $jumlahLama
                            . '.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE BARANG LAMA
                |--------------------------------------------------------------------------
                */

                $barangLama->stok =
                    $stokBarangLamaSesudah;

                $barangLama->save();


                /*
                |--------------------------------------------------------------------------
                | TAMBAH STOK BARANG BARU
                |--------------------------------------------------------------------------
                */

                $stokBarangBaruSesudah =
                    $stokBarangBaruSebelum
                    +
                    $jumlahBaru;


                $barangBaru->stok =
                    $stokBarangBaruSesudah;

                $barangBaru->save();


                /*
                |--------------------------------------------------------------------------
                | UPDATE TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $barangMasuk->update([
                    'barang_id' =>
                        $barangBaruId,

                    'tanggal' =>
                        $validated['tanggal'],

                    'jumlah' =>
                        $jumlahBaru,

                    'supplier' =>
                        $validated['supplier'] ?? null,

                    'keterangan' =>
                        $validated['keterangan'] ?? null,
                ]);


                $barangMasuk->refresh();


                /*
                |--------------------------------------------------------------------------
                | NEW VALUES
                |--------------------------------------------------------------------------
                */

                $newValues = [
                    'barang_id' =>
                        (int) $barangMasuk->barang_id,

                    'kode_barang' =>
                        $barangBaru->kode_barang,

                    'nama_barang' =>
                        $barangBaru->nama_barang,

                    'tanggal' =>
                        $barangMasuk->getRawOriginal('tanggal')
                        ?? $barangMasuk->tanggal,

                    'jumlah' =>
                        (int) $barangMasuk->jumlah,

                    'supplier' =>
                        $barangMasuk->supplier,

                    'keterangan' =>
                        $barangMasuk->keterangan,

                    'stok_barang' =>
                        $stokBarangBaruSesudah,

                    'stok_barang_lama_sesudah' =>
                        $stokBarangLamaSesudah,
                ];
            }


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
                    'barang_masuk',

                'subject_id' =>
                    $barangMasuk->id,

                'description' =>
                    'Memperbarui transaksi barang masuk.',

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


        return redirect()
            ->route('barang-masuk.index')
            ->with(
                'success',
                'Data barang masuk berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        BarangMasuk $barangMasuk
    ) {
        DB::transaction(function () use (
            $barangMasuk,
            $request
        ) {
            /*
            |--------------------------------------------------------------------------
            | LOCK BARANG
            |--------------------------------------------------------------------------
            */

            $barang = Barang::where(
                'id',
                $barangMasuk->barang_id
            )
                ->lockForUpdate()
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT STOK SEBELUM
            |--------------------------------------------------------------------------
            */

            $stokSebelum =
                (int) $barang->stok;


            /*
            |--------------------------------------------------------------------------
            | DATA LAMA UNTUK AUDIT
            |--------------------------------------------------------------------------
            */

            $oldValues = [
                'barang_id' =>
                    (int) $barangMasuk->barang_id,

                'kode_barang' =>
                    $barang->kode_barang,

                'nama_barang' =>
                    $barang->nama_barang,

                'tanggal' =>
                    $barangMasuk->getRawOriginal('tanggal')
                    ?? $barangMasuk->tanggal,

                'jumlah' =>
                    (int) $barangMasuk->jumlah,

                'supplier' =>
                    $barangMasuk->supplier,

                'keterangan' =>
                    $barangMasuk->keterangan,

                'stok_barang' =>
                    $stokSebelum,
            ];


            /*
            |--------------------------------------------------------------------------
            | HITUNG STOK SESUDAH DELETE
            |--------------------------------------------------------------------------
            */

            $stokSesudah =
                $stokSebelum
                -
                (int) $barangMasuk->jumlah;


            /*
            |--------------------------------------------------------------------------
            | PROTEKSI STOK NEGATIF
            |--------------------------------------------------------------------------
            */

            if ($stokSesudah < 0) {

                throw ValidationException::withMessages([
                    'jumlah' =>
                        'Transaksi barang masuk tidak dapat dihapus karena sebagian stoknya sudah digunakan. '
                        . 'Stok saat ini: '
                        . $barang->stok
                        . ', jumlah transaksi masuk: '
                        . $barangMasuk->jumlah
                        . '.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE STOK
            |--------------------------------------------------------------------------
            */

            $barang->stok =
                $stokSesudah;

            $barang->save();


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
                    'barang_masuk',

                'subject_id' =>
                    $barangMasuk->id,

                'description' =>
                    'Menghapus transaksi barang masuk.',

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

            $barangMasuk->delete();
        });


        return redirect()
            ->route('barang-masuk.index')
            ->with(
                'success',
                'Data barang masuk berhasil dihapus.'
            );
    }
}