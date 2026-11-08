<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokMinimumBarangTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MENENTUKAN STOK MINIMUM SAAT MENAMBAH BARANG
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_menentukan_stok_minimum_saat_menambah_barang(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Router',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $response = $this->actingAs($admin)
            ->post('/barang', [
                'kode_barang' => 'RTR-MIN-001',
                'nama_barang' => 'Router Minimum Custom',
                'kategori_id' => $kategoriId,
                'merk' => 'MikroTik',
                'stok' => 20,
                'satuan' => 'pcs',
                'lokasi' => 'Gudang A',
                'stok_minimum' => 12,
            ]);


        $response->assertRedirect(
            route('barang.index')
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'kode_barang' => 'RTR-MIN-001',
                'stok' => 20,
                'stok_minimum' => 12,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA MENGUBAH STOK MINIMUM BARANG
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_mengubah_stok_minimum_barang(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Modem',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $barang = Barang::create([
            'kode_barang' => 'MDM-MIN-001',
            'nama_barang' => 'Modem Minimum Lama',
            'kategori_id' => $kategoriId,
            'merk' => 'Huawei',
            'stok' => 15,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 5,
        ]);


        $response = $this->actingAs($admin)
            ->put(
                route(
                    'barang.update',
                    $barang
                ),
                [
                    'kode_barang' => 'MDM-MIN-001',
                    'nama_barang' => 'Modem Minimum Baru',
                    'kategori_id' => $kategoriId,
                    'merk' => 'Huawei',
                    'stok' => 15,
                    'satuan' => 'pcs',
                    'lokasi' => 'Gudang B',
                    'stok_minimum' => 10,
                ]
            );


        $response->assertRedirect(
            route('barang.index')
        );


        $barang->refresh();


        $this->assertEquals(
            10,
            $barang->stok_minimum
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barang->id,
                'stok_minimum' => 10,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOK MINIMUM TIDAK BOLEH NEGATIF
    |--------------------------------------------------------------------------
    */

    public function test_stok_minimum_tidak_boleh_negatif(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Patchcord',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $response = $this->actingAs($admin)
            ->from('/barang/create')
            ->post('/barang', [
                'kode_barang' => 'PCH-MIN-001',
                'nama_barang' => 'Patchcord Negatif',
                'kategori_id' => $kategoriId,
                'merk' => 'Generic',
                'stok' => 10,
                'satuan' => 'pcs',
                'lokasi' => 'Gudang C',
                'stok_minimum' => -1,
            ]);


        $response->assertRedirect(
            '/barang/create'
        );


        $response->assertSessionHasErrors(
            'stok_minimum'
        );


        $this->assertDatabaseMissing(
            'barangs',
            [
                'kode_barang' => 'PCH-MIN-001',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOK MINIMUM WAJIB DIISI
    |--------------------------------------------------------------------------
    */

    public function test_stok_minimum_wajib_diisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Splitter',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $response = $this->actingAs($admin)
            ->from('/barang/create')
            ->post('/barang', [
                'kode_barang' => 'SPL-MIN-001',
                'nama_barang' => 'Splitter Tanpa Minimum',
                'kategori_id' => $kategoriId,
                'merk' => 'Generic',
                'stok' => 10,
                'satuan' => 'pcs',
                'lokasi' => 'Gudang C',
                'stok_minimum' => '',
            ]);


        $response->assertRedirect(
            '/barang/create'
        );


        $response->assertSessionHasErrors(
            'stok_minimum'
        );


        $this->assertDatabaseMissing(
            'barangs',
            [
                'kode_barang' => 'SPL-MIN-001',
            ]
        );
    }
}