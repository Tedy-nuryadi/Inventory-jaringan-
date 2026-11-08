<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanStokTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_melihat_data_dan_ringkasan_laporan_stok(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriModem = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $kategoriKabel = Kategori::create([
            'nama_kategori' => 'Kabel',
        ]);

        Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategoriModem->id,
            'merk' => 'CDATA',
            'stok' => 12,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'KBL-001',
            'nama_barang' => 'Patchcord SC 1M',
            'kategori_id' => $kategoriKabel->id,
            'merk' => 'Zimmlink',
            'stok' => 3,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok');

        $response->assertOk();

        $response->assertSee('Laporan Stok Barang');

        $response->assertSee('Modem CDATA');
        $response->assertSee('Patchcord SC 1M');

        $response->assertSee('MDM-001');
        $response->assertSee('KBL-001');

        $response->assertSee('15');
        $response->assertSee('2');
    }


    public function test_admin_bisa_mencari_barang_di_laporan_stok(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat',
        ]);

        Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'SW-001',
            'nama_barang' => 'Switch TP-Link',
            'kategori_id' => $kategori->id,
            'merk' => 'TP-Link',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok?search=MikroTik');

        $response->assertOk();

        $response->assertViewHas(
            'barangs',
            function ($barangs) {
                return $barangs->count() === 1
                    && $barangs->first()->kode_barang === 'RTR-001';
            }
        );

        $response->assertSee('Router MikroTik');

        $response->assertDontSee('Switch TP-Link');
    }


    public function test_admin_bisa_memfilter_laporan_stok_berdasarkan_kategori(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriModem = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $kategoriRouter = Kategori::create([
            'nama_kategori' => 'Router',
        ]);

        Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategoriModem->id,
            'merk' => 'CDATA',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategoriRouter->id,
            'merk' => 'MikroTik',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok?kategori_id=' . $kategoriModem->id);

        $response->assertOk();

        $response->assertViewHas(
            'barangs',
            function ($barangs) use ($kategoriModem) {
                return $barangs->count() === 1
                    && $barangs->first()->kategori_id === $kategoriModem->id;
            }
        );

        $response->assertSee('Modem CDATA');

        $response->assertDontSee('Router MikroTik');
    }


    public function test_admin_bisa_memfilter_laporan_stok_status_kritis(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat',
        ]);

        Barang::create([
            'kode_barang' => 'DEV-001',
            'nama_barang' => 'Perangkat Aman',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-002',
            'nama_barang' => 'Perangkat Menipis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 3,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-003',
            'nama_barang' => 'Perangkat Kritis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 0,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok?status=kritis');

        $response->assertOk();

        $response->assertViewHas(
            'barangs',
            function ($barangs) {
                return $barangs->count() === 1
                    && $barangs->first()->kode_barang === 'DEV-003';
            }
        );

        $response->assertSee('Perangkat Kritis');

        $response->assertDontSee('Perangkat Aman');

        $response->assertDontSee('Perangkat Menipis');
    }


    public function test_admin_bisa_memfilter_laporan_stok_status_menipis(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat',
        ]);

        Barang::create([
            'kode_barang' => 'DEV-001',
            'nama_barang' => 'Perangkat Aman',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-002',
            'nama_barang' => 'Perangkat Menipis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 3,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-003',
            'nama_barang' => 'Perangkat Kritis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 0,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok?status=menipis');

        $response->assertOk();

        $response->assertViewHas(
            'barangs',
            function ($barangs) {
                return $barangs->count() === 1
                    && $barangs->first()->kode_barang === 'DEV-002';
            }
        );

        $response->assertSee('Perangkat Menipis');

        $response->assertDontSee('Perangkat Aman');

        $response->assertDontSee('Perangkat Kritis');
    }


    public function test_admin_bisa_memfilter_laporan_stok_status_aman(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat',
        ]);

        Barang::create([
            'kode_barang' => 'DEV-001',
            'nama_barang' => 'Perangkat Aman',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-002',
            'nama_barang' => 'Perangkat Menipis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 3,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        Barang::create([
            'kode_barang' => 'DEV-003',
            'nama_barang' => 'Perangkat Kritis',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 0,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/stok?status=aman');

        $response->assertOk();

        $response->assertViewHas(
            'barangs',
            function ($barangs) {
                return $barangs->count() === 1
                    && $barangs->first()->kode_barang === 'DEV-001';
            }
        );

        $response->assertSee('Perangkat Aman');

        $response->assertDontSee('Perangkat Menipis');

        $response->assertDontSee('Perangkat Kritis');
    }
}