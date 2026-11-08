<?php

namespace Tests\Feature;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementCreateTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MEMBUKA FORM TAMBAH USER
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_membuka_form_tambah_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/users/create');

        $response->assertOk();

        $response->assertViewIs(
            'users.create'
        );

        $response->assertSee(
            'Tambah User'
        );

        $response->assertSee(
            'name="name"',
            false
        );

        $response->assertSee(
            'name="email"',
            false
        );

        $response->assertSee(
            'name="password"',
            false
        );

        $response->assertSee(
            'name="password_confirmation"',
            false
        );

        $response->assertSee(
            'name="role"',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MENAMBAH USER BARU
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_menambahkan_user_baru(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Admin Baru',
                'email' => 'adminbaru@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
            ]);

        $response->assertRedirect(
            route('users.index')
        );

        $response->assertSessionHas(
            'success'
        );

        $this->assertDatabaseHas(
            'users',
            [
                'name' => 'Admin Baru',
                'email' => 'adminbaru@example.com',
                'role' => 'admin',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD USER HARUS HASH
    |--------------------------------------------------------------------------
    */

    public function test_password_user_baru_disimpan_dalam_bentuk_hash(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Admin Hash',
                'email' => 'userhash@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
            ])
            ->assertRedirect(
                route('users.index')
            );

        $user = User::where(
            'email',
            'userhash@example.com'
        )->first();

        $this->assertNotNull(
            $user
        );

        $this->assertNotEquals(
            'password123',
            $user->password
        );

        $this->assertTrue(
            Hash::check(
                'password123',
                $user->password
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE TIDAK VALID HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_membuat_user_dengan_role_tidak_valid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'name' => 'User Role Ilegal',
                'email' => 'roleilegal@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'superadmin',
            ]);

        $response->assertRedirect(
            '/users/create'
        );

        $response->assertSessionHasErrors(
            'role'
        );

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'roleilegal@example.com',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD CONFIRMATION
    |--------------------------------------------------------------------------
    */

    public function test_password_confirmation_user_baru_harus_sesuai(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'name' => 'Password Salah',
                'email' => 'passwordsalah@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password456',
                'role' => 'admin',
            ]);

        $response->assertRedirect(
            '/users/create'
        );

        $response->assertSessionHasErrors(
            'password'
        );

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'passwordsalah@example.com',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EMAIL TIDAK BOLEH DUPLIKAT
    |--------------------------------------------------------------------------
    */

    public function test_email_user_baru_tidak_boleh_duplikat(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'email' => 'sudahada@example.com',
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'name' => 'User Duplikat',
                'email' => 'sudahada@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
            ]);

        $response->assertRedirect(
            '/users/create'
        );

        $response->assertSessionHasErrors(
            'email'
        );

        $this->assertDatabaseCount(
            'users',
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MEMBUKA FORM TAMBAH USER
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_membuka_form_tambah_user(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)
            ->get('/users/create');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MENYIMPAN USER
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_menambahkan_user_baru(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)
            ->post('/users', [
                'name' => 'Admin Ilegal',
                'email' => 'adminilegal@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'admin',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'adminilegal@example.com',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM MENAMPILKAN PILIHAN PROFIL TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_form_tambah_user_menampilkan_profil_teknisi_yang_belum_memiliki_akun(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $teknisiBelumPunyaAkun = Teknisi::create([
            'user_id' => null,
            'nama' => 'Teknisi Belum Punya Akun',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $userSudahTerhubung = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userSudahTerhubung->id,
            'nama' => 'Teknisi Sudah Punya Akun',
            'telepon' => '082222222222',
            'alamat' => 'Serang',
        ]);

        $response = $this->actingAs($admin)
            ->get('/users/create');

        $response->assertOk();

        $response->assertViewHas(
            'teknisis',
            function ($teknisis) use ($teknisiBelumPunyaAkun) {

                return $teknisis->count() === 1
                    && $teknisis->first()->id === $teknisiBelumPunyaAkun->id;
            }
        );

        $response->assertSee(
            'name="teknisi_id"',
            false
        );

        $response->assertSee(
            'Teknisi Belum Punya Akun'
        );

        $response->assertDontSee(
            'Teknisi Sudah Punya Akun'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | USER TEKNISI HARUS TERHUBUNG DENGAN PROFIL TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_membuat_user_teknisi_dan_menghubungkannya_ke_profil_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => null,
            'nama' => 'Teknisi Lapangan A',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Akun Teknisi A',
                'email' => 'teknisia@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'teknisi',
                'teknisi_id' => $teknisi->id,
            ]);

        $response->assertRedirect(
            route('users.index')
        );

        $user = User::where(
            'email',
            'teknisia@example.com'
        )->first();

        $this->assertNotNull(
            $user
        );

        $this->assertEquals(
            'teknisi',
            $user->role
        );

        $teknisi->refresh();

        $this->assertEquals(
            $user->id,
            $teknisi->user_id
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $teknisi->id,
                'user_id' => $user->id,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE TEKNISI WAJIB MEMILIH PROFIL TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_role_teknisi_wajib_memilih_profil_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'name' => 'Teknisi Tanpa Profil',
                'email' => 'tanpaprofil@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'teknisi',
                'teknisi_id' => '',
            ]);

        $response->assertRedirect(
            '/users/create'
        );

        $response->assertSessionHasErrors(
            'teknisi_id'
        );

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'tanpaprofil@example.com',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROFIL TEKNISI YANG SUDAH TERPAKAI TIDAK BOLEH DIPAKAI LAGI
    |--------------------------------------------------------------------------
    */

    public function test_profil_teknisi_yang_sudah_memiliki_akun_tidak_bisa_dihubungkan_ke_user_baru(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userLama = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userLama->id,
            'nama' => 'Teknisi Sudah Terhubung',
            'telepon' => '084444444444',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'name' => 'Akun Teknisi Kedua',
                'email' => 'teknisikedua@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'teknisi',
                'teknisi_id' => $teknisi->id,
            ]);

        $response->assertRedirect(
            '/users/create'
        );

        $response->assertSessionHasErrors(
            'teknisi_id'
        );

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'teknisikedua@example.com',
            ]
        );
    }
}