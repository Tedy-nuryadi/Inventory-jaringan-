<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BOLEH MEMBUKA HALAMAN AUDIT ACTIVITY
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_membuka_halaman_audit_activity(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Audit Page',
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/activity-logs');

        $response->assertOk();
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MEMBUKA HALAMAN AUDIT ACTIVITY
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_membuka_halaman_audit_activity(): void
    {
        $teknisi = User::factory()->create([
            'name' => 'Teknisi Audit Page',
            'role' => 'teknisi',
        ]);

        $response = $this
            ->actingAs($teknisi)
            ->get('/activity-logs');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN MENAMPILKAN DATA AUDIT
    |--------------------------------------------------------------------------
    */

    public function test_halaman_audit_activity_menampilkan_data_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Pemeriksa',
            'email' => 'admin.pemeriksa@example.com',
            'role' => 'admin',
        ]);

        $log = ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'barang',
            'subject_id' => 10,
            'description' => 'Memperbarui barang: Router Mikrotik',

            'old_values' => [
                'nama' => 'Router Lama',
            ],

            'new_values' => [
                'nama' => 'Router Mikrotik',
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route('activity-logs.index')
            );

        $response->assertOk();

        $response->assertSee(
            'Audit Activity'
        );

        $response->assertSee(
            $log->created_at->format('d/m/Y H:i')
        );

        $response->assertSee(
            'Admin Pemeriksa'
        );

        $response->assertSee(
            'update'
        );

        $response->assertSee(
            'barang'
        );

        $response->assertSee(
            'Memperbarui barang: Router Mikrotik'
        );

        $response->assertSee(
            '127.0.0.1'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER ACTION
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_audit_activity_berdasarkan_action(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Filter Action',
            'email' => 'admin.filter.action@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'create',
            'module' => 'barang',
            'subject_id' => 1,
            'description' => 'LOG CREATE KHUSUS FILTER',
            'old_values' => null,

            'new_values' => [
                'nama' => 'Barang Baru',
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'delete',
            'module' => 'barang',
            'subject_id' => 2,
            'description' => 'LOG DELETE TIDAK BOLEH TAMPIL',

            'old_values' => [
                'nama' => 'Barang Dihapus',
            ],

            'new_values' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'activity-logs.index',
                    [
                        'action' => 'create',
                    ]
                )
            );

        $response->assertOk();

        $response->assertSee(
            'LOG CREATE KHUSUS FILTER'
        );

        $response->assertDontSee(
            'LOG DELETE TIDAK BOLEH TAMPIL'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER MODULE
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_audit_activity_berdasarkan_module(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Filter Module',
            'email' => 'admin.filter.module@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'barang',
            'subject_id' => 11,
            'description' => 'LOG BARANG KHUSUS FILTER MODULE',

            'old_values' => [
                'nama' => 'Router Lama',
            ],

            'new_values' => [
                'nama' => 'Router Baru',
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'kategori',
            'subject_id' => 12,
            'description' => 'LOG KATEGORI TIDAK BOLEH TAMPIL',

            'old_values' => [
                'nama' => 'Kategori Lama',
            ],

            'new_values' => [
                'nama' => 'Kategori Baru',
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'activity-logs.index',
                    [
                        'module' => 'barang',
                    ]
                )
            );

        $response->assertOk();

        $response->assertSee(
            'LOG BARANG KHUSUS FILTER MODULE'
        );

        $response->assertDontSee(
            'LOG KATEGORI TIDAK BOLEH TAMPIL'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER USER
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_audit_activity_berdasarkan_user(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Filter User',
            'email' => 'admin.filter.user@example.com',
            'role' => 'admin',
        ]);

        $userPertama = User::factory()->create([
            'name' => 'User Pertama',
            'email' => 'user.pertama@example.com',
            'role' => 'admin',
        ]);

        $userKedua = User::factory()->create([
            'name' => 'User Kedua',
            'email' => 'user.kedua@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::create([
            'user_id' => $userPertama->id,
            'action' => 'create',
            'module' => 'kategori',
            'subject_id' => 21,
            'description' => 'LOG USER PERTAMA HARUS TAMPIL',
            'old_values' => null,

            'new_values' => [
                'nama' => 'Kategori User Pertama',
            ],

            'ip_address' => '10.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        ActivityLog::create([
            'user_id' => $userKedua->id,
            'action' => 'create',
            'module' => 'kategori',
            'subject_id' => 22,
            'description' => 'LOG USER KEDUA TIDAK BOLEH TAMPIL',
            'old_values' => null,

            'new_values' => [
                'nama' => 'Kategori User Kedua',
            ],

            'ip_address' => '10.0.0.2',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'activity-logs.index',
                    [
                        'user_id' => $userPertama->id,
                    ]
                )
            );

        $response->assertOk();

        $response->assertSee(
            'LOG USER PERTAMA HARUS TAMPIL'
        );

        $response->assertDontSee(
            'LOG USER KEDUA TIDAK BOLEH TAMPIL'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER RENTANG TANGGAL
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memfilter_audit_activity_berdasarkan_rentang_tanggal(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Filter Tanggal',
            'email' => 'admin.filter.tanggal@example.com',
            'role' => 'admin',
        ]);

        $logDalamRentang = ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'barang',
            'subject_id' => 31,
            'description' => 'LOG TANGGAL HARUS TAMPIL',

            'old_values' => [
                'stok' => 10,
            ],

            'new_values' => [
                'stok' => 20,
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $logDalamRentang->forceFill([
            'created_at' => '2026-10-10 10:00:00',
            'updated_at' => '2026-10-10 10:00:00',
        ])->save();

        $logDiluarRentang = ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'barang',
            'subject_id' => 32,
            'description' => 'LOG TANGGAL TIDAK BOLEH TAMPIL',

            'old_values' => [
                'stok' => 20,
            ],

            'new_values' => [
                'stok' => 30,
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $logDiluarRentang->forceFill([
            'created_at' => '2026-10-12 10:00:00',
            'updated_at' => '2026-10-12 10:00:00',
        ])->save();

        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'activity-logs.index',
                    [
                        'date_from' => '2026-10-09',
                        'date_to' => '2026-10-10',
                    ]
                )
            );

        $response->assertOk();

        $response->assertSee(
            'LOG TANGGAL HARUS TAMPIL'
        );

        $response->assertDontSee(
            'LOG TANGGAL TIDAK BOLEH TAMPIL'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UI FILTER
    |--------------------------------------------------------------------------
    */

    public function test_halaman_audit_activity_menampilkan_form_filter_lengkap(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin UI Filter',
            'email' => 'admin.ui.filter@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User Untuk Filter',
            'email' => 'user.filter@example.com',
            'role' => 'teknisi',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'create',
            'module' => 'barang',
            'subject_id' => 50,
            'description' => 'Log Untuk Membentuk Pilihan Modul',
            'old_values' => null,

            'new_values' => [
                'nama' => 'Router',
            ],

            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(
                route('activity-logs.index')
            );

        $response->assertOk();

        $response->assertSee(
            'Filter Aktivitas'
        );

        $response->assertSee(
            'name="user_id"',
            false
        );

        $response->assertSee(
            'User Untuk Filter'
        );

        $response->assertSee(
            'name="action"',
            false
        );

        $response->assertSee(
            'name="module"',
            false
        );

        $response->assertSee(
            'name="date_from"',
            false
        );

        $response->assertSee(
            'name="date_to"',
            false
        );

        $response->assertSee(
            'Terapkan Filter'
        );

        $response->assertSee(
            'Reset'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL PERUBAHAN OLD VALUES -> NEW VALUES
    |--------------------------------------------------------------------------
    */

    public function test_halaman_audit_activity_menampilkan_detail_perubahan_nilai_lama_dan_baru(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' => 'Admin Detail Audit',
            'email' => 'admin.detail.audit@example.com',
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOG UPDATE
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'update',
            'module' => 'barang',
            'subject_id' => 99,

            'description' =>
                'Memperbarui barang khusus detail audit',

            'old_values' => [
                'nama' =>
                    'ROUTER-LAMA-UNIK',

                'lokasi' =>
                    'GUDANG-LAMA-UNIK',
            ],

            'new_values' => [
                'nama' =>
                    'ROUTER-BARU-UNIK',

                'lokasi' =>
                    'GUDANG-BARU-UNIK',
            ],

            'ip_address' =>
                '192.168.50.10',

            'user_agent' =>
                'PHPUnit Detail Browser',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUKA HALAMAN
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->get(
                route('activity-logs.index')
            );


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | DETAIL PERUBAHAN
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'Detail Perubahan'
        );


        /*
        |--------------------------------------------------------------------------
        | LABEL SEBELUM DAN SESUDAH
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'Sebelum'
        );

        $response->assertSee(
            'Sesudah'
        );


        /*
        |--------------------------------------------------------------------------
        | NILAI LAMA
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'ROUTER-LAMA-UNIK'
        );

        $response->assertSee(
            'GUDANG-LAMA-UNIK'
        );


        /*
        |--------------------------------------------------------------------------
        | NILAI BARU
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'ROUTER-BARU-UNIK'
        );

        $response->assertSee(
            'GUDANG-BARU-UNIK'
        );
    }
}