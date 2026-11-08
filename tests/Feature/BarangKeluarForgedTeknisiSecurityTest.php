<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangKeluarForgedTeknisiSecurityTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | STORE - TEKNISI TIDAK BOLEH MEMALSUKAN TEKNISI_ID
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_membuat_barang_keluar_atas_nama_teknisi_lain_dengan_teknisi_id_palsu(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI A
        |--------------------------------------------------------------------------
        */

        $userA = User::factory()->create([
            'name' =>
                'Teknisi A',

            'email' =>
                'teknisi.a.forged@example.com',

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
                $userA->id,

            'nama' =>
                'Teknisi A',

            'telepon' =>
                '081111111111',

            'alamat' =>
                'Alamat Teknisi A',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI B
        |--------------------------------------------------------------------------
        */

        $userB = User::factory()->create([
            'name' =>
                'Teknisi B',

            'email' =>
                'teknisi.b.forged@example.com',

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
                $userB->id,

            'nama' =>
                'Teknisi B',

            'telepon' =>
                '082222222222',

            'alamat' =>
                'Alamat Teknisi B',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Security Forged',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BK-FORGED-001',

            'nama_barang' =>
                'Router Forged Security',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Security Brand',

            'stok' =>
                10,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI A MENGIRIM TEKNISI_ID MILIK B
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($userA)
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' =>
                        $barang->id,

                    'teknisi_id' =>
                        $teknisiB->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        2,

                    'keperluan' =>
                        'Pengujian forged teknisi_id',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | REQUEST BERHASIL
        |--------------------------------------------------------------------------
        |
        | Tetapi transaksi harus tetap menggunakan Teknisi A
        | dari akun yang sedang login.
        |
        */

        $response->assertRedirect(
            route('barang-keluar.index')
        );


        /*
        |--------------------------------------------------------------------------
        | STOK BERKURANG
        |--------------------------------------------------------------------------
        */

        $barang->refresh();

        $this->assertSame(
            8,
            (int) $barang->stok
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI A
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'barang_id' =>
                    $barang->id,

                'teknisi_id' =>
                    $teknisiA->id,

                'jumlah' =>
                    2,

                'keperluan' =>
                    'Pengujian forged teknisi_id',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH MENJADI MILIK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'barang_id' =>
                    $barang->id,

                'teknisi_id' =>
                    $teknisiB->id,

                'jumlah' =>
                    2,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | HANYA SATU TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            1,
            BarangKeluar::count()
        );


        /*
        |--------------------------------------------------------------------------
        | CEK RECORD
        |--------------------------------------------------------------------------
        */

        $transaksi = BarangKeluar::firstOrFail();

        $this->assertSame(
            $teknisiA->id,
            (int) $transaksi->teknisi_id
        );

        $this->assertNotSame(
            $teknisiB->id,
            (int) $transaksi->teknisi_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE - TEKNISI TIDAK BOLEH MEMALSUKAN TEKNISI_ID
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_memindahkan_kepemilikan_transaksi_ke_teknisi_lain_dengan_teknisi_id_palsu(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI A
        |--------------------------------------------------------------------------
        */

        $userA = User::factory()->create([
            'name' =>
                'Teknisi A Update',

            'email' =>
                'teknisi.a.update.forged@example.com',

            'role' =>
                'teknisi',
        ]);


        $teknisiA = Teknisi::create([
            'user_id' =>
                $userA->id,

            'nama' =>
                'Teknisi A Update',

            'telepon' =>
                '081111111112',

            'alamat' =>
                'Alamat Teknisi A Update',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI B
        |--------------------------------------------------------------------------
        */

        $userB = User::factory()->create([
            'name' =>
                'Teknisi B Update',

            'email' =>
                'teknisi.b.update.forged@example.com',

            'role' =>
                'teknisi',
        ]);


        $teknisiB = Teknisi::create([
            'user_id' =>
                $userB->id,

            'nama' =>
                'Teknisi B Update',

            'telepon' =>
                '082222222223',

            'alamat' =>
                'Alamat Teknisi B Update',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Forged Update',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        |
        | Stok saat ini = 8.
        |
        | Transaksi lama sudah mengeluarkan 2 unit.
        | Jadi stok sebelum transaksi secara logis = 10.
        |
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BK-FORGED-UPD-001',

            'nama_barang' =>
                'Router Forged Update',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Security Update Brand',

            'stok' =>
                8,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang Update Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI A
        |--------------------------------------------------------------------------
        |
        | Jumlah lama = 2.
        |
        */

        $barangKeluar = BarangKeluar::create([
            'barang_id' =>
                $barang->id,

            'teknisi_id' =>
                $teknisiA->id,

            'tanggal' =>
                '2026-10-11',

            'jumlah' =>
                2,

            'pengguna' =>
                $userA->name,

            'keperluan' =>
                'Keperluan lama',

            'foto' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI A MELAKUKAN UPDATE
        |--------------------------------------------------------------------------
        |
        | Request sengaja memasukkan:
        |
        | teknisi_id = Teknisi B
        |
        | sekaligus jumlah diubah:
        |
        | 2 -> 3
        |
        | Perhitungan stok:
        |
        | stok sekarang 8
        | + transaksi lama 2
        | - transaksi baru 3
        | = 7
        |
        */

        $response = $this
            ->actingAs($userA)
            ->put(
                route(
                    'barang-keluar.update',
                    $barangKeluar
                ),
                [
                    'barang_id' =>
                        $barang->id,

                    'teknisi_id' =>
                        $teknisiB->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        3,

                    'keperluan' =>
                        'Keperluan setelah update forged',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE HARUS BERHASIL
        |--------------------------------------------------------------------------
        |
        | Yang ditolak bukan update milik sendiri.
        |
        | Yang harus diabaikan adalah teknisi_id palsu.
        |
        */

        $response->assertRedirect(
            route('barang-keluar.index')
        );


        /*
        |--------------------------------------------------------------------------
        | CEK STOK
        |--------------------------------------------------------------------------
        */

        $barang->refresh();

        $this->assertSame(
            7,
            (int) $barang->stok
        );


        /*
        |--------------------------------------------------------------------------
        | CEK TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $barangKeluar->refresh();


        /*
        | Kepemilikan WAJIB tetap Teknisi A.
        */

        $this->assertSame(
            $teknisiA->id,
            (int) $barangKeluar->teknisi_id
        );


        /*
        | Tidak boleh berubah ke Teknisi B.
        */

        $this->assertNotSame(
            $teknisiB->id,
            (int) $barangKeluar->teknisi_id
        );


        /*
        | Data lain tetap boleh diperbarui.
        */

        $this->assertSame(
            3,
            (int) $barangKeluar->jumlah
        );

        $this->assertSame(
            'Keperluan setelah update forged',
            $barangKeluar->keperluan
        );


        /*
        |--------------------------------------------------------------------------
        | DATABASE HARUS MENUNJUK TEKNISI A
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
                    $teknisiA->id,

                'jumlah' =>
                    3,

                'keperluan' =>
                    'Keperluan setelah update forged',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | DATABASE TIDAK BOLEH MENUNJUK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'id' =>
                    $barangKeluar->id,

                'teknisi_id' =>
                    $teknisiB->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH MEMBUAT TRANSAKSI BARU
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            1,
            BarangKeluar::count()
        );
    }
}