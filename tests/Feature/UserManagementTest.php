<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MENGUBAH NAMA DAN EMAIL USER
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_mengubah_nama_dan_email_user_tanpa_mengubah_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Pengelola User',
            'email' => 'admin.pengelola.user@example.com',
            'role' => 'admin',
        ]);

        $passwordAsli = 'PasswordTarget123!';

        $userTarget = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'email.lama@example.com',
            'password' => Hash::make($passwordAsli),
            'role' => 'admin',
        ]);

        $hashPasswordSebelum =
            $userTarget->password;


        $response = $this
            ->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'name' =>
                        'Nama Baru',

                    'email' =>
                        'email.baru@example.com',

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
                    $userTarget->id,

                'name' =>
                    'Nama Baru',

                'email' =>
                    'email.baru@example.com',

                'role' =>
                    'admin',
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
                'user'
            )
            ->where(
                'subject_id',
                $userTarget->id
            )
            ->latest()
            ->first();


        $this->assertNotNull(
            $log
        );


        $this->assertEquals(
            'Nama Lama',
            $log->old_values['name']
        );

        $this->assertEquals(
            'email.lama@example.com',
            $log->old_values['email']
        );

        $this->assertEquals(
            'Nama Baru',
            $log->new_values['name']
        );

        $this->assertEquals(
            'email.baru@example.com',
            $log->new_values['email']
        );


        $this->assertArrayNotHasKey(
            'password',
            $log->old_values
        );

        $this->assertArrayNotHasKey(
            'password',
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
    | FORM EDIT MEMILIKI FIELD NAMA DAN EMAIL
    |--------------------------------------------------------------------------
    */

    public function test_form_edit_user_memiliki_field_nama_dan_email_dengan_nilai_saat_ini(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin UI User',
            'email' => 'admin.ui.user@example.com',
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'name' => 'Nama UI Lama',
            'email' => 'email.ui.lama@example.com',
            'role' => 'admin',
        ]);


        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'users.edit',
                    $userTarget
                )
            );


        $response->assertOk();


        $response->assertSee(
            'name="name"',
            false
        );

        $response->assertSee(
            'value="Nama UI Lama"',
            false
        );

        $response->assertSee(
            'name="email"',
            false
        );

        $response->assertSee(
            'value="email.ui.lama@example.com"',
            false
        );

        $response->assertSee(
            'name="role"',
            false
        );

        $response->assertDontSee(
            'value="' . $userTarget->password . '"',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EMAIL HARUS UNIK
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_mengubah_email_user_menjadi_email_milik_user_lain(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Validasi Email',
            'email' => 'admin.validasi@example.com',
            'role' => 'admin',
        ]);


        $userPemilikEmail = User::factory()->create([
            'name' => 'Pemilik Email',
            'email' => 'sudah.dipakai@example.com',
            'role' => 'admin',
        ]);


        $userTarget = User::factory()->create([
            'name' => 'User Target Email',
            'email' => 'target.awal@example.com',
            'role' => 'admin',
        ]);


        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'name' =>
                        'Nama Tidak Boleh Tersimpan',

                    'email' =>
                        $userPemilikEmail->email,

                    'role' =>
                        'admin',

                    'teknisi_id' =>
                        null,
                ]
            );


        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );


        $response->assertSessionHasErrors(
            'email'
        );


        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'name' =>
                    'User Target Email',

                'email' =>
                    'target.awal@example.com',

                'role' =>
                    'admin',
            ]
        );


        $this->assertDatabaseMissing(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'email' =>
                    'sudah.dipakai@example.com',
            ]
        );


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
                    $userTarget->id,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA RESET PASSWORD USER
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_mereset_password_user_tanpa_menyimpan_password_ke_audit(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Reset Password',
            'email' => 'admin.reset.password@example.com',
            'role' => 'admin',
        ]);


        $passwordLama =
            'PasswordLama123!';

        $passwordBaru =
            'PasswordBaru456!';


        $userTarget = User::factory()->create([
            'name' =>
                'User Reset Password',

            'email' =>
                'user.reset.password@example.com',

            'password' =>
                Hash::make(
                    $passwordLama
                ),

            'role' =>
                'admin',
        ]);


        $hashPasswordLama =
            $userTarget->password;


        $response = $this
            ->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'name' =>
                        $userTarget->name,

                    'email' =>
                        $userTarget->email,

                    'role' =>
                        'admin',

                    'teknisi_id' =>
                        null,

                    'password' =>
                        $passwordBaru,

                    'password_confirmation' =>
                        $passwordBaru,
                ]
            );


        $response->assertRedirect(
            route('users.index')
        );


        $userTarget->refresh();


        $this->assertNotEquals(
            $hashPasswordLama,
            $userTarget->password
        );

        $this->assertTrue(
            Hash::check(
                $passwordBaru,
                $userTarget->password
            )
        );

        $this->assertFalse(
            Hash::check(
                $passwordLama,
                $userTarget->password
            )
        );


        $this->assertEquals(
            'User Reset Password',
            $userTarget->name
        );

        $this->assertEquals(
            'user.reset.password@example.com',
            $userTarget->email
        );

        $this->assertEquals(
            'admin',
            $userTarget->role
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
                'user'
            )
            ->where(
                'subject_id',
                $userTarget->id
            )
            ->latest()
            ->first();


        $this->assertNotNull(
            $log
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
            $passwordLama,
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $passwordBaru,
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $hashPasswordLama,
            $log->description ?? ''
        );

        $this->assertStringNotContainsString(
            $userTarget->password,
            $log->description ?? ''
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT MEMILIKI FIELD RESET PASSWORD YANG AMAN
    |--------------------------------------------------------------------------
    */

    public function test_form_edit_user_memiliki_field_password_baru_dan_konfirmasi_tanpa_menampilkan_password_lama(): void
    {
        $admin = User::factory()->create([
            'name' =>
                'Admin UI Password',

            'email' =>
                'admin.ui.password@example.com',

            'role' =>
                'admin',
        ]);


        $userTarget = User::factory()->create([
            'name' =>
                'User UI Password',

            'email' =>
                'user.ui.password@example.com',

            'password' =>
                Hash::make(
                    'PasswordLamaRahasia123!'
                ),

            'role' =>
                'admin',
        ]);


        $hashPassword =
            $userTarget->password;


        $response = $this
            ->actingAs($admin)
            ->get(
                route(
                    'users.edit',
                    $userTarget
                )
            );


        $response->assertOk();


        $response->assertSee(
            'name="password"',
            false
        );

        $response->assertSee(
            'name="password_confirmation"',
            false
        );

        $response->assertSee(
            'type="password"',
            false
        );

        $response->assertSee(
            'autocomplete="new-password"',
            false
        );


        $response->assertDontSee(
            $hashPassword,
            false
        );

        $response->assertDontSee(
            'PasswordLamaRahasia123!',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI PASSWORD SALAH HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_password_dan_konfirmasi_yang_tidak_sama_ditolak_tanpa_mengubah_user_dan_tanpa_audit_palsu(): void
    {
        $admin = User::factory()->create([
            'name' =>
                'Admin Validasi Password',

            'email' =>
                'admin.validasi.password@example.com',

            'role' =>
                'admin',
        ]);


        $passwordLama =
            'PasswordLamaValid123!';


        $userTarget = User::factory()->create([
            'name' =>
                'User Password Validasi',

            'email' =>
                'user.password.validasi@example.com',

            'password' =>
                Hash::make(
                    $passwordLama
                ),

            'role' =>
                'admin',
        ]);


        $hashPasswordSebelum =
            $userTarget->password;


        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'name' =>
                        'Nama Tidak Boleh Berubah',

                    'email' =>
                        'email.tidak.boleh.berubah@example.com',

                    'role' =>
                        'admin',

                    'teknisi_id' =>
                        null,

                    'password' =>
                        'PasswordBaru123!',

                    'password_confirmation' =>
                        'PasswordKonfirmasiBerbeda456!',
                ]
            );


        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );


        $response->assertSessionHasErrors(
            'password'
        );


        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'name' =>
                    'User Password Validasi',

                'email' =>
                    'user.password.validasi@example.com',

                'role' =>
                    'admin',
            ]
        );


        $this->assertDatabaseMissing(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'name' =>
                    'Nama Tidak Boleh Berubah',
            ]
        );


        $userTarget->refresh();


        $this->assertEquals(
            $hashPasswordSebelum,
            $userTarget->password
        );


        $this->assertTrue(
            Hash::check(
                $passwordLama,
                $userTarget->password
            )
        );


        $this->assertFalse(
            Hash::check(
                'PasswordBaru123!',
                $userTarget->password
            )
        );


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
                    $userTarget->id,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD KURANG DARI 8 KARAKTER HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_password_baru_kurang_dari_delapan_karakter_ditolak_tanpa_mengubah_user_dan_tanpa_audit_palsu(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' =>
                'Admin Password Pendek',

            'email' =>
                'admin.password.pendek@example.com',

            'role' =>
                'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USER TARGET
        |--------------------------------------------------------------------------
        */

        $passwordLama =
            'PasswordLamaAman123!';


        $userTarget = User::factory()->create([
            'name' =>
                'User Password Pendek',

            'email' =>
                'user.password.pendek@example.com',

            'password' =>
                Hash::make(
                    $passwordLama
                ),

            'role' =>
                'admin',
        ]);


        $hashPasswordSebelum =
            $userTarget->password;


        /*
        |--------------------------------------------------------------------------
        | PASSWORD BARU HANYA 7 KARAKTER
        |--------------------------------------------------------------------------
        */

        $passwordPendek =
            'abc1234';


        /*
        |--------------------------------------------------------------------------
        | KIRIM UPDATE
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'name' =>
                        'Nama Tidak Boleh Tersimpan',

                    'email' =>
                        'email.pendek.tidak.boleh@example.com',

                    'role' =>
                        'admin',

                    'teknisi_id' =>
                        null,

                    'password' =>
                        $passwordPendek,

                    'password_confirmation' =>
                        $passwordPendek,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS KEMBALI KE FORM EDIT
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );


        /*
        |--------------------------------------------------------------------------
        | HARUS ADA ERROR PASSWORD
        |--------------------------------------------------------------------------
        */

        $response->assertSessionHasErrors(
            'password'
        );


        /*
        |--------------------------------------------------------------------------
        | NAMA, EMAIL, ROLE TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'name' =>
                    'User Password Pendek',

                'email' =>
                    'user.password.pendek@example.com',

                'role' =>
                    'admin',
            ]
        );


        $this->assertDatabaseMissing(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'name' =>
                    'Nama Tidak Boleh Tersimpan',
            ]
        );


        $this->assertDatabaseMissing(
            'users',
            [
                'id' =>
                    $userTarget->id,

                'email' =>
                    'email.pendek.tidak.boleh@example.com',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PASSWORD LAMA HARUS TETAP
        |--------------------------------------------------------------------------
        */

        $userTarget->refresh();


        $this->assertEquals(
            $hashPasswordSebelum,
            $userTarget->password
        );


        $this->assertTrue(
            Hash::check(
                $passwordLama,
                $userTarget->password
            )
        );


        $this->assertFalse(
            Hash::check(
                $passwordPendek,
                $userTarget->password
            )
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
                    $userTarget->id,
            ]
        );
    }
}