<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanKeluarExport;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeluarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HALAMAN LAPORAN
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

        $totalJumlahKeluar =
            $barangKeluars->sum('jumlah');


        /*
        |--------------------------------------------------------------------------
        | DATA DROPDOWN FILTER
        |--------------------------------------------------------------------------
        */

        $barangs = Barang::orderBy(
            'nama_barang',
            'asc'
        )->get();

        $kategoris = Kategori::orderBy(
            'nama_kategori',
            'asc'
        )->get();

        $teknisis = Teknisi::orderBy(
            'nama',
            'asc'
        )->get();


        return view(
            'laporan.keluar.index',
            compact(
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahKeluar',
                'barangs',
                'kategoris',
                'teknisis'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
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
            new LaporanKeluarExport(
                $barangKeluars
            ),
            'laporan-barang-keluar.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
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

        $totalJumlahKeluar =
            $barangKeluars->sum('jumlah');

        $pdf = Pdf::loadView(
            'laporan.keluar.pdf',
            compact(
                'barangKeluars',
                'totalTransaksi',
                'totalJumlahKeluar'
            )
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'laporan-barang-keluar.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER BERSAMA
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        $query,
        Request $request
    ): void
    {
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


        /*
        |--------------------------------------------------------------------------
        | FILTER TEKNISI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('teknisi_id')) {

            $query->where(
                'teknisi_id',
                $request->teknisi_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BARANG
        |--------------------------------------------------------------------------
        */

        if ($request->filled('barang_id')) {

            $query->where(
                'barang_id',
                $request->barang_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kategori_id')) {

            $query->whereHas(
                'barang',
                function ($barangQuery) use ($request) {

                    $barangQuery->where(
                        'kategori_id',
                        $request->kategori_id
                    );

                }
            );
        }
    }
}