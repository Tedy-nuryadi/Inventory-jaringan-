<?php

namespace Tests\Feature\Laporan;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatSayaFilterUiTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | FORM FILTER TERSEDIA
    |--------------------------------------------------------------------------
    */

    public function test_halaman_riwayat_saya_memiliki_form_filter_periode(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Filter UI Riwayat',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/riwayat-saya');

        $response->assertOk();

        $response->assertSee(
            'Filter Riwayat'
        );

        $response->assertSee(
            'name="tanggal_mulai"',
            false
        );

        $response->assertSee(
            'name="tanggal_selesai"',
            false
        );

        $response->assertSee(
            'Filter'
        );

        $response->assertSee(
            'Reset Filter'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NILAI FILTER TETAP TERISI
    |--------------------------------------------------------------------------
    */

    public function test_form_filter_riwayat_saya_mempertahankan_nilai_periode_yang_dipilih(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Nilai Filter Riwayat',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get(
                '/riwayat-saya'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $response->assertSee(
            'value="2026-10-01"',
            false
        );

        $response->assertSee(
            'value="2026-10-10"',
            false
        );
    }
}