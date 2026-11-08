<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanMasukExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected Collection $barangMasuks;

    protected int $nomor = 0;

    public function __construct(Collection $barangMasuks)
    {
        $this->barangMasuks = $barangMasuks;
    }

    public function collection(): Collection
    {
        return $this->barangMasuks;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Merk',
            'Jumlah',
            'Satuan',
            'Supplier',
            'Keterangan',
        ];
    }

    public function map($barangMasuk): array
    {
        $this->nomor++;

        return [
            $this->nomor,
            $barangMasuk->tanggal,
            $barangMasuk->barang?->kode_barang ?? '-',
            $barangMasuk->barang?->nama_barang ?? '-',
            $barangMasuk->barang?->kategori?->nama_kategori ?? '-',
            $barangMasuk->barang?->merk ?? '-',
            $barangMasuk->jumlah,
            $barangMasuk->barang?->satuan ?? '-',
            $barangMasuk->supplier ?? '-',
            $barangMasuk->keterangan ?? '-',
        ];
    }
}