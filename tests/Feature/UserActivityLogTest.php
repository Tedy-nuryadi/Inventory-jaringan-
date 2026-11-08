<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | CREATE USER
    |--------------------------------------------------------------------------
    */

    public function test_tambah_user_dicatat_ke_audit_log_tanpa_menyimpan_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Audit User',
            'email' => 'admin.audit@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                route('users.store'),
                [
                    'name' => 'Admin Baru',
                    'email' => 'admin.baru@example.com',
                    'password' => 'Password123!',
                    'password_confirmation' => 'Password123!',
                    'role' => 'admin',
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'name' => 'Admin Baru',
                'email' => 'admin.baru@example.com',
                'role' => 'admin',
            ]
        );

        $userBaru = User::where(
            'email',
            'admin.baru@example.com'
        )->first();

        $this->assertNotNull(
            $userBaru
        );

        $this->assertTrue(
            Hash::check(
                'Password123!',
                $userBaru->password
            )
        );

        $this->assertNotEquals(
            'Password123!',
            $userBaru->password
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'user',
                'subject_id' => $userBaru->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'create')
            ->where('module', 'user')
            ->where('subject_id', $userBaru->id)
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertNull(
            $log->old_values
        );

        $this->assertEquals(
            'Admin Baru',
            $log->new_values['name']
        );

        $this->assertEquals(
            'admin.baru@example.com',
            $log->new_values['email']
        );

        $this->assertEquals(
            'admin',
            $log->new_values['role']
        );

        $this->assertArrayNotHasKey(
            'password',
            $log->new_values
        );

        $this->assertArrayNotHasKey(
            'password_confirmation',
            $log->new_values
        );

        $this->assertStringNotContainsString(
            'Password123!',
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $userBaru->password,
            $log->description ?? ''
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER: ADMIN -> TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_update_user_menjadi_teknisi_dicatat_beserta_role_dan_profil_lama_baru_tanpa_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Pengelola',
            'email' => 'admin.pengelola@example.com',
            'role' => 'admin',
        ]);

        $passwordAsli =
            'TargetPassword123!';

        $userTarget = User::factory()->create([
            'name' => 'User Target',
            'email' => 'target@example.com',
            'password' => Hash::make($passwordAsli),
            'role' => 'admin',
        ]);

        $hashPasswordSebelum =
            $userTarget->password;

        $teknisi = Teknisi::create([
            'user_id' => null,
            'nama' => 'Teknisi Target',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => $teknisi->id,
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $userTarget->id,
                'name' => 'User Target',
                'email' => 'target@example.com',
                'role' => 'teknisi',
            ]
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisi->id,
                'user_id' => $userTarget->id,
                'nama' => 'Teknisi Target',
            ]
        );

        $userTarget->refresh();

        $this->assertEquals(
            $hashPasswordSebelum,
            $userTarget->password
        );

        $this->assertTrue(
            Hash::check(
                $passwordAsli,
                $userTarget->password
            )
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'user',
                'subject_id' => $userTarget->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'update')
            ->where('module', 'user')
            ->where('subject_id', $userTarget->id)
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'User Target',
            $log->old_values['name']
        );

        $this->assertEquals(
            'target@example.com',
            $log->old_values['email']
        );

        $this->assertEquals(
            'admin',
            $log->old_values['role']
        );

        $this->assertNull(
            $log->old_values['teknisi_id']
        );

        $this->assertEquals(
            'User Target',
            $log->new_values['name']
        );

        $this->assertEquals(
            'target@example.com',
            $log->new_values['email']
        );

        $this->assertEquals(
            'teknisi',
            $log->new_values['role']
        );

        $this->assertEquals(
            $teknisi->id,
            $log->new_values['teknisi_id']
        );

        $this->assertArrayNotHasKey(
            'password',
            $log->old_values
        );

        $this->assertArrayNotHasKey(
            'password_confirmation',
            $log->old_values
        );

        $this->assertArrayNotHasKey(
            'password',
            $log->new_values
        );

        $this->assertArrayNotHasKey(
            'password_confirmation',
            $log->new_values
        );

        $this->assertStringNotContainsString(
            $passwordAsli,
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $hashPasswordSebelum,
            $log->description ?? ''
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER: TEKNISI -> ADMIN
    |--------------------------------------------------------------------------
    */

    public function test_update_user_teknisi_menjadi_admin_melepas_profil_dan_dicatat_tanpa_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Pengelola Dua',
            'email' => 'admin.dua@example.com',
            'role' => 'admin',
        ]);

        $passwordAsli =
            'TeknisiPassword123!';

        $userTeknisi = User::factory()->create([
            'name' => 'User Teknisi',
            'email' => 'user.teknisi@example.com',
            'password' => Hash::make($passwordAsli),
            'role' => 'teknisi',
        ]);

        $hashPasswordSebelum =
            $userTeknisi->password;

        $teknisi = Teknisi::create([
            'user_id' =>
                $userTeknisi->id,

            'nama' =>
                'Profil Teknisi Lama',

            'telepon' =>
                '081299999999',

            'alamat' =>
                'Serang',
        ]);

        $teknisiId =
            $teknisi->id;

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTeknisi
                ),
                [
                    'role' =>
                        'admin',

                    'teknisi_id' =>
                        null,
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $userTeknisi->id,

                'name' =>
                    'User Teknisi',

                'email' =>
                    'user.teknisi@example.com',

                'role' =>
                    'admin',
            ]
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisiId,

                'user_id' =>
                    null,

                'nama' =>
                    'Profil Teknisi Lama',
            ]
        );

        $userTeknisi->refresh();

        $this->assertEquals(
            $hashPasswordSebelum,
            $userTeknisi->password
        );

        $this->assertTrue(
            Hash::check(
                $passwordAsli,
                $userTeknisi->password
            )
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' =>
                    $admin->id,

                'action' =>
                    'update',

                'module' =>
                    'user',

                'subject_id' =>
                    $userTeknisi->id,
            ]
        );

        $log = ActivityLog::where(
            'user_id',
            $admin->id
        )
            ->where('action', 'update')
            ->where('module', 'user')
            ->where('subject_id', $userTeknisi->id)
            ->first();

        $this->assertNotNull(
            $log
        );

        $this->assertEquals(
            'User Teknisi',
            $log->old_values['name']
        );

        $this->assertEquals(
            'user.teknisi@example.com',
            $log->old_values['email']
        );

        $this->assertEquals(
            'teknisi',
            $log->old_values['role']
        );

        $this->assertEquals(
            $teknisiId,
            $log->old_values['teknisi_id']
        );

        $this->assertEquals(
            'User Teknisi',
            $log->new_values['name']
        );

        $this->assertEquals(
            'user.teknisi@example.com',
            $log->new_values['email']
        );

        $this->assertEquals(
            'admin',
            $log->new_values['role']
        );

        $this->assertNull(
            $log->new_values['teknisi_id']
        );

        $this->assertArrayNotHasKey(
            'password',
            $log->old_values
        );

        $this->assertArrayNotHasKey(
            'password_confirmation',
            $log->old_values
        );

        $this->assertArrayNotHasKey(
            'password',
            $log->new_values
        );

        $this->assertArrayNotHasKey(
            'password_confirmation',
            $log->new_values
        );

        $this->assertStringNotContainsString(
            $passwordAsli,
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $hashPasswordSebelum,
            $log->description ?? ''
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARD: ADMIN TIDAK BOLEH MENURUNKAN ROLE DIRI SENDIRI
    |--------------------------------------------------------------------------
    */

    public function test_admin_login_tidak_boleh_menurunkan_role_diri_sendiri_dan_tidak_membuat_audit_update(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN LOGIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' =>
                'Admin Utama',

            'email' =>
                'admin.utama@example.com',

            'password' =>
                Hash::make(
                    'AdminPassword123!'
                ),

            'role' =>
                'admin',
        ]);


        $hashPasswordSebelum =
            $admin->password;


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI TERSEDIA
        |--------------------------------------------------------------------------
        */

        $teknisi = Teknisi::create([
            'user_id' =>
                null,

            'nama' =>
                'Profil Percobaan',

            'telepon' =>
                '081288888888',

            'alamat' =>
                'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | COBA TURUNKAN ROLE DIRI SENDIRI
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $admin
                )
            )
            ->put(
                route(
                    'users.update',
                    $admin
                ),
                [
                    'role' =>
                        'teknisi',

                    'teknisi_id' =>
                        $teknisi->id,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | KEMBALI KE HALAMAN EDIT
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route(
                'users.edit',
                $admin
            )
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
        | ROLE ADMIN TETAP ADMIN
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $admin->id,

                'name' =>
                    'Admin Utama',

                'email' =>
                    'admin.utama@example.com',

                'role' =>
                    'admin',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI TETAP BELUM TERHUBUNG
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisi->id,

                'user_id' =>
                    null,

                'nama' =>
                    'Profil Percobaan',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PASSWORD TIDAK BERUBAH
        |--------------------------------------------------------------------------
        */

        $admin->refresh();

        $this->assertEquals(
            $hashPasswordSebelum,
            $admin->password
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT UPDATE PALSU
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'activity_logs',
            [
                'user_id' =>
                    $admin->id,

                'action' =>
                    'update',

                'module' =>
                    'user',

                'subject_id' =>
                    $admin->id,
            ]
        );
    }
}