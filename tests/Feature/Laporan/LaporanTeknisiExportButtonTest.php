<?php

namespace Tests\Feature\Laporan;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTeknisiExportButtonTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TOMBOL EXPORT EXCEL DAN PDF TERSEDIA
    |--------------------------------------------------------------------------
    */

    public function test_halaman_laporan_pemakaian_teknisi_memiliki_tombol_export_excel_dan_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/teknisi');

        $response->assertOk();

        $response->assertSee(
            'Export Excel'
        );

        $response->assertSee(
            'Export PDF'
        );

        $response->assertSee(
            route('laporan.teknisi.excel'),
            false
        );

        $response->assertSee(
            route('laporan.teknisi.pdf'),
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOMBOL EXPORT MEMPERTAHANKAN FILTER
    |--------------------------------------------------------------------------
    */

    public function test_tombol_export_laporan_pemakaian_teknisi_mempertahankan_filter_aktif(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI
        |--------------------------------------------------------------------------
        */

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Export Button',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        $filter = [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-10',
            'teknisi_id' => $teknisi->id,
        ];


        /*
        |--------------------------------------------------------------------------
        | REQUEST HALAMAN LAPORAN
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi?'
                . http_build_query($filter)
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | URL EXPORT EXCEL HARUS MEMBAWA FILTER
        |--------------------------------------------------------------------------
        */

        $urlExcel = route(
            'laporan.teknisi.excel',
            $filter
        );

        $response->assertSee(
            $urlExcel,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | URL EXPORT PDF HARUS MEMBAWA FILTER
        |--------------------------------------------------------------------------
        */

        $urlPdf = route(
            'laporan.teknisi.pdf',
            $filter
        );

        $response->assertSee(
            $urlPdf,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | NILAI FILTER JUGA TETAP ADA DI FORM
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

        $response->assertSee(
            'value="' . $teknisi->id . '" selected',
            false
        );
    }
}