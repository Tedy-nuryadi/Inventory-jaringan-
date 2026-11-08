<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatSayaTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI HANYA MELIHAT RIWAYAT MILIKNYA SENDIRI
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_hanya_bisa_melihat_riwayat_pemakaian_miliknya_sendiri(): void
    {
        $userAndi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiAndi = Teknisi::create([
            'user_id' => $userAndi->id,
            'nama' => 'Teknisi Andi Riwayat',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $userBudi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiBudi = Teknisi::create([
            'user_id' => $userBudi->id,
            'nama' => 'Teknisi Budi Riwayat',
            'telepon' => '082222222222',
            'alamat' => 'Serang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-RIWAYAT-001',
            'nama_barang' => 'Modem Riwayat Teknisi',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan Milik Andi',
            'keperluan' => 'Pemasangan Andi',
            'tujuan' => 'Site Andi',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 8,
            'pengguna' => 'Pelanggan Milik Budi',
            'keperluan' => 'Pemasangan Budi',
            'tujuan' => 'Site Budi',
            'foto' => null,
        ]);

        $response = $this->actingAs($userAndi)
            ->get('/riwayat-saya');

        $response->assertOk();

        $response->assertViewIs(
            'laporan.teknisi.saya'
        );

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($teknisiAndi) {

                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->teknisi_id
                        === $teknisiAndi->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Milik Andi';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahPemakaian',
            3
        );

        $response->assertSee(
            'Riwayat Pemakaian Saya'
        );

        $response->assertSee(
            'Pelanggan Milik Andi'
        );

        $response->assertSee(
            'Pemasangan Andi'
        );

        $response->assertSee(
            'Site Andi'
        );

        $response->assertDontSee(
            'Pelanggan Milik Budi'
        );

        $response->assertDontSee(
            'Pemasangan Budi'
        );

        $response->assertDontSee(
            'Site Budi'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER RIWAYAT SAYA BERDASARKAN PERIODE TANGGAL
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_bisa_memfilter_riwayat_saya_berdasarkan_periode_tanggal(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER + TEKNISI
        |--------------------------------------------------------------------------
        */

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Filter Periode Riwayat',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
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
            'kode_barang' => 'RTR-RIWAYAT-001',
            'nama_barang' => 'Router Riwayat Saya',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI SEPTEMBER - HARUS TERFILTER KELUAR
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-09-25',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Riwayat September',
            'keperluan' => 'Pekerjaan September Riwayat',
            'tujuan' => 'Site September Riwayat',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI OKTOBER - TARGET
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Riwayat Oktober',
            'keperluan' => 'Pekerjaan Oktober Riwayat',
            'tujuan' => 'Site Oktober Riwayat',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST FILTER PERIODE
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($userTeknisi)
            ->get(
                '/riwayat-saya'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );


        /*
        |--------------------------------------------------------------------------
        | ASSERT
        |--------------------------------------------------------------------------
        */

        $response->assertOk();

        $response->assertViewIs(
            'laporan.teknisi.saya'
        );


        /*
        |--------------------------------------------------------------------------
        | HASIL FILTER
        |--------------------------------------------------------------------------
        */

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($teknisi) {

                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->teknisi_id
                        === $teknisi->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Riwayat Oktober';
            }
        );


        /*
        |--------------------------------------------------------------------------
        | RINGKASAN HARUS IKUT FILTER
        |--------------------------------------------------------------------------
        */

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahPemakaian',
            6
        );


        /*
        |--------------------------------------------------------------------------
        | DATA OKTOBER MUNCUL
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'Pelanggan Riwayat Oktober'
        );

        $response->assertSee(
            'Pekerjaan Oktober Riwayat'
        );

        $response->assertSee(
            'Site Oktober Riwayat'
        );


        /*
        |--------------------------------------------------------------------------
        | DATA SEPTEMBER TIDAK MUNCUL
        |--------------------------------------------------------------------------
        */

        $response->assertDontSee(
            'Pelanggan Riwayat September'
        );

        $response->assertDontSee(
            'Pekerjaan September Riwayat'
        );

        $response->assertDontSee(
            'Site September Riwayat'
        );
    }
}