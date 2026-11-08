<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriActivityLogTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function test_tambah_kategori_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Kategori Audit',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                route('kategori.store'),
                [
                    'nama_kategori' =>
                        'Perangkat Fiber Optik',
                ]
            );

        $response->assertRedirect(
            route('kategori.index')
        );

        $this->assertDatabaseHas(
            'kategoris',
            [
                'nama_kategori' =>
                    'Perangkat Fiber Optik',
            ]
        );

        $kategori = Kategori::where(
            'nama_kategori',
            'Perangkat Fiber Optik'
        )->first();

        $this->assertNotNull(
            $kategori
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'kategori',
                'subject_id' => $kategori->id,
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
                'kategori'
            )
            ->where(
                'subject_id',
                $kategori->id
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertNull(
            $log->old_values
        );

        $this->assertEquals(
            'Perangkat Fiber Optik',
            $log->new_values['nama_kategori']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function test_update_kategori_dicatat_beserta_nilai_lama_dan_baru(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Update Kategori',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Router Lama',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'kategori.update',
                    $kategori
                ),
                [
                    'nama_kategori' =>
                        'Router Baru',
                ]
            );

        $response->assertRedirect(
            route('kategori.index')
        );

        $this->assertDatabaseHas(
            'kategoris',
            [
                'id' => $kategori->id,
                'nama_kategori' =>
                    'Router Baru',
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'kategori',
                'subject_id' => $kategori->id,
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
                'kategori'
            )
            ->where(
                'subject_id',
                $kategori->id
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'Router Lama',
            $log->old_values['nama_kategori']
        );

        $this->assertEquals(
            'Router Baru',
            $log->new_values['nama_kategori']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function test_hapus_kategori_dicatat_dengan_data_sebelum_dihapus(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Delete Kategori',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Kategori Akan Dihapus',
        ]);

        $kategoriId =
            $kategori->id;

        $response = $this->actingAs($admin)
            ->delete(
                route(
                    'kategori.destroy',
                    $kategori
                )
            );

        $response->assertRedirect(
            route('kategori.index')
        );

        $this->assertDatabaseMissing(
            'kategoris',
            [
                'id' => $kategoriId,
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'delete',
                'module' => 'kategori',
                'subject_id' => $kategoriId,
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
                'kategori'
            )
            ->where(
                'subject_id',
                $kategoriId
            )
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'Kategori Akan Dihapus',
            $log->old_values['nama_kategori']
        );

        $this->assertNull(
            $log->new_values
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KATEGORI YANG MASIH DIPAKAI TIDAK BOLEH DIHAPUS
    |--------------------------------------------------------------------------
    */

    public function test_kategori_yang_masih_dipakai_barang_tidak_boleh_dihapus_dan_tidak_dicatat_sebagai_delete(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Guard Kategori',
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Kategori Masih Digunakan',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG MENGGUNAKAN KATEGORI
        |--------------------------------------------------------------------------
        */

        Barang::create([
            'kode_barang' =>
                'KAT-GUARD-001',

            'nama_barang' =>
                'Router Guard Kategori',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'MikroTik',

            'stok' =>
                10,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang A',
        ]);


        $kategoriId =
            $kategori->id;


        /*
        |--------------------------------------------------------------------------
        | COBA DELETE
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->delete(
                route(
                    'kategori.destroy',
                    $kategori
                )
            );


        /*
        |--------------------------------------------------------------------------
        | TETAP KEMBALI KE HALAMAN KATEGORI
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route('kategori.index')
        );


        /*
        |--------------------------------------------------------------------------
        | ADA PESAN ERROR
        |--------------------------------------------------------------------------
        */

        $response->assertSessionHas(
            'error'
        );


        /*
        |--------------------------------------------------------------------------
        | KATEGORI TIDAK TERHAPUS
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'kategoris',
            [
                'id' =>
                    $kategoriId,

                'nama_kategori' =>
                    'Kategori Masih Digunakan',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT DELETE
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'user_id' =>
                    $admin->id,

                'action' =>
                    'delete',

                'module' =>
                    'kategori',

                'subject_id' =>
                    $kategoriId,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN BARANG TETAP ADA
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barangs',
            [
                'kode_barang' =>
                    'KAT-GUARD-001',

                'kategori_id' =>
                    $kategoriId,
            ]
        );
    }
}