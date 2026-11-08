<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BarangMasukActivityLogTest extends TestCase
{
    use RefreshDatabase;


    public function test_tambah_barang_masuk_menambah_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Perangkat Jaringan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $barang = Barang::create([
            'kode_barang' => 'BM-AUDIT-001',
            'nama_barang' => 'Router Barang Masuk',
            'kategori_id' => $kategoriId,
            'merk' => 'MikroTik',
            'stok' => 5,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                route('barang-masuk.store'),
                [
                    'barang_id' => $barang->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 3,
                    'supplier' => 'Supplier Test',
                    'keterangan' => 'Pengadaan perangkat',
                ]
            );

        $response->assertRedirect(
            '/barang-masuk'
        );

        $this->assertDatabaseHas(
            'barang_masuks',
            [
                'barang_id' => $barang->id,
                'jumlah' => 3,
                'supplier' => 'Supplier Test',
                'keterangan' => 'Pengadaan perangkat',
            ]
        );

        $barang->refresh();

        $this->assertEquals(
            8,
            $barang->stok
        );

        $barangMasuk = BarangMasuk::where(
            'barang_id',
            $barang->id
        )
            ->where(
                'jumlah',
                3
            )
            ->first();

        $this->assertNotNull(
            $barangMasuk
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'barang_masuk',
                'subject_id' => $barangMasuk->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where(
                'action',
                'create'
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

        $this->assertEquals(
            $barang->id,
            $log->new_values['barang_id']
        );

        $this->assertEquals(
            3,
            $log->new_values['jumlah']
        );

        $this->assertEquals(
            5,
            $log->new_values['stok_sebelum']
        );

        $this->assertEquals(
            8,
            $log->new_values['stok_sesudah']
        );

        $this->assertEquals(
            'Supplier Test',
            $log->new_values['supplier']
        );
    }


    public function test_update_barang_masuk_mengoreksi_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Modem',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $barang = Barang::create([
            'kode_barang' => 'BM-UPD-001',
            'nama_barang' => 'Modem Update Audit',
            'kategori_id' => $kategoriId,
            'merk' => 'Huawei',
            'stok' => 13,
            'stok_minimum' => 3,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang B',
        ]);

        $barangMasuk = BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'supplier' => 'Supplier Lama',
            'keterangan' => 'Pengadaan lama',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'barang-masuk.update',
                    $barangMasuk
                ),
                [
                    'barang_id' => $barang->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 5,
                    'supplier' => 'Supplier Baru',
                    'keterangan' => 'Pengadaan diperbarui',
                ]
            );

        $response->assertRedirect(
            '/barang-masuk'
        );

        $this->assertDatabaseHas(
            'barang_masuks',
            [
                'id' => $barangMasuk->id,
                'barang_id' => $barang->id,
                'jumlah' => 5,
                'supplier' => 'Supplier Baru',
                'keterangan' => 'Pengadaan diperbarui',
            ]
        );

        $barang->refresh();

        $this->assertEquals(
            15,
            $barang->stok
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'barang_masuk',
                'subject_id' => $barangMasuk->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where(
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

        $this->assertEquals(
            3,
            $log->old_values['jumlah']
        );

        $this->assertEquals(
            'Supplier Lama',
            $log->old_values['supplier']
        );

        $this->assertEquals(
            'Pengadaan lama',
            $log->old_values['keterangan']
        );

        $this->assertEquals(
            13,
            $log->old_values['stok_barang']
        );

        $this->assertEquals(
            5,
            $log->new_values['jumlah']
        );

        $this->assertEquals(
            'Supplier Baru',
            $log->new_values['supplier']
        );

        $this->assertEquals(
            'Pengadaan diperbarui',
            $log->new_values['keterangan']
        );

        $this->assertEquals(
            15,
            $log->new_values['stok_barang']
        );
    }


    public function test_hapus_barang_masuk_mengembalikan_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Switch',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | STOK 12 SUDAH TERMASUK TRANSAKSI BARANG MASUK 4 PCS
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' => 'BM-DEL-001',
            'nama_barang' => 'Switch Delete Audit',
            'kategori_id' => $kategoriId,
            'merk' => 'TP-Link',
            'stok' => 12,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang C',
        ]);

        $barangMasuk = BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-11',
            'jumlah' => 4,
            'supplier' => 'Supplier Hapus',
            'keterangan' => 'Transaksi akan dihapus',
        ]);

        $barangMasukId =
            $barangMasuk->id;


        /*
        |--------------------------------------------------------------------------
        | HAPUS TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->delete(
                route(
                    'barang-masuk.destroy',
                    $barangMasuk
                )
            );


        $response->assertRedirect(
            '/barang-masuk'
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI SUDAH HILANG
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_masuks',
            [
                'id' => $barangMasukId,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | STOK DIKEMBALIKAN
        |--------------------------------------------------------------------------
        |
        | Sebelum delete = 12
        | Transaksi lama  = 4
        | Setelah delete  = 8
        |
        */

        $barang->refresh();

        $this->assertEquals(
            8,
            $barang->stok
        );


        /*
        |--------------------------------------------------------------------------
        | AUDIT DELETE
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'delete',
                'module' => 'barang_masuk',
                'subject_id' => $barangMasukId,
            ]
        );


        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where(
                'action',
                'delete'
            )
            ->where(
                'module',
                'barang_masuk'
            )
            ->where(
                'subject_id',
                $barangMasukId
            )
            ->first();


        $this->assertNotNull(
            $log
        );


        /*
        |--------------------------------------------------------------------------
        | DATA SEBELUM DIHAPUS
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            $barang->id,
            $log->old_values['barang_id']
        );

        $this->assertEquals(
            'BM-DEL-001',
            $log->old_values['kode_barang']
        );

        $this->assertEquals(
            'Switch Delete Audit',
            $log->old_values['nama_barang']
        );

        $this->assertEquals(
            4,
            $log->old_values['jumlah']
        );

        $this->assertEquals(
            'Supplier Hapus',
            $log->old_values['supplier']
        );

        $this->assertEquals(
            'Transaksi akan dihapus',
            $log->old_values['keterangan']
        );

        $this->assertEquals(
            12,
            $log->old_values['stok_barang']
        );


        /*
        |--------------------------------------------------------------------------
        | DELETE TIDAK MEMILIKI NEW VALUES
        |--------------------------------------------------------------------------
        */

        $this->assertNull(
            $log->new_values
        );
    }
}