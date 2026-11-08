<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanTeknisiExport;
use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\Teknisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanTeknisiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LAPORAN PEMAKAIAN TEKNISI - ADMIN
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = BarangKeluar::with([
            'barang.kategori',
            'teknisi',
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTransaksi =
            $barangKeluars->count();

        $totalJumlahPemakaian =
            $barangKeluars->sum('jumlah');

        $teknisis = Teknisi::orderBy(
            'nama',
            'asc'
        )->get();

        return view(
            'laporan.teknisi.index',
            compact(
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahPemakaian',
                'teknisis'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL - ADMIN
    |--------------------------------------------------------------------------
    */

    public function excel(Request $request)
    {
        $query = BarangKeluar::with([
            'barang.kategori',
            'teknisi',
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(
            new LaporanTeknisiExport(
                $barangKeluars
            ),
            'laporan-pemakaian-teknisi.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF - ADMIN
    |--------------------------------------------------------------------------
    */

    public function pdf(Request $request)
    {
        $query = BarangKeluar::with([
            'barang.kategori',
            'teknisi',
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTransaksi =
            $barangKeluars->count();

        $totalJumlahPemakaian =
            $barangKeluars->sum('jumlah');

        $pdf = Pdf::loadView(
            'laporan.teknisi.pdf',
            compact(
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahPemakaian'
            )
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'laporan-pemakaian-teknisi.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER LAPORAN ADMIN
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        $query,
        Request $request
    ): void
    {
        if ($request->filled('tanggal_mulai')) {

            $query->whereDate(
                'tanggal',
                '>=',
                $request->tanggal_mulai
            );
        }

        if ($request->filled('tanggal_selesai')) {

            $query->whereDate(
                'tanggal',
                '<=',
                $request->tanggal_selesai
            );
        }

        if ($request->filled('teknisi_id')) {

            $query->where(
                'teknisi_id',
                $request->teknisi_id
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL TEKNISI LOGIN
    |--------------------------------------------------------------------------
    */

    private function getTeknisiLogin()
    {
        $teknisi =
            auth()->user()->teknisi;

        if (!$teknisi) {

            abort(
                403,
                'Akun teknisi belum terhubung dengan data teknisi.'
            );
        }

        return $teknisi;
    }


    /*
    |--------------------------------------------------------------------------
    | QUERY RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    private function riwayatSayaQuery(
        Request $request,
        $teknisi
    )
    {
        /*
        |--------------------------------------------------------------------------
        | KEAMANAN UTAMA
        |--------------------------------------------------------------------------
        |
        | teknisi_id SELALU berasal dari akun login.
        | teknisi_id dari URL/request tidak digunakan.
        |
        */

        $query = BarangKeluar::with([
            'barang.kategori',
            'teknisi',
        ])
        ->where(
            'teknisi_id',
            $teknisi->id
        );


        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL MULAI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal_mulai')) {

            $query->whereDate(
                'tanggal',
                '>=',
                $request->tanggal_mulai
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL SELESAI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal_selesai')) {

            $query->whereDate(
                'tanggal',
                '<=',
                $request->tanggal_selesai
            );
        }


        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | RIWAYAT SAYA - TEKNISI
    |--------------------------------------------------------------------------
    */

    public function saya(Request $request)
    {
        $teknisi =
            $this->getTeknisiLogin();

        $query =
            $this->riwayatSayaQuery(
                $request,
                $teknisi
            );

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTransaksi =
            $barangKeluars->count();

        $totalJumlahPemakaian =
            $barangKeluars->sum('jumlah');

        return view(
            'laporan.teknisi.saya',
            compact(
                'teknisi',
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahPemakaian'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    public function excelSaya(Request $request)
    {
        $teknisi =
            $this->getTeknisiLogin();

        $query =
            $this->riwayatSayaQuery(
                $request,
                $teknisi
            );

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(
            new LaporanTeknisiExport(
                $barangKeluars
            ),
            'riwayat-pemakaian-saya.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    public function pdfSaya(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | TEKNISI YANG SEDANG LOGIN
        |--------------------------------------------------------------------------
        */

        $teknisi =
            $this->getTeknisiLogin();


        /*
        |--------------------------------------------------------------------------
        | QUERY HANYA MILIK SENDIRI
        |--------------------------------------------------------------------------
        */

        $query =
            $this->riwayatSayaQuery(
                $request,
                $teknisi
            );


        /*
        |--------------------------------------------------------------------------
        | DATA PDF
        |--------------------------------------------------------------------------
        */

        $barangKeluars = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN
        |--------------------------------------------------------------------------
        */

        $totalTransaksi =
            $barangKeluars->count();

        $totalJumlahPemakaian =
            $barangKeluars->sum('jumlah');


        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'laporan.teknisi.saya-pdf',
            compact(
                'teknisi',
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahPemakaian'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | LANDSCAPE
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper(
            'a4',
            'landscape'
        );


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        return $pdf->download(
            'riwayat-pemakaian-saya.pdf'
        );
    }
}