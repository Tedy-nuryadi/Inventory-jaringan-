<?php

namespace Tests\Feature\Laporan;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_membuka_semua_laporan_global(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/laporan/stok')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/laporan/barang-masuk')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/laporan/barang-keluar')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/laporan/teknisi')
            ->assertOk();
    }


    public function test_teknisi_tidak_bisa_membuka_laporan_global(): void
    {
        $teknisiUser = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $this->actingAs($teknisiUser)
            ->get('/laporan/stok')
            ->assertForbidden();

        $this->actingAs($teknisiUser)
            ->get('/laporan/barang-masuk')
            ->assertForbidden();

        $this->actingAs($teknisiUser)
            ->get('/laporan/barang-keluar')
            ->assertForbidden();

        $this->actingAs($teknisiUser)
            ->get('/laporan/teknisi')
            ->assertForbidden();
    }


    public function test_teknisi_bisa_membuka_riwayat_saya(): void
    {
        $teknisiUser = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $teknisiUser->id,
            'nama' => 'Teknisi Test',
        ]);

        $this->actingAs($teknisiUser)
            ->get('/riwayat-saya')
            ->assertOk();
    }
}