<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KategoriManagementTest extends TestCase
{
    use RefreshDatabase;


    public function test_admin_bisa_membuka_halaman_kategori(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);


        $response = $this
            ->actingAs($admin)
            ->get('/kategori');


        $response->assertStatus(200);
    }


    public function test_admin_bisa_menambah_kategori(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin2@test.com',
            'role' => 'admin',
        ]);


        $response = $this
            ->actingAs($admin)
            ->post('/kategori', [
                'nama_kategori' => 'Router',
            ]);


        $response->assertRedirect('/kategori');


        $this->assertDatabaseHas('kategoris', [
            'nama_kategori' => 'Router',
        ]);
    }


    public function test_admin_bisa_mengedit_kategori(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin3@test.com',
            'role' => 'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' => 'Modem Lama',
        ]);


        $response = $this
            ->actingAs($admin)
            ->put(
                '/kategori/' . $kategori->id,
                [
                    'nama_kategori' => 'Modem',
                ]
            );


        $response->assertRedirect('/kategori');


        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'nama_kategori' => 'Modem',
        ]);
    }


    public function test_admin_bisa_menghapus_kategori_kosong(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin4@test.com',
            'role' => 'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Kosong',
        ]);


        $response = $this
            ->actingAs($admin)
            ->delete(
                '/kategori/' . $kategori->id
            );


        $response->assertRedirect('/kategori');


        $this->assertDatabaseMissing('kategoris', [
            'id' => $kategori->id,
        ]);
    }


    public function test_kategori_yang_masih_dipakai_barang_tidak_bisa_dihapus(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin5@test.com',
            'role' => 'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);


        DB::table('barangs')->insert([
            'kode_barang' => 'MDM-001',
            'nama_barang' => 'Modem Test',
            'kategori_id' => $kategori->id,
            'merk' => 'Test',
            'stok' => 10,
            'satuan' => 'pcs',
            'lokasi' => 'Gudang Test',
            'stok_minimum' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        $response = $this
            ->actingAs($admin)
            ->delete(
                '/kategori/' . $kategori->id
            );


        $response->assertRedirect('/kategori');


        $response->assertSessionHas('error');


        // Kategori harus tetap ada
        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'nama_kategori' => 'Modem',
        ]);


        // Barang juga harus tetap ada
        $this->assertDatabaseHas('barangs', [
            'kode_barang' => 'MDM-001',
            'kategori_id' => $kategori->id,
        ]);
    }
}