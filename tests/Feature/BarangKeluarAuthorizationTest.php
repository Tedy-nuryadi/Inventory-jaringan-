<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangKeluarAuthorizationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MENGHAPUS TRANSAKSI MILIK TEKNISI LAIN
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_menghapus_transaksi_barang_keluar_milik_teknisi_lain(): void
    {
        $userTeknisiA = User::factory()->create([
            'name' => 'User Teknisi A Delete',
            'email' => 'teknisi.a.delete@example.com',
            'role' => 'teknisi',
        ]);

        $teknisiA = Teknisi::create([
            'user_id' => $userTeknisiA->id,
            'nama' => 'Teknisi A Delete',
            'telepon' => '081200001111',
            'alamat' => 'Tangerang',
        ]);


        $userTeknisiB = User::factory()->create([
            'name' => 'User Teknisi B Delete',
            'email' => 'teknisi.b.delete@example.com',
            'role' => 'teknisi',
        ]);

        $teknisiB = Teknisi::create([
            'user_id' => $userTeknisiB->id,
            'nama' => 'Teknisi B Delete',
            'telepon' => '081200002222',
            'alamat' => 'Tangerang',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Authorization',
        ]);


        $barang = Barang::create([
            'kode_barang' => 'BRG-AUTH-DELETE-001',
            'nama_barang' => 'Router Authorization Delete',
            'kategori_id' => $kategori->id,
            'merk' => 'Auth Brand',
            'stok' => 8,
            'satuan' => 'Unit',
            'lokasi' => 'Gudang Authorization',
            'stok_minimum' => 2,
        ]);


        $barangKeluar = BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiB->id,
            'tanggal' => '2026-10-11',
            'jumlah' => 2,
            'pengguna' => $userTeknisiB->name,
            'keperluan' => 'Instalasi milik Teknisi B',
            'foto' => null,
        ]);


        $stokSebelum =
            (int) $barang->stok;

        $barangIdSebelum =
            (int) $barangKeluar->barang_id;

        $teknisiIdSebelum =
            (int) $barangKeluar->teknisi_id;

        $jumlahSebelum =
            (int) $barangKeluar->jumlah;

        $tanggalSebelum =
            $barangKeluar->tanggal;

        $keperluanSebelum =
            $barangKeluar->keperluan;

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | DELETE ILEGAL
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($userTeknisiA)
            ->delete(
                route(
                    'barang-keluar.destroy',
                    $barangKeluar
                )
            );


        $response->assertForbidden();


        /*
        |--------------------------------------------------------------------------
        | STOK TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $barang->refresh();

        $this->assertSame(
            $stokSebelum,
            (int) $barang->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barang->id,
                'stok' => 8,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TIDAK BOLEH TERHAPUS
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'barang_id' => $barang->id,
                'teknisi_id' => $teknisiB->id,
                'jumlah' => 2,
                'keperluan' => 'Instalasi milik Teknisi B',
            ]
        );


        $barangKeluar->refresh();


        $this->assertSame(
            $barangIdSebelum,
            (int) $barangKeluar->barang_id
        );

        $this->assertSame(
            $teknisiIdSebelum,
            (int) $barangKeluar->teknisi_id
        );

        $this->assertSame(
            $teknisiB->id,
            (int) $barangKeluar->teknisi_id
        );

        $this->assertSame(
            $jumlahSebelum,
            (int) $barangKeluar->jumlah
        );

        $this->assertEquals(
            $tanggalSebelum,
            $barangKeluar->tanggal
        );

        $this->assertSame(
            $keperluanSebelum,
            $barangKeluar->keperluan
        );


        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangKeluar::count()
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT PALSU
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );


        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisiA->id,
                'user_id' => $userTeknisiA->id,
            ]
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisiB->id,
                'user_id' => $userTeknisiB->id,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MEMBUKA EDIT TRANSAKSI MILIK TEKNISI LAIN
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_membuka_halaman_edit_transaksi_barang_keluar_milik_teknisi_lain(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI A
        |--------------------------------------------------------------------------
        */

        $userTeknisiA = User::factory()->create([
            'name' =>
                'User Teknisi A Edit',

            'email' =>
                'teknisi.a.edit.security@example.com',

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
                'Teknisi A Edit',

            'telepon' =>
                '081200003333',

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
                'User Teknisi B Edit',

            'email' =>
                'teknisi.b.edit.security@example.com',

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
                'Teknisi B Edit',

            'telepon' =>
                '081200004444',

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
                'Perangkat Edit Authorization',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BRG-AUTH-EDIT-001',

            'nama_barang' =>
                'Router Authorization Edit',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Auth Brand',

            'stok' =>
                8,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Authorization',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $barangKeluar = BarangKeluar::create([
            'barang_id' =>
                $barang->id,

            'teknisi_id' =>
                $teknisiB->id,

            'tanggal' =>
                '2026-10-11',

            'jumlah' =>
                2,

            'pengguna' =>
                $userTeknisiB->name,

            'keperluan' =>
                'Transaksi rahasia Teknisi B',

            'foto' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT
        |--------------------------------------------------------------------------
        */

        $stokSebelum =
            (int) $barang->stok;

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | TEKNISI A COBA BUKA URL EDIT TEKNISI B
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($userTeknisiA)
            ->get(
                route(
                    'barang-keluar.edit',
                    $barangKeluar
                )
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS 403
        |--------------------------------------------------------------------------
        */

        $response->assertForbidden();


        /*
        |--------------------------------------------------------------------------
        | STOK TIDAK BERUBAH
        |--------------------------------------------------------------------------
        */

        $barang->refresh();

        $this->assertSame(
            $stokSebelum,
            (int) $barang->stok
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TETAP ADA
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' =>
                    $barangKeluar->id,

                'barang_id' =>
                    $barang->id,

                'teknisi_id' =>
                    $teknisiB->id,

                'jumlah' =>
                    2,

                'keperluan' =>
                    'Transaksi rahasia Teknisi B',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH TRANSAKSI TETAP
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangKeluar::count()
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT PALSU
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );


        /*
        |--------------------------------------------------------------------------
        | RELASI TEKNISI TETAP NORMAL
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisiA->id,

                'user_id' =>
                    $userTeknisiA->id,
            ]
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisiB->id,

                'user_id' =>
                    $userTeknisiB->id,
            ]
        );
    }
}