<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanKeluarTest extends TestCase
{
    use RefreshDatabase;


    public function test_admin_bisa_melihat_data_dan_ringkasan_laporan_barang_keluar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Satu',
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

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-01',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan A',
            'keperluan' => 'Pemasangan baru',
            'tujuan' => 'Site A',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 2,
            'pengguna' => 'Pelanggan B',
            'keperluan' => 'Penggantian perangkat',
            'tujuan' => 'Site B',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/barang-keluar');

        $response->assertOk();

        $response->assertSee(
            'Laporan Barang Keluar'
        );

        $response->assertSee(
            'Modem CDATA'
        );

        $response->assertSee(
            'Teknisi Satu'
        );

        $response->assertSee(
            'Pemasangan baru'
        );

        $response->assertSee(
            'Penggantian perangkat'
        );

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) {
                return $barangKeluars->count() === 2;
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            2
        );

        $response->assertViewHas(
            'totalJumlahKeluar',
            5
        );
    }


    public function test_admin_bisa_memfilter_laporan_barang_keluar_berdasarkan_periode_tanggal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Filter',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Router',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-09-25',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan September',
            'keperluan' => 'Sebelum periode',
            'tujuan' => 'Site September',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Dalam Periode',
            'keperluan' => 'Dalam periode',
            'tujuan' => 'Site Oktober',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-20',
            'jumlah' => 8,
            'pengguna' => 'Pelanggan Setelah Periode',
            'keperluan' => 'Setelah periode',
            'tujuan' => 'Site Akhir Oktober',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) {
                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Dalam Periode';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahKeluar',
            6
        );

        $response->assertSee(
            'Pelanggan Dalam Periode'
        );

        $response->assertDontSee(
            'Pelanggan September'
        );

        $response->assertDontSee(
            'Pelanggan Setelah Periode'
        );
    }


    public function test_admin_bisa_memfilter_laporan_barang_keluar_berdasarkan_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisiSatu = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiSatu = Teknisi::create([
            'user_id' => $userTeknisiSatu->id,
            'nama' => 'Teknisi Andi',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $userTeknisiDua = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiDua = Teknisi::create([
            'user_id' => $userTeknisiDua->id,
            'nama' => 'Teknisi Budi',
            'telepon' => '082222222222',
            'alamat' => 'Serang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiSatu->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan Andi',
            'keperluan' => 'Instalasi oleh Andi',
            'tujuan' => 'Site Andi',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiDua->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 7,
            'pengguna' => 'Pelanggan Budi',
            'keperluan' => 'Instalasi oleh Budi',
            'tujuan' => 'Site Budi',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?teknisi_id='
                . $teknisiSatu->id
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($teknisiSatu) {
                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->teknisi_id
                        === $teknisiSatu->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Andi';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahKeluar',
            3
        );

        $response->assertSee(
            'Pelanggan Andi'
        );

        /*
        |--------------------------------------------------------------------------
        | Jangan assertDontSee('Teknisi Budi')
        |--------------------------------------------------------------------------
        |
        | Teknisi Budi memang harus tetap muncul di dropdown filter.
        | Yang tidak boleh muncul adalah transaksi milik Budi.
        |
        */

        $response->assertDontSee(
            'Pelanggan Budi'
        );

        $response->assertDontSee(
            'Instalasi oleh Budi'
        );

        $response->assertDontSee(
            'Site Budi'
        );
    }


    public function test_admin_bisa_memfilter_laporan_barang_keluar_berdasarkan_barang(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Barang',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Jaringan',
        ]);

        $modem = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $router = Barang::create([
            'kode_barang' => 'RTR-001',
            'nama_barang' => 'Router MikroTik',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-07',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Modem',
            'keperluan' => 'Pemasangan modem',
            'tujuan' => 'Site Modem',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $router->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-08',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Router',
            'keperluan' => 'Pemasangan router',
            'tujuan' => 'Site Router',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?barang_id='
                . $modem->id
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($modem) {
                return $barangKeluars->count() === 1
                    && $barangKeluars->first()->barang_id
                        === $modem->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Modem';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahKeluar',
            4
        );

        $response->assertSee(
            'Pelanggan Modem'
        );

        /*
        |--------------------------------------------------------------------------
        | Router MikroTik tetap ada di dropdown Barang
        |--------------------------------------------------------------------------
        */

        $response->assertDontSee(
            'Pelanggan Router'
        );

        $response->assertDontSee(
            'Pemasangan router'
        );

        $response->assertDontSee(
            'Site Router'
        );
    }


    public function test_admin_bisa_memfilter_laporan_barang_keluar_berdasarkan_kategori(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Kategori',
            'telepon' => '084444444444',
            'alamat' => 'Tangerang',
        ]);

        $kategoriModem = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $kategoriSwitch = Kategori::create([
            'nama_kategori' => 'Switch',
        ]);

        $modem = Barang::create([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem CDATA',
            'kategori_id' => $kategoriModem->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        $switch = Barang::create([
            'kode_barang' => 'SW-001',
            'nama_barang' => 'Switch TP-Link',
            'kategori_id' => $kategoriSwitch->id,
            'merk' => 'TP-Link',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $modem->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-09',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Modem Kategori',
            'keperluan' => 'Instalasi modem kategori',
            'tujuan' => 'Site Modem',
            'foto' => null,
        ]);

        BarangKeluar::create([
            'barang_id' => $switch->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 8,
            'pengguna' => 'Pelanggan Switch Kategori',
            'keperluan' => 'Instalasi switch kategori',
            'tujuan' => 'Site Switch',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/barang-keluar'
                . '?kategori_id='
                . $kategoriModem->id
            );

        $response->assertOk();

        $response->assertViewHas(
            'barangKeluars',
            function ($barangKeluars) use ($kategoriModem) {
                return $barangKeluars->count() === 1
                    && $barangKeluars
                        ->first()
                        ->barang
                        ->kategori_id
                        === $kategoriModem->id
                    && $barangKeluars->first()->pengguna
                        === 'Pelanggan Modem Kategori';
            }
        );

        $response->assertViewHas(
            'totalTransaksi',
            1
        );

        $response->assertViewHas(
            'totalJumlahKeluar',
            4
        );

        $response->assertSee(
            'Pelanggan Modem Kategori'
        );

        /*
        |--------------------------------------------------------------------------
        | Switch TP-Link tetap ada di dropdown Barang
        |--------------------------------------------------------------------------
        */

        $response->assertDontSee(
            'Pelanggan Switch Kategori'
        );

        $response->assertDontSee(
            'Instalasi switch kategori'
        );

        $response->assertDontSee(
            'Site Switch'
        );
    }
}