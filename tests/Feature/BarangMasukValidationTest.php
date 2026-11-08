<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangMasukValidationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | JUMLAH BARANG MASUK 0 DAN NEGATIF HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_jumlah_barang_masuk_nol_dan_negatif_ditolak_tanpa_mengubah_stok_atau_membuat_audit(): void
    {
        $admin = User::factory()->create([
            'name' =>
                'Admin Validasi Barang Masuk',

            'email' =>
                'admin.validasi.masuk@example.com',

            'role' =>
                'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Barang Masuk',
        ]);


        $barang = Barang::create([
            'kode_barang' =>
                'BRG-MASUK-VALIDASI-001',

            'nama_barang' =>
                'Router Barang Masuk Test',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Test Brand',

            'stok' =>
                10,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Test',

            'stok_minimum' =>
                2,
        ]);


        $stokAwal =
            (int) $barang->stok;

        $jumlahTransaksiAwal =
            BarangMasuk::count();

        $jumlahAuditAwal =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | JUMLAH = 0
        |--------------------------------------------------------------------------
        */

        $responseNol = $this
            ->actingAs($admin)
            ->from(
                route('barang-masuk.create')
            )
            ->post(
                route('barang-masuk.store'),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        0,

                    'supplier' =>
                        'Supplier Test',

                    'keterangan' =>
                        'Pengujian jumlah nol',
                ]
            );


        $responseNol->assertRedirect(
            route('barang-masuk.create')
        );

        $responseNol->assertSessionHasErrors(
            'jumlah'
        );


        $barang->refresh();


        $this->assertSame(
            $stokAwal,
            (int) $barang->stok
        );

        $this->assertSame(
            $jumlahTransaksiAwal,
            BarangMasuk::count()
        );

        $this->assertSame(
            $jumlahAuditAwal,
            ActivityLog::count()
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH NEGATIF
        |--------------------------------------------------------------------------
        */

        $responseNegatif = $this
            ->actingAs($admin)
            ->from(
                route('barang-masuk.create')
            )
            ->post(
                route('barang-masuk.store'),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        -5,

                    'supplier' =>
                        'Supplier Test',

                    'keterangan' =>
                        'Pengujian jumlah negatif',
                ]
            );


        $responseNegatif->assertRedirect(
            route('barang-masuk.create')
        );

        $responseNegatif->assertSessionHasErrors(
            'jumlah'
        );


        $barang->refresh();


        $this->assertSame(
            $stokAwal,
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


        $this->assertDatabaseMissing(
            'barang_masuks',
            [
                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    0,
            ]
        );


        $this->assertDatabaseMissing(
            'barang_masuks',
            [
                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    -5,
            ]
        );


        $this->assertSame(
            $jumlahTransaksiAwal,
            BarangMasuk::count()
        );


        $this->assertSame(
            $jumlahAuditAwal,
            ActivityLog::count()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE JUMLAH BARANG MASUK HARUS MENGHITUNG SELISIH STOK
    |--------------------------------------------------------------------------
    */

    public function test_update_jumlah_barang_masuk_menghitung_stok_dari_selisih_transaksi_lama_dan_baru(): void
    {
        $admin = User::factory()->create([
            'name' =>
                'Admin Update Barang Masuk',

            'email' =>
                'admin.update.masuk@example.com',

            'role' =>
                'admin',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Update Barang Masuk',
        ]);


        /*
        |--------------------------------------------------------------------------
        | STOK SEKARANG = 15
        | TRANSAKSI LAMA = 5
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BRG-MASUK-UPDATE-001',

            'nama_barang' =>
                'Switch Update Barang Masuk',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Update Brand',

            'stok' =>
                15,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Update',

            'stok_minimum' =>
                2,
        ]);


        $barangMasuk = BarangMasuk::create([
            'barang_id' =>
                $barang->id,

            'tanggal' =>
                '2026-10-10',

            'jumlah' =>
                5,

            'supplier' =>
                'Supplier Lama',

            'keterangan' =>
                'Transaksi barang masuk lama',
        ]);


        /*
        |--------------------------------------------------------------------------
        | UPDATE 5 MENJADI 8
        |
        | 15 - 5 + 8 = 18
        |--------------------------------------------------------------------------
        */

        $response = $this
            ->actingAs($admin)
            ->put(
                route(
                    'barang-masuk.update',
                    $barangMasuk
                ),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        8,

                    'supplier' =>
                        'Supplier Baru',

                    'keterangan' =>
                        'Transaksi barang masuk diperbarui',
                ]
            );


        $response->assertRedirect(
            route('barang-masuk.index')
        );


        $barang->refresh();


        $this->assertSame(
            18,
            (int) $barang->stok
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barang->id,

                'stok' =>
                    18,
            ]
        );


        $barangMasuk->refresh();


        $this->assertSame(
            8,
            (int) $barangMasuk->jumlah
        );


        $this->assertSame(
            'Supplier Baru',
            $barangMasuk->supplier
        );


        $this->assertSame(
            'Transaksi barang masuk diperbarui',
            $barangMasuk->keterangan
        );


        $this->assertDatabaseHas(
            'barang_masuks',
            [
                'id' =>
                    $barangMasuk->id,

                'barang_id' =>
                    $barang->id,

                'jumlah' =>
                    8,

                'supplier' =>
                    'Supplier Baru',

                'keterangan' =>
                    'Transaksi barang masuk diperbarui',
            ]
        );


        $this->assertNotSame(
            23,
            (int) $barang->stok
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG MASUK TIDAK BOLEH MEMBUAT STOK NEGATIF
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_masuk_ditolak_jika_koreksi_jumlah_membuat_stok_menjadi_negatif(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' =>
                'Admin Koreksi Stok Masuk',

            'email' =>
                'admin.koreksi.stok.masuk@example.com',

            'role' =>
                'admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Koreksi Stok Masuk',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KONDISI BARANG
        |--------------------------------------------------------------------------
        |
        | Transaksi Barang Masuk lama = 10
        |
        | Tetapi stok saat ini tinggal = 3.
        |
        | Artinya sebagian barang dari transaksi tersebut sudah digunakan.
        |
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BRG-MASUK-KOREKSI-001',

            'nama_barang' =>
                'Router Koreksi Stok',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Koreksi Brand',

            'stok' =>
                3,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Koreksi',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI BARANG MASUK LAMA = 10
        |--------------------------------------------------------------------------
        */

        $barangMasuk = BarangMasuk::create([
            'barang_id' =>
                $barang->id,

            'tanggal' =>
                '2026-10-01',

            'jumlah' =>
                10,

            'supplier' =>
                'Supplier Original',

            'keterangan' =>
                'Barang masuk original',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT SEBELUM UPDATE
        |--------------------------------------------------------------------------
        */

        $stokSebelum =
            (int) $barang->stok;

        $barangIdSebelum =
            (int) $barangMasuk->barang_id;

        $jumlahSebelum =
            (int) $barangMasuk->jumlah;

        $tanggalSebelum =
            $barangMasuk->tanggal;

        $supplierSebelum =
            $barangMasuk->supplier;

        $keteranganSebelum =
            $barangMasuk->keterangan;

        $jumlahTransaksiSebelum =
            BarangMasuk::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | COBA KOREKSI JUMLAH 10 MENJADI 2
        |--------------------------------------------------------------------------
        |
        | Jika diterapkan:
        |
        | stok baru = stok sekarang - jumlah lama + jumlah baru
        |
        | stok baru = 3 - 10 + 2
        |
        | stok baru = -5
        |
        | Karena hasil negatif, UPDATE HARUS DITOLAK.
        |
        */

        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'barang-masuk.edit',
                    $barangMasuk
                )
            )
            ->put(
                route(
                    'barang-masuk.update',
                    $barangMasuk
                ),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        2,

                    'supplier' =>
                        'Supplier Koreksi',

                    'keterangan' =>
                        'Percobaan koreksi yang menyebabkan stok negatif',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS DITOLAK DAN KEMBALI KE FORM EDIT
        |--------------------------------------------------------------------------
        */

        $response->assertRedirect(
            route(
                'barang-masuk.edit',
                $barangMasuk
            )
        );


        /*
        |--------------------------------------------------------------------------
        | HARUS ADA ERROR JUMLAH
        |--------------------------------------------------------------------------
        */

        $response->assertSessionHasErrors(
            'jumlah'
        );


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


        $this->assertSame(
            3,
            (int) $barang->stok
        );


        $this->assertDatabaseHas(
            'barangs',
            [
                'id' =>
                    $barang->id,

                'stok' =>
                    3,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | STOK NEGATIF TIDAK BOLEH TERSIMPAN
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barangs',
            [
                'id' =>
                    $barang->id,

                'stok' =>
                    -5,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI LAMA HARUS TETAP
        |--------------------------------------------------------------------------
        */

        $barangMasuk->refresh();


        $this->assertSame(
            $barangIdSebelum,
            (int) $barangMasuk->barang_id
        );


        $this->assertSame(
            $jumlahSebelum,
            (int) $barangMasuk->jumlah
        );


        $this->assertSame(
            10,
            (int) $barangMasuk->jumlah
        );


        $this->assertEquals(
            $tanggalSebelum,
            $barangMasuk->tanggal
        );


        $this->assertSame(
            $supplierSebelum,
            $barangMasuk->supplier
        );


        $this->assertSame(
            $keteranganSebelum,
            $barangMasuk->keterangan
        );


        /*
        |--------------------------------------------------------------------------
        | DATA KOREKSI ILEGAL TIDAK BOLEH TERSIMPAN
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_masuks',
            [
                'id' =>
                    $barangMasuk->id,

                'jumlah' =>
                    2,

                'supplier' =>
                    'Supplier Koreksi',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH TRANSAKSI TETAP
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahTransaksiSebelum,
            BarangMasuk::count()
        );


        /*
        |--------------------------------------------------------------------------
        | TIDAK BOLEH ADA AUDIT PALSU
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahAuditSebelum,
            ActivityLog::count()
        );
    }
}