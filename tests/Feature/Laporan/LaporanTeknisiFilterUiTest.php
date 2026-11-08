<?php

namespace Tests\Feature\Laporan;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTeknisiFilterUiTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | FORM FILTER TERSEDIA
    |--------------------------------------------------------------------------
    */

    public function test_halaman_laporan_pemakaian_teknisi_memiliki_form_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/teknisi');

        $response->assertOk();

        $response->assertSee(
            'Filter Laporan'
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
            'name="teknisi_id"',
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

    public function test_form_filter_pemakaian_teknisi_mempertahankan_nilai_yang_dipilih(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Filter UI',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&teknisi_id=' . $teknisi->id
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | TANGGAL TETAP TERISI
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'value="2026-10-01"',
            false
        );

        $response->assertSee(
            'value="2026-10-10"',
            false
        );


        /*
        |--------------------------------------------------------------------------
        | TEKNISI TETAP TERPILIH
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'value="' . $teknisi->id . '" selected',
            false
        );


        /*
        |--------------------------------------------------------------------------
        | NAMA TEKNISI ADA DI DROPDOWN
        |--------------------------------------------------------------------------
        */

        $response->assertSee(
            'Teknisi Filter UI'
        );
    }
}