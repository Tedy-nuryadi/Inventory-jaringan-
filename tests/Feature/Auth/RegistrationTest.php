<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | HALAMAN REGISTRASI DINONAKTIFKAN
    |--------------------------------------------------------------------------
    */

    public function test_registration_screen_is_not_available(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();

        $this->assertGuest();
    }


    /*
    |--------------------------------------------------------------------------
    | USER PUBLIK TIDAK BISA MENDAFTAR
    |--------------------------------------------------------------------------
    */

    public function test_new_users_cannot_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'User Baru',
            'email' => 'userbaru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();

        $this->assertGuest();

        $this->assertDatabaseMissing('users', [
            'email' => 'userbaru@example.com',
        ]);
    }
}