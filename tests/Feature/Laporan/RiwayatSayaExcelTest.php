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

class RiwayatSayaExcelTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI BISA DOWNLOAD EXCEL RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_bisa_download_excel_riwayat_saya(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Excel Riwayat Saya',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-RIWAYAT-EXCEL-001',
            'nama_barang' => 'Modem Riwayat Excel',
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
            'pengguna' => 'Pelanggan Excel Riwayat Saya',
            'keperluan' => 'Pemasangan Excel Riwayat',
            'tujuan' => 'Site Excel Riwayat',
            'foto' => null,
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/riwayat-saya/excel');

        $response->assertOk();

        $response->assertDownload(
            'riwayat-pemakaian-saya.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL HANYA MILIK TEKNISI LOGIN + MENGIKUTI FILTER PERIODE
    |--------------------------------------------------------------------------
    */

    public function test_excel_riwayat_saya_hanya_berisi_data_milik_teknisi_login_dan_mengikuti_filter_periode(): void
    {
        Excel::fake();


        /*
        |--------------------------------------------------------------------------
        | TEKNISI ANDI - USER YANG LOGIN
        |--------------------------------------------------------------------------
        */

        $userAndi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiAndi = Teknisi::create([
            'user_id' => $userAndi->id,
            'nama' => 'Teknisi Andi Excel Saya',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI BUDI - USER LAIN
        |--------------------------------------------------------------------------
        */

        $userBudi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiBudi = Teknisi::create([
            'user_id' => $userBudi->id,
            'nama' => 'Teknisi Budi Excel Saya',
            'telepon' => '082222222222',
            'alamat' => 'Serang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' => 'Router',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' => 'RTR-RIWAYAT-EXCEL-002',
            'nama_barang' => 'Router Excel Riwayat Saya',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI - DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Andi September Excel',
            'keperluan' => 'Pekerjaan Andi September',
            'tujuan' => 'Site Andi September',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI - TARGET DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Target Andi Excel Saya',
            'keperluan' => 'Pekerjaan Target Andi',
            'tujuan' => 'Site Target Andi',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUDI - DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 9,
            'pengguna' => 'Pelanggan Budi Excel Saya',
            'keperluan' => 'Pekerjaan Budi',
            'tujuan' => 'Site Budi',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST SEBAGAI ANDI
        |--------------------------------------------------------------------------
        |
        | Sengaja kita masukkan teknisi_id milik Budi.
        |
        | Controller Riwayat Saya HARUS mengabaikannya dan tetap memakai
        | identitas teknisi dari akun yang sedang login.
        |
        */

        $response = $this->actingAs($userAndi)
            ->get(
                '/riwayat-saya/excel'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&teknisi_id=' . $teknisiBudi->id
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | PERIKSA DATA YANG DIKIRIM KE EXPORT
        |--------------------------------------------------------------------------
        */

        Excel::assertDownloaded(
            'riwayat-pemakaian-saya.xlsx',
            function (LaporanTeknisiExport $export) use ($teknisiAndi) {

                $data = $export->collection();

                if ($data->count() !== 1) {
                    return false;
                }

                $barangKeluar = $data->first();

                return
                    $barangKeluar->teknisi_id
                        === $teknisiAndi->id

                    && $barangKeluar->tanggal->format('Y-m-d')
                        === '2026-10-05'

                    && $barangKeluar->pengguna
                        === 'Pelanggan Target Andi Excel Saya'

                    && $barangKeluar->keperluan
                        === 'Pekerjaan Target Andi'

                    && (int) $barangKeluar->jumlah
                        === 6;
            }
        );
    }
}