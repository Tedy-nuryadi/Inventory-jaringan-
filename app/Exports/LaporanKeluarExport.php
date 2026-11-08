<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanKeluarExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected Collection $barangKeluars;

    protected int $nomor = 0;

    public function __construct(Collection $barangKeluars)
    {
        $this->barangKeluars = $barangKeluars;
    }

    public function collection(): Collection
    {
        return $this->barangKeluars;
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
            'Teknisi',
            'Jumlah',
            'Satuan',
            'Pengguna',
            'Keperluan',
            'Tujuan',
            'Foto',
        ];
    }

    public function map($barangKeluar): array
    {
        $this->nomor++;

        return [
            $this->nomor,

            $barangKeluar->tanggal
                ? $barangKeluar->tanggal->format('Y-m-d')
                : '-',

            $barangKeluar->barang?->kode_barang
                ?? '-',

            $barangKeluar->barang?->nama_barang
                ?? '-',

            $barangKeluar->barang?->kategori?->nama_kategori
                ?? '-',

            $barangKeluar->barang?->merk
                ?? '-',

            $barangKeluar->teknisi?->nama
                ?? '-',

            $barangKeluar->jumlah,

            $barangKeluar->barang?->satuan
                ?? '-',

            $barangKeluar->pengguna
                ?? '-',

            $barangKeluar->keperluan
                ?? '-',

            $barangKeluar->tujuan
                ?? '-',

            $barangKeluar->foto
                ?? '-',
        ];
    }
}