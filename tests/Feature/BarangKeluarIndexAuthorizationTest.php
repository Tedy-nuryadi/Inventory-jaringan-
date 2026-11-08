<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangKeluarIndexAuthorizationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI HANYA BOLEH MELIHAT TRANSAKSI MILIKNYA SENDIRI
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_hanya_bisa_melihat_daftar_barang_keluar_miliknya_sendiri(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI A
        |--------------------------------------------------------------------------
        */

        $userTeknisiA = User::factory()->create([
            'name' =>
                'User Teknisi A Index',

            'email' =>
                'teknisi.a.index@example.com',

            'role' =>
                'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI A
        |--------------------------------------------------------------------------
        */

        $teknisiA = Teknisi::create([
            'user_id' =>
                $userTeknisiA->id,

            'nama' =>
                'Teknisi A Index',

            'telepon' =>
                '081211110001',

            'alamat' =>
                'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI B
        |--------------------------------------------------------------------------
        */

        $userTeknisiB = User::factory()->create([
            'name' =>
                'User Teknisi B Index',

            'email' =>
                'teknisi.b.index@example.com',

            'role' =>
                'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI B
        |--------------------------------------------------------------------------
        */

        $teknisiB = Teknisi::create([
            'user_id' =>
                $userTeknisiB->id,

            'nama' =>
                'Teknisi B Index',

            'telepon' =>
                '081211110002',

            'alamat' =>
                'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Index Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG A
        |--------------------------------------------------------------------------
        */

        $barangA = Barang::create([
            'kode_barang' =>
                'BRG-INDEX-A',

            'nama_barang' =>
                'Router Index A',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Brand A',

            'stok' =>
                10,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Rak A',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG B
        |--------------------------------------------------------------------------
        */

        $barangB = Barang::create([
            'kode_barang' =>
                'BRG-INDEX-B',

            'nama_barang' =>
                'Switch Index B',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Brand B',

            'stok' =>
                10,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Rak B',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI A
        |--------------------------------------------------------------------------
        */

        $transaksiA = BarangKeluar::create([
            'barang_id' =>
                $barangA->id,

            'teknisi_id' =>
                $teknisiA->id,

            'tanggal' =>
                '2026-10-11',

            'jumlah' =>
                1,

            'pengguna' =>
                $userTeknisiA->name,

            'keperluan' =>
                'TRANSAKSI-MILIK-TEKNISI-A-INDEX',

            'foto' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $transaksiB = BarangKeluar::create([
            'barang_id' =>
                $barangB->id,

            'teknisi_id' =>
                $teknisiB->id,

            'tanggal' =>
                '2026-10-12',

            'jumlah' =>
                2,

            'pengguna' =>
                $userTeknisiB->name,

            'keperluan' =>
                'TRANSAKSI-RAHASIA-TEKNISI-B-INDEX',

            'foto' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI A MEMBUKA DAFTAR BARANG KELUAR
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($userTeknisiA)
            ->get(
                route('barang-keluar.index')
            );


        /*
        |--------------------------------------------------------------------------
        | HALAMAN HARUS BISA DIBUKA
        |--------------------------------------------------------------------------
        */

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA YANG DIKIRIM CONTROLLER KE VIEW
        |--------------------------------------------------------------------------
        |
        | Mendukung nama variabel:
        |
        | - barangKeluar
        | - barangKeluars
        |
        | sehingga test tetap aman apabila view menggunakan salah satunya.
        |
        */

        $viewData =
            $response->original->getData();


        $daftarTransaksi =
            $viewData['barangKeluar']
            ??
            $viewData['barangKeluars']
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | DATA TRANSAKSI HARUS TERSEDIA
        |--------------------------------------------------------------------------
        */

        $this->assertNotNull(
            $daftarTransaksi,
            'Data daftar Barang Keluar tidak ditemukan pada view.'
        );


        /*
        |--------------------------------------------------------------------------
        | HANYA SATU TRANSAKSI YANG BOLEH DILIHAT TEKNISI A
        |--------------------------------------------------------------------------
        */

        $this->assertCount(
            1,
            $daftarTransaksi
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI A HARUS ADA
        |--------------------------------------------------------------------------
        */

        $this->assertTrue(
            $daftarTransaksi->contains(
                'id',
                $transaksiA->id
            )
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI B TIDAK BOLEH ADA
        |--------------------------------------------------------------------------
        */

        $this->assertFalse(
            $daftarTransaksi->contains(
                'id',
                $transaksiB->id
            )
        );


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN TEKNISI YANG TAMPIL BENAR
        |--------------------------------------------------------------------------
        */

        $transaksiYangTerlihat =
            $daftarTransaksi->first();


        $this->assertSame(
            $teknisiA->id,
            (int) $transaksiYangTerlihat->teknisi_id
        );


        $this->assertNotSame(
            $teknisiB->id,
            (int) $transaksiYangTerlihat->teknisi_id
        );


        /*
        |--------------------------------------------------------------------------
        | DATABASE TETAP MENYIMPAN KEDUA TRANSAKSI
        |--------------------------------------------------------------------------
        |
        | Filter hanya membatasi apa yang boleh dilihat.
        | Transaksi Teknisi B tidak boleh terhapus.
        |
        */

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' =>
                    $transaksiA->id,

                'teknisi_id' =>
                    $teknisiA->id,
            ]
        );


        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' =>
                    $transaksiB->id,

                'teknisi_id' =>
                    $teknisiB->id,
            ]
        );
    }
}