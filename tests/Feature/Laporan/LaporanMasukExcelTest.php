<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMasukExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_export_laporan_barang_masuk_ke_excel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 10,
            'supplier' => 'PT Supplier Indonesia',
            'keterangan' => 'Pembelian stok',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-masuk/excel');

        $response->assertOk();

        $response->assertDownload(
            'laporan-barang-masuk.xlsx'
        );
    }


    public function test_teknisi_tidak_bisa_export_laporan_barang_masuk_ke_excel(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)
            ->get('/laporan/barang-masuk/excel');

        $response->assertForbidden();
    }
}