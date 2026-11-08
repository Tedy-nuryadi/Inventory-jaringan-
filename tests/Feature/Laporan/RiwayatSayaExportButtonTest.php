<?php

namespace Tests\Feature\Laporan;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatSayaExportButtonTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TOMBOL EXPORT EXCEL DAN PDF TERSEDIA
    |--------------------------------------------------------------------------
    */

    public function test_halaman_riwayat_saya_memiliki_tombol_export_excel_dan_pdf(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI
        |--------------------------------------------------------------------------
        */

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA TEKNISI
        |--------------------------------------------------------------------------
        */

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Export Button Riwayat',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($userTeknisi)
            ->get('/riwayat-saya');


        /*
        |--------------------------------------------------------------------------
        | ASSERT
        |--------------------------------------------------------------------------
        */

        $response->assertOk();

        $response->assertSee(
            'Export Excel'
        );

        $response->assertSee(
            'Export PDF'
        );

        $response->assertSee(
            route('riwayat.saya.excel'),
            false
        );

        $response->assertSee(
            route('riwayat.saya.pdf'),
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOMBOL EXPORT MEMPERTAHANKAN FILTER PERIODE
    |--------------------------------------------------------------------------
    */

    public function test_tombol_export_riwayat_saya_mempertahankan_filter_periode_aktif(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI
        |--------------------------------------------------------------------------
        */

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA TEKNISI
        |--------------------------------------------------------------------------
        */

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Filter Export Riwayat',
            'telepon' => '082222222222',
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
        ];


        /*
        |--------------------------------------------------------------------------
        | REQUEST HALAMAN RIWAYAT
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($userTeknisi)
            ->get(
                '/riwayat-saya?'
                . http_build_query($filter)
            );

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | URL EXCEL HARUS MEMBAWA FILTER
        |--------------------------------------------------------------------------
        */

        $urlExcel = route(
            'riwayat.saya.excel',
            $filter
        );

        $response->assertSee(
            $urlExcel,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | URL PDF HARUS MEMBAWA FILTER
        |--------------------------------------------------------------------------
        */

        $urlPdf = route(
            'riwayat.saya.pdf',
            $filter
        );

        $response->assertSee(
            $urlPdf,
            false
        );


        /*
        |--------------------------------------------------------------------------
        | NILAI FILTER FORM TETAP TERISI
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
    }
}