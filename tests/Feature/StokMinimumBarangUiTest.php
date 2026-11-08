<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokMinimumBarangUiTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH BARANG
    |--------------------------------------------------------------------------
    */

    public function test_form_tambah_barang_memiliki_field_stok_minimum(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        DB::table('kategoris')->insert([
            'nama_kategori' => 'Aksesoris',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        $response = $this->actingAs($admin)
            ->get(
                route('barang.create')
            );


        $response->assertOk();


        $response->assertSee(
            'Stok Minimum'
        );


        $response->assertSee(
            'name="stok_minimum"',
            false
        );


        $response->assertSee(
            'value="5"',
            false
        );


        $response->assertSee(
            'min="0"',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT BARANG
    |--------------------------------------------------------------------------
    */

    public function test_form_edit_barang_memiliki_field_stok_minimum_dan_menampilkan_nilainya(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Perangkat Jaringan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $barang = Barang::create([
            'kode_barang' => 'TEST-MIN-001',
            'nama_barang' => 'Router Test',
            'kategori_id' => $kategoriId,
            'merk' => 'MikroTik',
            'stok' => 8,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 6,
        ]);


        $response = $this->actingAs($admin)
            ->get(
                route(
                    'barang.edit',
                    $barang
                )
            );


        $response->assertOk();


        $response->assertSee(
            'Stok Minimum'
        );


        $response->assertSee(
            'name="stok_minimum"',
            false
        );


        $response->assertSee(
            'value="6"',
            false
        );


        $response->assertSee(
            'min="0"',
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT MEMPERTAHANKAN DATA LAIN
    |--------------------------------------------------------------------------
    */

    public function test_form_edit_barang_tetap_menampilkan_lokasi_dan_satuan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Fiber Optik',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        $barang = Barang::create([
            'kode_barang' => 'FO-001',
            'nama_barang' => 'Patchcord LC',
            'kategori_id' => $kategoriId,
            'merk' => 'MyLink',
            'stok' => 10,
            'satuan' => 'pcs',
            'lokasi' => 'Loker LT3',
            'stok_minimum' => 3,
        ]);


        $response = $this->actingAs($admin)
            ->get(
                route(
                    'barang.edit',
                    $barang
                )
            );


        $response->assertOk();


        $response->assertSee(
            'Lokasi Penyimpanan'
        );


        $response->assertSee(
            'Loker LT3'
        );


        $response->assertSee(
            'value="pcs"',
            false
        );
    }
}