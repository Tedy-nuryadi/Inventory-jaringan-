<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BarangKeluarActivityLogTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | CREATE BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_tambah_barang_keluar_mengurangi_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Audit',
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'name' => 'Teknisi Audit',
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Audit',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Perangkat Jaringan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $barang = Barang::create([
            'kode_barang' => 'BK-AUDIT-001',
            'nama_barang' => 'Router Barang Keluar',
            'kategori_id' => $kategoriId,
            'merk' => 'MikroTik',
            'stok' => 10,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 3,
                    'keperluan' => 'Pemasangan jaringan pelanggan',
                ]
            );

        $response->assertRedirect(
            route('barang-keluar.index')
        );

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'barang_id' => $barang->id,
                'teknisi_id' => $teknisi->id,
                'jumlah' => 3,
                'pengguna' => 'Admin Audit',
                'keperluan' => 'Pemasangan jaringan pelanggan',
            ]
        );

        $barang->refresh();

        $this->assertEquals(
            7,
            $barang->stok
        );

        $barangKeluar = BarangKeluar::where(
            'barang_id',
            $barang->id
        )
            ->where(
                'teknisi_id',
                $teknisi->id
            )
            ->where(
                'jumlah',
                3
            )
            ->first();

        $this->assertNotNull(
            $barangKeluar
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'barang_keluar',
                'subject_id' => $barangKeluar->id,
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
                'barang_keluar'
            )
            ->where(
                'subject_id',
                $barangKeluar->id
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
            $teknisi->id,
            $log->new_values['teknisi_id']
        );

        $this->assertEquals(
            3,
            $log->new_values['jumlah']
        );

        $this->assertEquals(
            10,
            $log->new_values['stok_sebelum']
        );

        $this->assertEquals(
            7,
            $log->new_values['stok_sesudah']
        );

        $this->assertEquals(
            'Pemasangan jaringan pelanggan',
            $log->new_values['keperluan']
        );

        $this->assertEquals(
            'Admin Audit',
            $log->new_values['pengguna']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_keluar_mengoreksi_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Update Audit',
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'name' => 'Teknisi Update',
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Update',
            'telepon' => '081298765432',
            'alamat' => 'Tangerang',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Modem',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $barang = Barang::create([
            'kode_barang' => 'BK-UPD-001',
            'nama_barang' => 'Modem Update Audit',
            'kategori_id' => $kategoriId,
            'merk' => 'Huawei',
            'stok' => 7,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang B',
        ]);

        $barangKeluar = BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Admin Lama',
            'keperluan' => 'Instalasi lama',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'barang-keluar.update',
                    $barangKeluar
                ),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 5,
                    'keperluan' => 'Instalasi diperbarui',
                ]
            );

        $response->assertRedirect(
            route('barang-keluar.index')
        );

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'barang_id' => $barang->id,
                'teknisi_id' => $teknisi->id,
                'jumlah' => 5,
                'pengguna' => 'Admin Update Audit',
                'keperluan' => 'Instalasi diperbarui',
            ]
        );

        $barang->refresh();

        $this->assertEquals(
            5,
            $barang->stok
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'barang_keluar',
                'subject_id' => $barangKeluar->id,
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
                'barang_keluar'
            )
            ->where(
                'subject_id',
                $barangKeluar->id
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            $barang->id,
            $log->old_values['barang_id']
        );

        $this->assertEquals(
            $teknisi->id,
            $log->old_values['teknisi_id']
        );

        $this->assertEquals(
            3,
            $log->old_values['jumlah']
        );

        $this->assertEquals(
            'Instalasi lama',
            $log->old_values['keperluan']
        );

        $this->assertEquals(
            7,
            $log->old_values['stok_barang']
        );

        $this->assertEquals(
            $barang->id,
            $log->new_values['barang_id']
        );

        $this->assertEquals(
            $teknisi->id,
            $log->new_values['teknisi_id']
        );

        $this->assertEquals(
            5,
            $log->new_values['jumlah']
        );

        $this->assertEquals(
            'Instalasi diperbarui',
            $log->new_values['keperluan']
        );

        $this->assertEquals(
            5,
            $log->new_values['stok_barang']
        );

        $this->assertEquals(
            'Admin Update Audit',
            $log->new_values['pengguna']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_hapus_barang_keluar_mengembalikan_stok_dan_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Delete Audit',
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'name' => 'Teknisi Delete',
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Delete',
            'telepon' => '081277788899',
            'alamat' => 'Tangerang',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Switch',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | STOK 6 SUDAH MEMPERHITUNGKAN TRANSAKSI KELUAR 4 PCS
        |--------------------------------------------------------------------------
        |
        | Stok awal sebelum barang keluar = 10
        | Barang keluar                  = 4
        | Stok saat ini                  = 6
        |
        */

        $barang = Barang::create([
            'kode_barang' => 'BK-DEL-001',
            'nama_barang' => 'Switch Delete Audit',
            'kategori_id' => $kategoriId,
            'merk' => 'TP-Link',
            'stok' => 6,
            'stok_minimum' => 2,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang C',
        ]);

        $barangKeluar = BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-11',
            'jumlah' => 4,
            'pengguna' => 'Admin Lama',
            'keperluan' => 'Pemasangan switch pelanggan',
            'foto' => null,
        ]);

        $barangKeluarId =
            $barangKeluar->id;


        /*
        |--------------------------------------------------------------------------
        | DELETE TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->delete(
                route(
                    'barang-keluar.destroy',
                    $barangKeluar
                )
            );


        $response->assertRedirect(
            route('barang-keluar.index')
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI SUDAH TERHAPUS
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'id' => $barangKeluarId,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | STOK KEMBALI
        |--------------------------------------------------------------------------
        |
        | Stok sebelum delete = 6
        | Dikembalikan        = 4
        | Stok akhir          = 10
        |
        */

        $barang->refresh();

        $this->assertEquals(
            10,
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
                'module' => 'barang_keluar',
                'subject_id' => $barangKeluarId,
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
                'barang_keluar'
            )
            ->where(
                'subject_id',
                $barangKeluarId
            )
            ->first();


        $this->assertNotNull(
            $log
        );


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT SEBELUM DELETE
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            $barang->id,
            $log->old_values['barang_id']
        );

        $this->assertEquals(
            'BK-DEL-001',
            $log->old_values['kode_barang']
        );

        $this->assertEquals(
            'Switch Delete Audit',
            $log->old_values['nama_barang']
        );

        $this->assertEquals(
            $teknisi->id,
            $log->old_values['teknisi_id']
        );

        $this->assertEquals(
            4,
            $log->old_values['jumlah']
        );

        $this->assertEquals(
            'Admin Lama',
            $log->old_values['pengguna']
        );

        $this->assertEquals(
            'Pemasangan switch pelanggan',
            $log->old_values['keperluan']
        );

        $this->assertEquals(
            6,
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