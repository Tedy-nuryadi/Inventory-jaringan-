<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKeluarFilterUiTest extends TestCase
{
    use RefreshDatabase;


    public function test_halaman_laporan_barang_keluar_memiliki_form_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-keluar');

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
            'name="barang_id"',
            false
        );

        $response->assertSee(
            'name="kategori_id"',
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


    public function test_form_filter_barang_keluar_mempertahankan_nilai_yang_dipilih(): void
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

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&barang_id=' . $barang->id
                . '&kategori_id=' . $kategori->id
                . '&teknisi_id=' . $teknisi->id
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

        $response->assertSee(
            'value="' . $barang->id . '" selected',
            false
        );

        $response->assertSee(
            'value="' . $kategori->id . '" selected',
            false
        );

        $response->assertSee(
            'value="' . $teknisi->id . '" selected',
            false
        );
    }


    public function test_halaman_laporan_barang_keluar_memiliki_tombol_export_excel_dan_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-keluar');

        $response->assertOk();

        $response->assertSee(
            'Export Excel'
        );

        $response->assertSee(
            'Export PDF'
        );

        $response->assertSee(
            route('laporan.keluar.excel'),
            false
        );

        $response->assertSee(
            route('laporan.keluar.pdf'),
            false
        );
    }


    public function test_tombol_export_barang_keluar_mempertahankan_filter_aktif(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Export Filter',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-EXPORT-001',
            'nama_barang' => 'Modem Export',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 20,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&barang_id=' . $barang->id
                . '&kategori_id=' . $kategori->id
                . '&teknisi_id=' . $teknisi->id
            );

        $response->assertOk();

        $queryString =
            'tanggal_mulai=2026-10-01'
            . '&tanggal_selesai=2026-10-10'
            . '&barang_id=' . $barang->id
            . '&kategori_id=' . $kategori->id
            . '&teknisi_id=' . $teknisi->id;

        $response->assertSee(
            route('laporan.keluar.excel')
            . '?'
            . $queryString,
            false
        );

        $response->assertSee(
            route('laporan.keluar.pdf')
            . '?'
            . $queryString,
            false
        );
    }
}