<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KategoriController;


/*
|--------------------------------------------------------------------------
| CONTROLLER LAPORAN
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Laporan\LaporanStokController;
use App\Http\Controllers\Laporan\LaporanMasukController;
use App\Http\Controllers\Laporan\LaporanKeluarController;
use App\Http\Controllers\Laporan\LaporanTeknisiController;


use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;


/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect()->route('dashboard');

});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,teknisi'
])->group(function () {


    Route::get('/dashboard', function () {


        $totalBarang = Barang::count();

        $totalStok = Barang::sum('stok');

        $bulanIni = now()->month;

        $tahunIni = now()->year;


        /*
        |--------------------------------------------------------------------------
        | BARANG MASUK BULAN INI
        |--------------------------------------------------------------------------
        */

        $totalMasuk = BarangMasuk::whereMonth(
            'tanggal',
            $bulanIni
        )
        ->whereYear(
            'tanggal',
            $tahunIni
        )
        ->sum('jumlah');


        /*
        |--------------------------------------------------------------------------
        | BARANG KELUAR BULAN INI
        |--------------------------------------------------------------------------
        */

        $totalKeluar = BarangKeluar::whereMonth(
            'tanggal',
            $bulanIni
        )
        ->whereYear(
            'tanggal',
            $tahunIni
        )
        ->sum('jumlah');


        /*
        |--------------------------------------------------------------------------
        | STOK MENIPIS
        |--------------------------------------------------------------------------
        */

        $stokMenipis = Barang::with('kategori')
            ->whereColumn(
                'stok',
                '<=',
                'stok_minimum'
            )
            ->orderBy(
                'stok',
                'asc'
            )
            ->get();


        $jumlahStokMenipis =
            $stokMenipis->count();


        /*
        |--------------------------------------------------------------------------
        | BARANG TERBARU
        |--------------------------------------------------------------------------
        */

        $barangTerbaru = Barang::with('kategori')
            ->latest()
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | BARANG KELUAR TERBARU
        |--------------------------------------------------------------------------
        */

        $barangKeluarTerbaru =
            BarangKeluar::with([
                'barang',
                'teknisi'
            ])
            ->latest()
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GRAFIK 6 BULAN
        |--------------------------------------------------------------------------
        */

        $grafikLabels = [];

        $grafikMasuk = [];

        $grafikKeluar = [];


        for ($i = 5; $i >= 0; $i--) {

            $periode = now()
                ->copy()
                ->subMonths($i);


            $grafikLabels[] =
                $periode->translatedFormat('M');


            $grafikMasuk[] =
                BarangMasuk::whereYear(
                    'tanggal',
                    $periode->year
                )
                ->whereMonth(
                    'tanggal',
                    $periode->month
                )
                ->sum('jumlah');


            $grafikKeluar[] =
                BarangKeluar::whereYear(
                    'tanggal',
                    $periode->year
                )
                ->whereMonth(
                    'tanggal',
                    $periode->month
                )
                ->sum('jumlah');

        }


        $grafikMaksimum = max(
            max($grafikMasuk ?: [0]),
            max($grafikKeluar ?: [0]),
            1
        );


        return view(
            'dashboard',
            compact(
                'totalBarang',
                'totalStok',
                'totalMasuk',
                'totalKeluar',
                'stokMenipis',
                'jumlahStokMenipis',
                'barangTerbaru',
                'barangKeluarTerbaru',
                'grafikLabels',
                'grafikMasuk',
                'grafikKeluar',
                'grafikMaksimum'
            )
        );


    })->name('dashboard');


});


/*
|--------------------------------------------------------------------------
| KHUSUS ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | BARANG
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'barang',
        BarangController::class
    );


    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'kategori',
        KategoriController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'destroy'
    ]);


    /*
    |--------------------------------------------------------------------------
    | BARANG MASUK
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'barang-masuk',
        BarangMasukController::class
    );


    /*
    |--------------------------------------------------------------------------
    | TEKNISI
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'teknisi',
        TeknisiController::class
    );


    /*
    |--------------------------------------------------------------------------
    | MANAGEMENT USER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/users',
        [UserController::class, 'index']
    )->name('users.index');


    /*
    |--------------------------------------------------------------------------
    | TAMBAH USER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/users/create',
        [UserController::class, 'create']
    )->name('users.create');


    Route::post(
        '/users',
        [UserController::class, 'store']
    )->name('users.store');


    /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/users/{user}/edit',
        [UserController::class, 'edit']
    )->name('users.edit');


    Route::put(
        '/users/{user}',
        [UserController::class, 'update']
    )->name('users.update');


    /*
    |--------------------------------------------------------------------------
    | AKTIF / NONAKTIF USER
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/users/{user}/toggle-active',
        [UserController::class, 'toggleActive']
    )->name('users.toggle-active');


    /*
    |--------------------------------------------------------------------------
    | AUDIT ACTIVITY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/activity-logs',
        [ActivityLogController::class, 'index']
    )->name('activity-logs.index');



    /*
    |--------------------------------------------------------------------------
    | LAPORAN STOK
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/laporan/stok',
        [LaporanStokController::class, 'index']
    )->name('laporan.stok.index');


    Route::get(
        '/laporan/stok/excel',
        [LaporanStokController::class, 'excel']
    )->name('laporan.stok.excel');


    Route::get(
        '/laporan/stok/pdf',
        [LaporanStokController::class, 'pdf']
    )->name('laporan.stok.pdf');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN BARANG MASUK
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/laporan/barang-masuk',
        [LaporanMasukController::class, 'index']
    )->name('laporan.masuk.index');


    Route::get(
        '/laporan/barang-masuk/excel',
        [LaporanMasukController::class, 'excel']
    )->name('laporan.masuk.excel');


    Route::get(
        '/laporan/barang-masuk/pdf',
        [LaporanMasukController::class, 'pdf']
    )->name('laporan.masuk.pdf');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/laporan/barang-keluar',
        [LaporanKeluarController::class, 'index']
    )->name('laporan.keluar.index');


    Route::get(
        '/laporan/barang-keluar/excel',
        [LaporanKeluarController::class, 'excel']
    )->name('laporan.keluar.excel');


    Route::get(
        '/laporan/barang-keluar/pdf',
        [LaporanKeluarController::class, 'pdf']
    )->name('laporan.keluar.pdf');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN PEMAKAIAN TEKNISI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/laporan/teknisi',
        [LaporanTeknisiController::class, 'index']
    )->name('laporan.teknisi.index');


    Route::get(
        '/laporan/teknisi/excel',
        [LaporanTeknisiController::class, 'excel']
    )->name('laporan.teknisi.excel');


    Route::get(
        '/laporan/teknisi/pdf',
        [LaporanTeknisiController::class, 'pdf']
    )->name('laporan.teknisi.pdf');


});


/*
|--------------------------------------------------------------------------
| ADMIN + TEKNISI
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,teknisi'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'barang-keluar',
        BarangKeluarController::class
    );


});


/*
|--------------------------------------------------------------------------
| KHUSUS TEKNISI
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:teknisi'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | RIWAYAT PEMAKAIAN SAYA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/riwayat-saya',
        [LaporanTeknisiController::class, 'saya']
    )->name('riwayat.saya');


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/riwayat-saya/excel',
        [LaporanTeknisiController::class, 'excelSaya']
    )->name('riwayat.saya.excel');


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/riwayat-saya/pdf',
        [LaporanTeknisiController::class, 'pdfSaya']
    )->name('riwayat.saya.pdf');


});


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');


    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');


    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


});


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';