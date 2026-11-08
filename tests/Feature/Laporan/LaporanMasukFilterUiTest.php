<?php

namespace Tests\Feature\Laporan;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMasukFilterUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_laporan_barang_masuk_memiliki_form_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-masuk');

        $response->assertOk();

        $response->assertSee('Filter Laporan');

        $response->assertSee(
            'name="search"',
            false
        );

        $response->assertSee(
            'name="tanggal_mulai"',
            false
        );

        $response->assertSee(
            'name="tanggal_selesai"',
            false
        );

        $response->assertSee('Cari');

        $response->assertSee('Reset Filter');
    }


    public function test_form_filter_barang_masuk_mempertahankan_nilai_yang_dipilih(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-masuk'
                . '?search=CDATA'
                . '&tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $response->assertSee(
            'value="CDATA"',
            false
        );

        $response->assertSee(
            'value="2026-10-01"',
            false
        );

        $response->assertSee(
            'value="2026-10-10"',
            false
        );
    }


    public function test_halaman_laporan_barang_masuk_memiliki_tombol_export_excel_dan_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-masuk');

        $response->assertOk();

        $response->assertSee('Export Excel');

        $response->assertSee('Export PDF');

        $response->assertSee(
            route('laporan.masuk.excel'),
            false
        );

        $response->assertSee(
            route('laporan.masuk.pdf'),
            false
        );
    }


    public function test_tombol_export_barang_masuk_mempertahankan_filter_aktif(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-masuk'
                . '?search=CDATA'
                . '&tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $excelUrl = route(
            'laporan.masuk.excel',
            [
                'search' => 'CDATA',
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-10',
            ]
        );

        $pdfUrl = route(
            'laporan.masuk.pdf',
            [
                'search' => 'CDATA',
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-10',
            ]
        );

        $response->assertSee(
            $excelUrl,
            false
        );

        $response->assertSee(
            $pdfUrl,
            false
        );
    }
}