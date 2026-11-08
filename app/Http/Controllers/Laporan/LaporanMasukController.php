<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanMasukExport;
use App\Http\Controllers\Controller;
use App\Models\BarangMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangMasuk::with([
            'barang.kategori'
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangMasuks = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTransaksi = $barangMasuks->count();

        $totalJumlahMasuk = $barangMasuks->sum('jumlah');

        return view(
            'laporan.masuk.index',
            compact(
                'barangMasuks',
                'totalTransaksi',
                'totalJumlahMasuk'
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
        $query = BarangMasuk::with([
            'barang.kategori'
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangMasuks = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return Excel::download(
            new LaporanMasukExport(
                $barangMasuks
            ),
            'laporan-barang-masuk.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(Request $request)
    {
        $query = BarangMasuk::with([
            'barang.kategori'
        ]);

        $this->applyFilters(
            $query,
            $request
        );

        $barangMasuks = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalTransaksi = $barangMasuks->count();

        $totalJumlahMasuk = $barangMasuks->sum('jumlah');

        $pdf = Pdf::loadView(
            'laporan.masuk.pdf',
            compact(
                'barangMasuks',
                'totalTransaksi',
                'totalJumlahMasuk'
            )
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'laporan-barang-masuk.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER QUERY
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        $query,
        Request $request
    ): void
    {
        /*
        |--------------------------------------------------------------------------
        | TANGGAL MULAI
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
        | TANGGAL SELESAI
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
        | PENCARIAN
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {

                    $q->whereHas(
                        'barang',
                        function ($barangQuery) use ($search) {

                            $barangQuery
                                ->where(
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
                                );

                        }
                    )
                    ->orWhere(
                        'supplier',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'keterangan',
                        'like',
                        '%' . $search . '%'
                    );

                }
            );
        }
    }
}