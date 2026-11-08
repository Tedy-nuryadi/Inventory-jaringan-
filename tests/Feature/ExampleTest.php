<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | HALAMAN UTAMA MENGARAH KE DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function test_halaman_utama_mengarahkan_ke_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(
            route('dashboard')
        );
    }
}