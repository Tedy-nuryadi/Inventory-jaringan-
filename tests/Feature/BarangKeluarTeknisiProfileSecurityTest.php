<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangKeluarTeknisiProfileSecurityTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TANPA PROFIL TIDAK BOLEH MEMBUKA DAFTAR BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tanpa_profil_teknisi_mendapat_403_saat_membuka_barang_keluar(): void
    {
        $userTeknisi = User::factory()->create([
            'name' =>
                'Teknisi Tanpa Profil',

            'email' =>
                'teknisi.tanpa.profil@example.com',

            'role' =>
                'teknisi',
        ]);


        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        $response = $this
            ->actingAs($userTeknisi)
            ->get(
                route('barang-keluar.index')
            );


        $response->assertForbidden();


        $this->assertDatabaseMissing(
            'teknisis',
            [
                'user_id' =>
                    $userTeknisi->id,
            ]
        );


        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangKeluar::count()
        );


        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TANPA PROFIL TIDAK BOLEH MEMBUKA FORM TAMBAH
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tanpa_profil_teknisi_mendapat_403_saat_membuka_form_tambah_barang_keluar(): void
    {
        $userTeknisi = User::factory()->create([
            'name' =>
                'Teknisi Tanpa Profil Create',

            'email' =>
                'teknisi.tanpa.profil.create@example.com',

            'role' =>
                'teknisi',
        ]);


        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        $response = $this
            ->actingAs($userTeknisi)
            ->get(
                route('barang-keluar.create')
            );


        $response->assertForbidden();


        $this->assertDatabaseMissing(
            'teknisis',
            [
                'user_id' =>
                    $userTeknisi->id,
            ]
        );


        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangKeluar::count()
        );


        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TANPA PROFIL TIDAK BOLEH POST LANGSUNG BARANG KELUAR
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tanpa_profil_teknisi_tidak_bisa_post_barang_keluar_secara_langsung(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI TANPA PROFIL
        |--------------------------------------------------------------------------
        */

        $userTeknisi = User::factory()->create([
            'name' =>
                'Teknisi Tanpa Profil Store',

            'email' =>
                'teknisi.tanpa.profil.store@example.com',

            'role' =>
                'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN TIDAK ADA PROFIL TEKNISI
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'teknisis',
            [
                'user_id' =>
                    $userTeknisi->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Security Store',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BRG-NO-PROFILE-001',

            'nama_barang' =>
                'Router No Profile Test',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Security Brand',

            'stok' =>
                10,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Security',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT KONDISI AWAL
        |--------------------------------------------------------------------------
        */

        $stokSebelum =
            (int) $barang->stok;

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | POST LANGSUNG KE ENDPOINT STORE
        |--------------------------------------------------------------------------
        |
        | Tidak mengirim teknisi_id karena akun role teknisi seharusnya
        | otomatis memakai profil teknisi dari user yang sedang login.
        |
        | Tetapi user ini sengaja tidak memiliki profil teknisi.
        |
        */

        $response = $this
            ->actingAs($userTeknisi)
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        3,

                    'keperluan' =>
                        'Percobaan POST tanpa profil teknisi',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS 403
        |--------------------------------------------------------------------------
        */

        $response->assertForbidden();


        /*
        |--------------------------------------------------------------------------
        | STOK TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $barang->refresh();


        $this->assertSame(
            $stokSebelum,
            (int) $barang->stok
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barang->id,

                'stok' =>
                    10,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI TIDAK BOLEH DIBUAT
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangKeluar::count()
        );


        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    3,

                'keperluan' =>
                    'Percobaan POST tanpa profil teknisi',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | AUDIT PALSU TIDAK BOLEH DIBUAT
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );


        /*
        |--------------------------------------------------------------------------
        | SISTEM TIDAK BOLEH OTOMATIS MEMBUAT PROFIL TEKNISI
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            0,
            Teknisi::where(
                'user_id',
                $userTeknisi->id
            )->count()
        );
    }
}