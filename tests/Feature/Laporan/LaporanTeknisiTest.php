<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTeknisiTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MELIHAT LAPORAN PEMAKAIAN TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_melihat_data_dan_ringkasan_laporan_pemakaian_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Andi',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
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
            'tanggal' => '2026-10-01',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan A',
            'keperluan' => 'Pemasangan baru',
            'tujuan' => 'Site A',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 2,
            'pengguna' => 'Pelanggan B',
            'keperluan' => 'Penggantian perangkat',
            'tujuan' => 'Site B',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/teknisi');

        $response->assertOk();

        $response->assertSee(
            'Laporan Pemakaian Teknisi'
        );

        $response->assertSee(
            'Teknisi Andi'
        );

        $response->assertSee(
            'Modem CDATA'
        );

        $response->assertSee(
            'Pemasangan baru'
        );

        $response->assertSee(
            'Penggantian perangkat'
        );

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) {
                return $barangKeluars->count() === 2;
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            2
        );

        $response->assertViewHas(
            'totalJumlahPemakaian',
            5
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER BERDASARKAN PERIODE TANGGAL
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_laporan_pemakaian_teknisi_berdasarkan_periode_tanggal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Periode',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Router',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-09-25',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan September Teknisi',
            'keperluan' => 'Pemakaian September',
            'tujuan' => 'Site September',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Oktober Teknisi',
            'keperluan' => 'Pemakaian Oktober',
            'tujuan' => 'Site Oktober',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) {
                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Oktober Teknisi';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahPemakaian',
            6
        );

        $response->assertSee(
            'Pelanggan Oktober Teknisi'
        );

        $response->assertSee(
            'Pemakaian Oktober'
        );

        $response->assertDontSee(
            'Pelanggan September Teknisi'
        );

        $response->assertDontSee(
            'Pemakaian September'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER BERDASARKAN TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_laporan_pemakaian_berdasarkan_teknisi(): void
    {
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
            'nama' => 'Teknisi Andi Filter',
            'telepon' => '081111111111',
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
            'nama' => 'Teknisi Budi Filter',
            'telepon' => '082222222222',
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
            'kode_barang' => 'MDM-TEK-001',
            'nama_barang' => 'Modem Filter Teknisi',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI ANDI - TARGET
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-07',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Khusus Andi',
            'keperluan' => 'Pekerjaan khusus Andi',
            'tujuan' => 'Site Khusus Andi',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI BUDI - BUKAN TARGET
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-08',
            'jumlah' => 9,
            'pengguna' => 'Pelanggan Khusus Budi',
            'keperluan' => 'Pekerjaan khusus Budi',
            'tujuan' => 'Site Khusus Budi',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST FILTER TEKNISI ANDI
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi'
                . '?teknisi_id='
                . $teknisiAndi->id
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | DATA HASIL FILTER
        |--------------------------------------------------------------------------
        */

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($teknisiAndi) {

                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->teknisi_id
                        === $teknisiAndi->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Khusus Andi';

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN
        |--------------------------------------------------------------------------
        */

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahPemakaian',
            4
        );


        /*
        |--------------------------------------------------------------------------
        | DATA TARGET MUNCUL
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'Pelanggan Khusus Andi'
        );

        $response->assertSee(
            'Pekerjaan khusus Andi'
        );

        $response->assertSee(
            'Site Khusus Andi'
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI BUDI TIDAK BOLEH MUNCUL
        |--------------------------------------------------------------------------
        |
        | Kita tidak memakai assertDontSee('Teknisi Budi Filter'),
        | karena nanti nama Budi tetap boleh muncul di dropdown filter.
        |
        */

        $response->assertDontSee(
            'Pelanggan Khusus Budi'
        );

        $response->assertDontSee(
            'Pekerjaan khusus Budi'
        );

        $response->assertDontSee(
            'Site Khusus Budi'
        );
    }
}