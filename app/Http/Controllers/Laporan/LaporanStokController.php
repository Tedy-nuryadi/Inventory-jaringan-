<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanStokExport;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with('kategori');

        $this->applyFilters($query, $request);

        $barangs = $query
            ->orderBy('nama_barang', 'asc')
            ->get();

        $kategoris = Kategori::orderBy(
            'nama_kategori',
            'asc'
        )->get();

        $totalJenisBarang = $barangs->count();

        $totalStok = $barangs->sum('stok');

        $stokMenipis = $barangs
            ->filter(function ($barang) {

                return $barang->stok > 0
                    && $barang->stok <= $barang->stok_minimum;

            })
            ->count();

        return view(
            'laporan.stok.index',
            compact(
                'barangs',
                'kategoris',
                'totalJenisBarang',
                'totalStok',
                'stokMenipis'
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
        $filters = $request->only([
            'search',
            'kategori_id',
            'status',
        ]);

        return Excel::download(
            new LaporanStokExport($filters),
            'laporan-stok.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(Request $request)
    {
        $query = Barang::with('kategori');

        $this->applyFilters($query, $request);

        $barangs = $query
            ->orderBy('nama_barang', 'asc')
            ->get();

        $totalJenisBarang = $barangs->count();

        $totalStok = $barangs->sum('stok');

        $stokMenipis = $barangs
            ->filter(function ($barang) {

                return $barang->stok > 0
                    && $barang->stok <= $barang->stok_minimum;

            })
            ->count();

        $pdf = Pdf::loadView(
            'laporan.stok.pdf',
            compact(
                'barangs',
                'totalJenisBarang',
                'totalStok',
                'stokMenipis'
            )
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'laporan-stok.pdf'
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
    ): void {

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->where(function ($q) use ($search) {

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
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kategori_id')) {

            $query->where(
                'kategori_id',
                $request->kategori_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'kritis') {

            $query->where(
                'stok',
                0
            );

        } elseif ($request->status === 'menipis') {

            $query
                ->where(
                    'stok',
                    '>',
                    0
                )
                ->whereColumn(
                    'stok',
                    '<=',
                    'stok_minimum'
                );

        } elseif ($request->status === 'aman') {

            $query->whereColumn(
                'stok',
                '>',
                'stok_minimum'
            );
        }
    }
}