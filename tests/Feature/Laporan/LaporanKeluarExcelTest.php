<?php

namespace Tests\Feature\Laporan;

use App\Exports\LaporanKeluarExport;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanKeluarExcelTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA DOWNLOAD EXCEL
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_download_excel_laporan_barang_keluar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Excel',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-EXCEL-001',
            'nama_barang' => 'Modem Excel',
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
            'pengguna' => 'Pelanggan Excel',
            'keperluan' => 'Pemasangan perangkat',
            'tujuan' => 'Site Excel',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-keluar/excel');

        $response->assertOk();

        $response->assertDownload(
            'laporan-barang-keluar.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH DOWNLOAD LAPORAN GLOBAL
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_download_excel_laporan_barang_keluar_global(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Terbatas',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/laporan/barang-keluar/excel');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL MENGIKUTI FILTER
    |--------------------------------------------------------------------------
    */

    public function test_excel_laporan_barang_keluar_mengikuti_filter_yang_dipilih(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI
        |--------------------------------------------------------------------------
        */

        $userTeknisiSatu = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiSatu = Teknisi::create([
            'user_id' => $userTeknisiSatu->id,
            'nama' => 'Teknisi Excel Satu',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);


        $userTeknisiDua = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiDua = Teknisi::create([
            'user_id' => $userTeknisiDua->id,
            'nama' => 'Teknisi Excel Dua',
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
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem Filter Excel',
            'kategori_id' => $kategoriModem->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        $router = Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router Filter Excel',
            'kategori_id' => $kategoriRouter->id,
            'merk' => 'MikroTik',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA YANG HARUS MASUK KE EXCEL
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Target Excel',
            'keperluan' => 'Data target Excel',
            'tujuan' => 'Site Target',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA TEKNISI BERBEDA
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiDua->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 8,
            'pengguna' => 'Pelanggan Teknisi Lain',
            'keperluan' => 'Tidak boleh masuk Excel',
            'tujuan' => 'Site Lain',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA BARANG / KATEGORI BERBEDA
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $router->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Router',
            'keperluan' => 'Tidak boleh masuk Excel',
            'tujuan' => 'Site Router',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 10,
            'pengguna' => 'Pelanggan September',
            'keperluan' => 'Di luar periode',
            'tujuan' => 'Site September',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST EXPORT DENGAN FILTER LENGKAP
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar/excel'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&barang_id=' . $modem->id
                . '&kategori_id=' . $kategoriModem->id
                . '&teknisi_id=' . $teknisiSatu->id
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN FILE DIDOWNLOAD
        |--------------------------------------------------------------------------
        */

        Excel::assertDownloaded(
            'laporan-barang-keluar.xlsx',
            function (LaporanKeluarExport $export) use (
                $modem,
                $teknisiSatu
            ) {

                $data = $export->collection();

                return $data->count() === 1
                    && $data->first()->barang_id === $modem->id
                    && $data->first()->teknisi_id === $teknisiSatu->id
                    && $data->first()->pengguna === 'Pelanggan Target Excel'
                    && (int) $data->first()->jumlah === 4;
            }
        );
    }
}