<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKeluarPdfTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA DOWNLOAD PDF
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_download_pdf_laporan_barang_keluar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi PDF',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-PDF-001',
            'nama_barang' => 'Modem PDF',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan PDF',
            'keperluan' => 'Pemasangan perangkat',
            'tujuan' => 'Site PDF',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-keluar/pdf');

        $response->assertOk();

        $response->assertDownload(
            'laporan-barang-keluar.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH DOWNLOAD PDF GLOBAL
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_download_pdf_laporan_barang_keluar_global(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Terbatas PDF',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/laporan/barang-keluar/pdf');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | PDF MENGIKUTI FILTER
    |--------------------------------------------------------------------------
    */

    public function test_pdf_laporan_barang_keluar_mengikuti_filter_yang_dipilih(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI SATU
        |--------------------------------------------------------------------------
        */

        $userTeknisiSatu = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiSatu = Teknisi::create([
            'user_id' => $userTeknisiSatu->id,
            'nama' => 'Teknisi PDF Satu',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI DUA
        |--------------------------------------------------------------------------
        */

        $userTeknisiDua = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiDua = Teknisi::create([
            'user_id' => $userTeknisiDua->id,
            'nama' => 'Teknisi PDF Dua',
            'telepon' => '084444444444',
            'alamat' => 'Serang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriModem = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $kategoriRouter = Kategori::create([
            'nama_kategori' => 'Router',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $modem = Barang::create([
            'kode_barang' => 'MDM-PDF-001',
            'nama_barang' => 'Modem Target PDF',
            'kategori_id' => $kategoriModem->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $router = Barang::create([
            'kode_barang' => 'RTR-PDF-001',
            'nama_barang' => 'Router Bukan Target PDF',
            'kategori_id' => $kategoriRouter->id,
            'merk' => 'MikroTik',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA TARGET
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Target PDF',
            'keperluan' => 'Target PDF',
            'tujuan' => 'Site Target',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI BERBEDA
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiDua->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 8,
            'pengguna' => 'Pelanggan Teknisi Lain PDF',
            'keperluan' => 'Bukan target PDF',
            'tujuan' => 'Site Lain',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG / KATEGORI BERBEDA
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $router->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Router PDF',
            'keperluan' => 'Bukan target PDF',
            'tujuan' => 'Site Router',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 10,
            'pengguna' => 'Pelanggan September PDF',
            'keperluan' => 'Di luar periode',
            'tujuan' => 'Site September',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST PDF DENGAN FILTER
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar/pdf'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&barang_id=' . $modem->id
                . '&kategori_id=' . $kategoriModem->id
                . '&teknisi_id=' . $teknisiSatu->id
            );

        $response->assertOk();

        $response->assertDownload(
            'laporan-barang-keluar.pdf'
        );


        /*
        |--------------------------------------------------------------------------
        | PDF HARUS MENGANDUNG DATA TARGET
        |--------------------------------------------------------------------------
        */

        $content = $response->getContent();

        $this->assertStringContainsString(
            '%PDF',
            $content
        );
    }
}