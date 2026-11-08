<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokMenipisDashboardTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD HARUS MENGGUNAKAN STOK MINIMUM MASING-MASING BARANG
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_mendeteksi_stok_menipis_berdasarkan_stok_minimum_masing_masing_barang(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriId = DB::table('kategoris')
            ->insertGetId([
                'nama_kategori' => 'Perangkat Jaringan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG 1
        |--------------------------------------------------------------------------
        |
        | Stok = 12
        | Minimum = 15
        |
        | Meskipun stok lebih besar dari 10,
        | barang ini HARUS dianggap menipis.
        |
        */

        DB::table('barangs')->insert([
            'kode_barang' => 'LOW-001',
            'nama_barang' => 'Router Stok Menipis',
            'kategori_id' => $kategoriId,
            'merk' => 'MikroTik',
            'stok' => 12,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG 2
        |--------------------------------------------------------------------------
        |
        | Stok = 9
        | Minimum = 5
        |
        | Walaupun stok <= 10,
        | barang ini masih AMAN.
        |
        */

        DB::table('barangs')->insert([
            'kode_barang' => 'SAFE-001',
            'nama_barang' => 'ONU Stok Aman',
            'kategori_id' => $kategoriId,
            'merk' => 'Huawei',
            'stok' => 9,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG 3
        |--------------------------------------------------------------------------
        |
        | Stok = 0
        | Minimum = 3
        |
        | Barang ini HARUS masuk peringatan.
        |
        */

        DB::table('barangs')->insert([
            'kode_barang' => 'CRIT-001',
            'nama_barang' => 'Patchcord Habis',
            'kategori_id' => $kategoriId,
            'merk' => 'Generic',
            'stok' => 0,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang B',
            'stok_minimum' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUKA DASHBOARD
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                route('dashboard')
            );


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        $response->assertOk();


        /*
        |--------------------------------------------------------------------------
        | JUMLAH STOK MENIPIS
        |--------------------------------------------------------------------------
        |
        | Harus hanya:
        |
        | LOW-001
        | CRIT-001
        |
        */

        $response->assertViewHas(
            'jumlahStokMenipis',
            2
        );


        /*
        |--------------------------------------------------------------------------
        | KOLEKSI STOK MENIPIS
        |--------------------------------------------------------------------------
        */

        $response->assertViewHas(
            'stokMenipis',
            function ($barangs) {

                $kodeBarang = $barangs
                    ->pluck('kode_barang')
                    ->all();


                return count($kodeBarang) === 2

                    && in_array(
                        'LOW-001',
                        $kodeBarang
                    )

                    && in_array(
                        'CRIT-001',
                        $kodeBarang
                    )

                    && !in_array(
                        'SAFE-001',
                        $kodeBarang
                    );
            }
        );
    }
}