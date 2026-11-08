<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangMasukDeleteValidationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | DELETE BARANG MASUK TIDAK BOLEH MEMBUAT STOK NEGATIF
    |--------------------------------------------------------------------------
    */

    public function test_hapus_barang_masuk_ditolak_jika_membuat_stok_menjadi_negatif(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' =>
                'Admin Delete Security',

            'email' =>
                'admin.delete.security@example.com',

            'role' =>
                'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Kategori Delete Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        |
        | Stok saat ini hanya 3.
        |
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BM-DELETE-SEC-001',

            'nama_barang' =>
                'Router Delete Security',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Security Brand',

            'stok' =>
                3,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MASUK LAMA = 5
        |--------------------------------------------------------------------------
        |
        | Jika transaksi ini dihapus:
        |
        | 3 - 5 = -2
        |
        | Maka DELETE wajib ditolak.
        |
        */

        $barangMasuk = BarangMasuk::create([
            'barang_id' =>
                $barang->id,

            'tanggal' =>
                '2026-10-10',

            'jumlah' =>
                5,

            'supplier' =>
                'Supplier Security',

            'keterangan' =>
                'Transaksi yang stoknya sudah sebagian terpakai',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT
        |--------------------------------------------------------------------------
        */

        $stokSebelum =
            (int) $barang->stok;

        $jumlahTransaksiSebelum =
            BarangMasuk::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | COBA DELETE
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->from(
                route('barang-masuk.index')
            )
            ->delete(
                route(
                    'barang-masuk.destroy',
                    $barangMasuk
                )
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS DITOLAK
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route('barang-masuk.index')
        );

        $response->assertSessionHasErrors(
            'jumlah'
        );


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

        $this->assertSame(
            3,
            (int) $barang->stok
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barang->id,

                'stok' =>
                    3,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TIDAK BOLEH TERHAPUS
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barang_masuks',
            [
                'id' =>
                    $barangMasuk->id,

                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    5,

                'supplier' =>
                    'Supplier Security',
            ]
        );


        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangMasuk::count()
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT DELETE PALSU
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );


        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'action' =>
                    'delete',

                'module' =>
                    'barang_masuk',

                'subject_id' =>
                    $barangMasuk->id,
            ]
        );
    }
}