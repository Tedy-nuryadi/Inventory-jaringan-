<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bisa_melihat_data_dan_ringkasan_laporan_barang_masuk(): void
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
            'stok' => 15,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-01',
            'jumlah' => 10,
            'supplier' => 'PT Supplier Satu',
            'keterangan' => 'Pembelian stok',
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 5,
            'supplier' => 'PT Supplier Dua',
            'keterangan' => 'Penambahan stok',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-masuk');

        $response->assertOk();

        $response->assertSee('Laporan Barang Masuk');

        $response->assertSee('Modem CDATA');

        $response->assertSee('PT Supplier Satu');

        $response->assertSee('PT Supplier Dua');

        $response->assertViewHas(
            'barangMasuks',
            function ($barangMasuks) {
                return $barangMasuks->count() === 2;
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            2
        );

        $response->assertViewHas(
            'totalJumlahMasuk',
            15
        );
    }


    public function test_admin_bisa_memfilter_laporan_barang_masuk_berdasarkan_periode_tanggal(): void
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
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-09-25',
            'jumlah' => 4,
            'supplier' => 'Supplier September',
            'keterangan' => 'Data sebelum periode',
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-03',
            'jumlah' => 10,
            'supplier' => 'Supplier Dalam Periode',
            'keterangan' => 'Data dalam periode',
        ]);

        BarangMasuk::create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-10-20',
            'jumlah' => 16,
            'supplier' => 'Supplier Setelah Periode',
            'keterangan' => 'Data setelah periode',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-masuk'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangMasuks',
            function ($barangMasuks) {
                return $barangMasuks->count() === 1
                    && $barangMasuks->first()->supplier
                        === 'Supplier Dalam Periode';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahMasuk',
            10
        );

        $response->assertSee(
            'Supplier Dalam Periode'
        );

        $response->assertDontSee(
            'Supplier September'
        );

        $response->assertDontSee(
            'Supplier Setelah Periode'
        );
    }


    public function test_admin_bisa_mencari_laporan_barang_masuk(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat',
        ]);

        $router = Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 10,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $switch = Barang::create([
            'kode_barang' => 'SW-001',
            'nama_barang' => 'Switch TP-Link',
            'kategori_id' => $kategori->id,
            'merk' => 'TP-Link',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        BarangMasuk::create([
            'barang_id' => $router->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 5,
            'supplier' => 'PT Jaringan Indonesia',
            'keterangan' => 'Pembelian router',
        ]);

        BarangMasuk::create([
            'barang_id' => $switch->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 10,
            'supplier' => 'PT Network Sejahtera',
            'keterangan' => 'Pembelian switch',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-masuk?search=MikroTik');

        $response->assertOk();

        $response->assertViewHas(
            'barangMasuks',
            function ($barangMasuks) {
                return $barangMasuks->count() === 1
                    && $barangMasuks->first()->barang->kode_barang
                        === 'RTR-001';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahMasuk',
            5
        );

        $response->assertSee('Router MikroTik');

        $response->assertDontSee('Switch TP-Link');
    }
}