<?php

namespace Tests\Feature\Laporan;

use App\Exports\LaporanTeknisiExport;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanTeknisiExcelTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA DOWNLOAD EXCEL
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_download_excel_laporan_pemakaian_teknisi(): void
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
            'kode_barang' => 'MDM-TEK-EXCEL-001',
            'nama_barang' => 'Modem Teknisi Excel',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan Excel Teknisi',
            'keperluan' => 'Pemasangan perangkat',
            'tujuan' => 'Site Excel Teknisi',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/teknisi/excel');

        $response->assertOk();

        $response->assertDownload(
            'laporan-pemakaian-teknisi.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH DOWNLOAD LAPORAN GLOBAL
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_download_excel_laporan_pemakaian_teknisi_global(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Terbatas Excel',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/laporan/teknisi/excel');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL MENGIKUTI FILTER PERIODE + TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_excel_laporan_pemakaian_teknisi_mengikuti_filter_yang_dipilih(): void
    {
        Excel::fake();


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI ANDI
        |--------------------------------------------------------------------------
        */

        $userAndi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiAndi = Teknisi::create([
            'user_id' => $userAndi->id,
            'nama' => 'Teknisi Andi Excel Filter',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI BUDI
        |--------------------------------------------------------------------------
        */

        $userBudi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiBudi = Teknisi::create([
            'user_id' => $userBudi->id,
            'nama' => 'Teknisi Budi Excel Filter',
            'telepon' => '084444444444',
            'alamat' => 'Serang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' => 'MDM-FILTER-001',
            'nama_barang' => 'Modem Filter Excel Teknisi',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TARGET: ANDI + DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Target Excel Teknisi',
            'keperluan' => 'Target Excel Teknisi',
            'tujuan' => 'Site Target Excel',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI TAPI DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 7,
            'pengguna' => 'Pelanggan September Excel',
            'keperluan' => 'Di luar periode',
            'tujuan' => 'Site September',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUDI DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 9,
            'pengguna' => 'Pelanggan Budi Excel',
            'keperluan' => 'Teknisi berbeda',
            'tujuan' => 'Site Budi',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST EXPORT DENGAN FILTER
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi/excel'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&teknisi_id=' . $teknisiAndi->id
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | ASSERT DOWNLOAD + DATA HASIL FILTER
        |--------------------------------------------------------------------------
        */

        Excel::assertDownloaded(
            'laporan-pemakaian-teknisi.xlsx',
            function (LaporanTeknisiExport $export) use ($teknisiAndi) {

                $data = $export->collection();

                return $data->count() === 1
                    && $data->first()->teknisi_id === $teknisiAndi->id
                    && $data->first()->pengguna
                        === 'Pelanggan Target Excel Teknisi'
                    && (int) $data->first()->jumlah === 4;
            }
        );
    }
}