<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanStokPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_export_laporan_stok_ke_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok/pdf');

        $response->assertOk();

        $response->assertDownload('laporan-stok.pdf');
    }


    public function test_teknisi_tidak_bisa_export_laporan_stok_ke_pdf(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)
            ->get('/laporan/stok/pdf');

        $response->assertForbidden();
    }
}