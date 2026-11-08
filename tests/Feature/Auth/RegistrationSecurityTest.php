<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | HALAMAN REGISTER TIDAK BOLEH DIAKSES PUBLIK
    |--------------------------------------------------------------------------
    */

    public function test_guest_tidak_bisa_membuka_halaman_register(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }


    /*
    |--------------------------------------------------------------------------
    | GUEST TIDAK BOLEH MEMBUAT AKUN SENDIRI
    |--------------------------------------------------------------------------
    */

    public function test_guest_tidak_bisa_mendaftarkan_akun_baru(): void
    {
        $response = $this->post('/register', [
            'name' => 'User Tidak Sah',
            'email' => 'user.tidaksah@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing(
            'users',
            [
                'email' => 'user.tidaksah@example.com',
            ]
        );
    }
}