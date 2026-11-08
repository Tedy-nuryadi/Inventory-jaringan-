<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangMasukTransferValidationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG MASUK DARI BARANG A KE BARANG B
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_masuk_ke_barang_lain_mengoreksi_kedua_stok_dengan_benar(): void
    {
        $admin = User::factory()->create([
            'name' =>
                'Admin Transfer Barang Masuk',

            'email' =>
                'admin.transfer.masuk@example.com',

            'role' =>
                'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Transfer Barang Masuk',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG A
        |--------------------------------------------------------------------------
        |
        | Stok sekarang = 15
        | Transaksi lama = 5
        |
        */

        $barangA = Barang::create([
            'kode_barang' =>
                'BM-TRANSFER-A-001',

            'nama_barang' =>
                'Router Transfer A',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Brand A',

            'stok' =>
                15,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang A',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG B
        |--------------------------------------------------------------------------
        |
        | Stok awal = 4
        |
        */

        $barangB = Barang::create([
            'kode_barang' =>
                'BM-TRANSFER-B-001',

            'nama_barang' =>
                'Switch Transfer B',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Brand B',

            'stok' =>
                4,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang B',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI LAMA
        |--------------------------------------------------------------------------
        */

        $barangMasuk = BarangMasuk::create([
            'barang_id' =>
                $barangA->id,

            'tanggal' =>
                '2026-10-10',

            'jumlah' =>
                5,

            'supplier' =>
                'Supplier Lama',

            'keterangan' =>
                'Transaksi lama pada Barang A',
        ]);


        $jumlahTransaksiSebelum =
            BarangMasuk::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | PINDAH A -> B
        |--------------------------------------------------------------------------
        |
        | A = 15 - 5 = 10
        | B = 4 + 7  = 11
        |
        */

        $response = $this
            ->actingAs($admin)
            ->put(
                route(
                    'barang-masuk.update',
                    $barangMasuk
                ),
                [
                    'barang_id' =>
                        $barangB->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        7,

                    'supplier' =>
                        'Supplier Baru',

                    'keterangan' =>
                        'Transaksi dipindahkan ke Barang B',
                ]
            );


        $response->assertRedirect(
            route('barang-masuk.index')
        );


        /*
        |--------------------------------------------------------------------------
        | CEK BARANG A
        |--------------------------------------------------------------------------
        */

        $barangA->refresh();

        $this->assertSame(
            10,
            (int) $barangA->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barangA->id,

                'stok' =>
                    10,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | CEK BARANG B
        |--------------------------------------------------------------------------
        */

        $barangB->refresh();

        $this->assertSame(
            11,
            (int) $barangB->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barangB->id,

                'stok' =>
                    11,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | CEK TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $barangMasuk->refresh();

        $this->assertSame(
            $barangB->id,
            (int) $barangMasuk->barang_id
        );

        $this->assertSame(
            7,
            (int) $barangMasuk->jumlah
        );

        $this->assertSame(
            'Supplier Baru',
            $barangMasuk->supplier
        );

        $this->assertSame(
            'Transaksi dipindahkan ke Barang B',
            $barangMasuk->keterangan
        );


        $this->assertDatabaseHas(
            'barang_masuks',
            [
                'id' =>
                    $barangMasuk->id,

                'barang_id' =>
                    $barangB->id,

                'jumlah' =>
                    7,

                'supplier' =>
                    'Supplier Baru',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH TRANSAKSI TETAP
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangMasuk::count()
        );


        /*
        |--------------------------------------------------------------------------
        | AUDIT UPDATE
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum + 1,
            ActivityLog::count()
        );


        $log = ActivityLog::where(
            'action',
            'update'
        )
            ->where(
                'module',
                'barang_masuk'
            )
            ->where(
                'subject_id',
                $barangMasuk->id
            )
            ->first();


        $this->assertNotNull(
            $log
        );


        /*
        |--------------------------------------------------------------------------
        | OLD VALUES
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            $barangA->id,
            $log->old_values['barang_id']
        );

        $this->assertEquals(
            5,
            $log->old_values['jumlah']
        );

        $this->assertEquals(
            15,
            $log->old_values['stok_barang']
        );


        /*
        |--------------------------------------------------------------------------
        | NEW VALUES
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            $barangB->id,
            $log->new_values['barang_id']
        );

        $this->assertEquals(
            7,
            $log->new_values['jumlah']
        );

        $this->assertEquals(
            11,
            $log->new_values['stok_barang']
        );

        $this->assertEquals(
            10,
            $log->new_values['stok_barang_lama_sesudah']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSFER HARUS ROLLBACK JIKA STOK BARANG LAMA SUDAH TERPAKAI
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_masuk_ke_barang_lain_ditolak_dan_rollback_jika_stok_barang_lama_tidak_cukup(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' =>
                'Admin Transfer Rollback',

            'email' =>
                'admin.transfer.rollback@example.com',

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
                'Perangkat Transfer Rollback',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG A
        |--------------------------------------------------------------------------
        |
        | Stok sekarang hanya 3.
        | Transaksi lama = 5.
        |
        | Jika transaksi dipindahkan:
        |
        | 3 - 5 = -2
        |
        | Maka operasi harus ditolak.
        |
        */

        $barangA = Barang::create([
            'kode_barang' =>
                'BM-ROLLBACK-A-001',

            'nama_barang' =>
                'Router Rollback A',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Rollback Brand A',

            'stok' =>
                3,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang Rollback A',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG B
        |--------------------------------------------------------------------------
        */

        $barangB = Barang::create([
            'kode_barang' =>
                'BM-ROLLBACK-B-001',

            'nama_barang' =>
                'Switch Rollback B',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Rollback Brand B',

            'stok' =>
                8,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang Rollback B',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI LAMA
        |--------------------------------------------------------------------------
        */

        $barangMasuk = BarangMasuk::create([
            'barang_id' =>
                $barangA->id,

            'tanggal' =>
                '2026-10-10',

            'jumlah' =>
                5,

            'supplier' =>
                'Supplier Original',

            'keterangan' =>
                'Transaksi original pada Barang A',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT AWAL
        |--------------------------------------------------------------------------
        */

        $stokASebelum =
            (int) $barangA->stok;

        $stokBSebelum =
            (int) $barangB->stok;

        $barangIdSebelum =
            (int) $barangMasuk->barang_id;

        $jumlahSebelum =
            (int) $barangMasuk->jumlah;

        $tanggalSebelum =
            $barangMasuk->tanggal;

        $supplierSebelum =
            $barangMasuk->supplier;

        $keteranganSebelum =
            $barangMasuk->keterangan;

        $jumlahTransaksiSebelum =
            BarangMasuk::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | COBA PINDAHKAN KE BARANG B
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'barang-masuk.edit',
                    $barangMasuk
                )
            )
            ->put(
                route(
                    'barang-masuk.update',
                    $barangMasuk
                ),
                [
                    'barang_id' =>
                        $barangB->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        7,

                    'supplier' =>
                        'Supplier Baru',

                    'keterangan' =>
                        'Percobaan transfer ilegal',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS DITOLAK
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route(
                'barang-masuk.edit',
                $barangMasuk
            )
        );

        $response->assertSessionHasErrors(
            'jumlah'
        );


        /*
        |--------------------------------------------------------------------------
        | STOK BARANG A TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $barangA->refresh();

        $this->assertSame(
            $stokASebelum,
            (int) $barangA->stok
        );

        $this->assertSame(
            3,
            (int) $barangA->stok
        );


        /*
        |--------------------------------------------------------------------------
        | STOK BARANG B TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $barangB->refresh();

        $this->assertSame(
            $stokBSebelum,
            (int) $barangB->stok
        );

        $this->assertSame(
            8,
            (int) $barangB->stok
        );


        /*
        |--------------------------------------------------------------------------
        | DATABASE STOK TETAP
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barangA->id,

                'stok' =>
                    3,
            ]
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barangB->id,

                'stok' =>
                    8,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $barangMasuk->refresh();

        $this->assertSame(
            $barangIdSebelum,
            (int) $barangMasuk->barang_id
        );

        $this->assertSame(
            $barangA->id,
            (int) $barangMasuk->barang_id
        );

        $this->assertSame(
            $jumlahSebelum,
            (int) $barangMasuk->jumlah
        );

        $this->assertSame(
            5,
            (int) $barangMasuk->jumlah
        );

        $this->assertEquals(
            $tanggalSebelum,
            $barangMasuk->tanggal
        );

        $this->assertSame(
            $supplierSebelum,
            $barangMasuk->supplier
        );

        $this->assertSame(
            $keteranganSebelum,
            $barangMasuk->keterangan
        );


        /*
        |--------------------------------------------------------------------------
        | DATA BARU TIDAK BOLEH TERSIMPAN
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_masuks',
            [
                'id' =>
                    $barangMasuk->id,

                'barang_id' =>
                    $barangB->id,

                'jumlah' =>
                    7,

                'supplier' =>
                    'Supplier Baru',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH TRANSAKSI TETAP
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangMasuk::count()
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


        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'action' =>
                    'update',

                'module' =>
                    'barang_masuk',

                'subject_id' =>
                    $barangMasuk->id,
            ]
        );
    }
}