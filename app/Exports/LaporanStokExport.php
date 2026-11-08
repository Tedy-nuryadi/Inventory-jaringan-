<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanStokExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    private array $filters;

    private int $nomor = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Barang::with('kategori');

        /*
        |--------------------------------------------------------------------------
        | FILTER PENCARIAN
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['search'])) {

            $search = trim($this->filters['search']);

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
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['kategori_id'])) {

            $query->where(
                'kategori_id',
                $this->filters['kategori_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        $status = $this->filters['status'] ?? null;

        if ($status === 'kritis') {

            $query->where('stok', 0);

        } elseif ($status === 'menipis') {

            $query
                ->where('stok', '>', 0)
                ->whereColumn(
                    'stok',
                    '<=',
                    'stok_minimum'
                );

        } elseif ($status === 'aman') {

            $query->whereColumn(
                'stok',
                '>',
                'stok_minimum'
            );
        }


        return $query
            ->orderBy('nama_barang', 'asc')
            ->get();
    }


    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Merk',
            'Lokasi',
            'Stok',
            'Satuan',
            'Stok Minimum',
            'Status',
        ];
    }


    public function map($barang): array
    {
        $this->nomor++;

        if ($barang->stok == 0) {

            $status = 'Kritis';

        } elseif ($barang->stok <= $barang->stok_minimum) {

            $status = 'Menipis';

        } else {

            $status = 'Aman';
        }


        return [
            $this->nomor,
            $barang->kode_barang,
            $barang->nama_barang,
            $barang->kategori?->nama_kategori ?? '-',
            $barang->merk ?? '-',
            $barang->lokasi ?? '-',
            $barang->stok,
            $barang->satuan,
            $barang->stok_minimum,
            $status,
        ];
    }
}