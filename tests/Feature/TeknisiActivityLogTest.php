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

class TeknisiActivityLogTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | CREATE TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_tambah_teknisi_dicatat_ke_audit_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Teknisi Audit',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                '/teknisi',
                [
                    'nama' => 'Teknisi Fiber',
                    'telepon' => '081234567890',
                    'alamat' => 'Tangerang',
                ]
            );

        $response->assertRedirect('/teknisi');

        $this->assertDatabaseHas(
            'teknisis',
            [
                'nama' => 'Teknisi Fiber',
                'telepon' => '081234567890',
                'alamat' => 'Tangerang',
            ]
        );

        $teknisi = Teknisi::where(
            'nama',
            'Teknisi Fiber'
        )->first();

        $this->assertNotNull($teknisi);

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'teknisi',
                'subject_id' => $teknisi->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'create')
            ->where('module', 'teknisi')
            ->where('subject_id', $teknisi->id)
            ->first();

        $this->assertNotNull($log);

        $this->assertNull(
            $log->old_values
        );

        $this->assertEquals(
            'Teknisi Fiber',
            $log->new_values['nama']
        );

        $this->assertEquals(
            '081234567890',
            $log->new_values['telepon']
        );

        $this->assertEquals(
            'Tangerang',
            $log->new_values['alamat']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_update_teknisi_dicatat_beserta_nilai_lama_dan_baru(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Update Teknisi',
            'role' => 'admin',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Lama',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang Lama',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                '/teknisi/' . $teknisi->id,
                [
                    'nama' => 'Teknisi Baru',
                    'telepon' => '082222222222',
                    'alamat' => 'Tangerang Baru',
                ]
            );

        $response->assertRedirect('/teknisi');

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisi->id,
                'nama' => 'Teknisi Baru',
                'telepon' => '082222222222',
                'alamat' => 'Tangerang Baru',
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'teknisi',
                'subject_id' => $teknisi->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'update')
            ->where('module', 'teknisi')
            ->where('subject_id', $teknisi->id)
            ->first();

        $this->assertNotNull($log);

        $this->assertEquals(
            'Teknisi Lama',
            $log->old_values['nama']
        );

        $this->assertEquals(
            '081111111111',
            $log->old_values['telepon']
        );

        $this->assertEquals(
            'Tangerang Lama',
            $log->old_values['alamat']
        );

        $this->assertEquals(
            'Teknisi Baru',
            $log->new_values['nama']
        );

        $this->assertEquals(
            '082222222222',
            $log->new_values['telepon']
        );

        $this->assertEquals(
            'Tangerang Baru',
            $log->new_values['alamat']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_hapus_teknisi_dicatat_dengan_data_sebelum_dihapus(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Delete Teknisi',
            'role' => 'admin',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Akan Dihapus',
            'telepon' => '083333333333',
            'alamat' => 'Serang',
        ]);

        $teknisiId =
            $teknisi->id;

        $response = $this->actingAs($admin)
            ->delete(
                '/teknisi/' . $teknisiId
            );

        $response->assertRedirect('/teknisi');

        $this->assertDatabaseMissing(
            'teknisis',
            [
                'id' => $teknisiId,
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'delete',
                'module' => 'teknisi',
                'subject_id' => $teknisiId,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'delete')
            ->where('module', 'teknisi')
            ->where('subject_id', $teknisiId)
            ->first();

        $this->assertNotNull($log);

        $this->assertEquals(
            'Teknisi Akan Dihapus',
            $log->old_values['nama']
        );

        $this->assertEquals(
            '083333333333',
            $log->old_values['telepon']
        );

        $this->assertEquals(
            'Serang',
            $log->old_values['alamat']
        );

        $this->assertNull(
            $log->new_values
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARD: MASIH TERHUBUNG USER
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_yang_masih_terhubung_user_tidak_boleh_dihapus_dan_tidak_dicatat_sebagai_delete(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Guard Teknisi',
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'name' => 'User Teknisi Aktif',
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Masih Terhubung',
            'telepon' => '081234567899',
            'alamat' => 'Tangerang',
        ]);

        $teknisiId =
            $teknisi->id;

        $response = $this->actingAs($admin)
            ->delete(
                '/teknisi/' . $teknisiId
            );

        $response->assertRedirect('/teknisi');

        $response->assertSessionHas(
            'error'
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisiId,
                'user_id' => $userTeknisi->id,
                'nama' => 'Teknisi Masih Terhubung',
            ]
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $userTeknisi->id,
                'role' => 'teknisi',
            ]
        );

        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'delete',
                'module' => 'teknisi',
                'subject_id' => $teknisiId,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARD: MASIH MEMILIKI RIWAYAT BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_yang_memiliki_riwayat_barang_keluar_tidak_boleh_dihapus_dan_tidak_dicatat_sebagai_delete(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Guard Riwayat',
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI TANPA USER
        |--------------------------------------------------------------------------
        |
        | user_id sengaja NULL agar guard sebelumnya
        | tidak menjadi penyebab penolakan.
        |
        */

        $teknisi = Teknisi::create([
            'user_id' => null,
            'nama' => 'Teknisi Riwayat',
            'telepon' => '084444444444',
            'alamat' => 'Cikupa',
        ]);

        $teknisiId =
            $teknisi->id;


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' =>
                    'Perangkat Riwayat',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'TEK-RWY-001',

            'nama_barang' =>
                'Router Riwayat Teknisi',

            'kategori_id' =>
                $kategoriId,

            'merk' =>
                'MikroTik',

            'stok' =>
                7,

            'stok_minimum' =>
                2,

            'satuan' =>
                'pcs',

            'lokasi' =>
                'Gudang A',
        ]);


        /*
        |--------------------------------------------------------------------------
        | RIWAYAT BARANG KELUAR
        |--------------------------------------------------------------------------
        */

        $barangKeluar = BarangKeluar::create([
            'barang_id' =>
                $barang->id,

            'teknisi_id' =>
                $teknisiId,

            'tanggal' =>
                '2026-10-12',

            'jumlah' =>
                3,

            'pengguna' =>
                'Admin Riwayat',

            'keperluan' =>
                'Instalasi pelanggan',

            'foto' =>
                null,
        ]);

        $barangKeluarId =
            $barangKeluar->id;


        /*
        |--------------------------------------------------------------------------
        | COBA HAPUS TEKNISI
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->delete(
                '/teknisi/' . $teknisiId
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS DITOLAK
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            '/teknisi'
        );

        $response->assertSessionHas(
            'error'
        );


        /*
        |--------------------------------------------------------------------------
        | TEKNISI TETAP ADA
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisiId,

                'nama' =>
                    'Teknisi Riwayat',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | RIWAYAT TETAP TERHUBUNG KE TEKNISI
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' =>
                    $barangKeluarId,

                'teknisi_id' =>
                    $teknisiId,

                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    3,
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
                    'teknisi',

                'subject_id' =>
                    $teknisiId,
            ]
        );
    }
}