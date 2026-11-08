<?php

namespace Tests\Feature\Laporan;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanStokFilterUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_laporan_stok_memiliki_form_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok');

        $response->assertOk();

        $response->assertSee('Filter Laporan');

        $response->assertSee('name="search"', false);

        $response->assertSee('name="kategori_id"', false);

        $response->assertSee('name="status"', false);

        $response->assertSee('Cari');

        $response->assertSee('Reset Filter');
    }


    public function test_form_filter_mempertahankan_nilai_yang_dipilih(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/stok?search=CDATA'
                . '&kategori_id=' . $kategori->id
                . '&status=aman'
            );

        $response->assertOk();

        $response->assertSee(
            'value="CDATA"',
            false
        );

        $response->assertSee(
            'value="' . $kategori->id . '" selected',
            false
        );

        $response->assertSee(
            'value="aman" selected',
            false
        );
    }


    public function test_halaman_laporan_stok_memiliki_tombol_export_excel_dengan_filter_aktif(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/stok?search=CDATA'
                . '&kategori_id=' . $kategori->id
                . '&status=aman'
            );

        $response->assertOk();

        $response->assertSee(
            'Export Excel'
        );

        $response->assertSee(
            route('laporan.stok.excel', [
                'search' => 'CDATA',
                'kategori_id' => $kategori->id,
                'status' => 'aman',
            ])
        );
    }


    public function test_halaman_laporan_stok_memiliki_tombol_export_pdf_dengan_filter_aktif(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/stok?search=CDATA'
                . '&kategori_id=' . $kategori->id
                . '&status=aman'
            );

        $response->assertOk();

        $response->assertSee(
            'Export PDF'
        );

        $response->assertSee(
            route('laporan.stok.pdf', [
                'search' => 'CDATA',
                'kategori_id' => $kategori->id,
                'status' => 'aman',
            ])
        );
    }
}