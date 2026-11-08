<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;


    public function test_aktivitas_tambah_barang_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Router',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($admin)
            ->post(
                route('barang.store'),
                [
                    'kode_barang' => 'AUDIT-001',
                    'nama_barang' => 'Router Audit Test',
                    'kategori_id' => $kategoriId,
                    'merk' => 'MikroTik',
                    'stok' => 10,
                    'stok_minimum' => 3,
                    'satuan' => 'pcs',
                    'lokasi' => 'Gudang Audit',
                ]
            );

        $response->assertRedirect(
            route('barang.index')
        );

        $barangId = DB::table('barangs')
            ->where(
                'kode_barang',
                'AUDIT-001'
            )
            ->value('id');

        $this->assertNotNull(
            $barangId
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'barang',
                'subject_id' => $barangId,
            ]
        );
    }


    public function test_aktivitas_update_barang_dicatat_beserta_nilai_lama_dan_baru(): void
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
            'kode_barang' => 'AUDIT-UPD-001',
            'nama_barang' => 'Modem Lama',
            'kategori_id' => $kategoriId,
            'merk' => 'Huawei',
            'stok' => 10,
            'stok_minimum' => 3,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'barang.update',
                    $barang
                ),
                [
                    'kode_barang' => 'AUDIT-UPD-001',
                    'nama_barang' => 'Modem Baru',
                    'kategori_id' => $kategoriId,
                    'merk' => 'Huawei',
                    'stok' => 7,
                    'stok_minimum' => 5,
                    'satuan' => 'pcs',
                    'lokasi' => 'Gudang B',
                ]
            );

        $response->assertRedirect(
            route('barang.index')
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'barang',
                'subject_id' => $barang->id,
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
                'barang'
            )
            ->where(
                'subject_id',
                $barang->id
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'Modem Lama',
            $log->old_values['nama_barang']
        );

        $this->assertEquals(
            10,
            $log->old_values['stok']
        );

        $this->assertEquals(
            3,
            $log->old_values['stok_minimum']
        );

        $this->assertEquals(
            'Gudang A',
            $log->old_values['lokasi']
        );

        $this->assertEquals(
            'Modem Baru',
            $log->new_values['nama_barang']
        );

        $this->assertEquals(
            7,
            $log->new_values['stok']
        );

        $this->assertEquals(
            5,
            $log->new_values['stok_minimum']
        );

        $this->assertEquals(
            'Gudang B',
            $log->new_values['lokasi']
        );
    }


    public function test_aktivitas_hapus_barang_dicatat_dengan_data_sebelum_dihapus(): void
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

        $barang = Barang::create([
            'kode_barang' => 'AUDIT-DEL-001',
            'nama_barang' => 'Switch Akan Dihapus',
            'kategori_id' => $kategoriId,
            'merk' => 'TP-Link',
            'stok' => 4,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Rak Lama',
        ]);

        $barangId = $barang->id;

        $response = $this->actingAs($admin)
            ->delete(
                route(
                    'barang.destroy',
                    $barang
                )
            );

        $response->assertRedirect(
            route('barang.index')
        );

        $this->assertDatabaseMissing(
            'barangs',
            [
                'id' => $barangId,
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'delete',
                'module' => 'barang',
                'subject_id' => $barangId,
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
                'barang'
            )
            ->where(
                'subject_id',
                $barangId
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'AUDIT-DEL-001',
            $log->old_values['kode_barang']
        );

        $this->assertEquals(
            'Switch Akan Dihapus',
            $log->old_values['nama_barang']
        );

        $this->assertEquals(
            4,
            $log->old_values['stok']
        );

        $this->assertEquals(
            2,
            $log->old_values['stok_minimum']
        );

        $this->assertEquals(
            'Rak Lama',
            $log->old_values['lokasi']
        );

        $this->assertNull(
            $log->new_values
        );
    }
}