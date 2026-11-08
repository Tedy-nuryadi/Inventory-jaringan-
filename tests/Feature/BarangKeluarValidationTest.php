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

class BarangKeluarValidationTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | BARANG KELUAR TIDAK BOLEH MELEBIHI STOK
    |--------------------------------------------------------------------------
    */

    public function test_barang_keluar_tidak_boleh_melebihi_stok_dan_tidak_membuat_transaksi_atau_audit_palsu(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Validasi Stok',
            'email' => 'admin.validasi.stok@example.com',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Jaringan',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Validasi Stok',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'BRG-VALIDASI-001',
            'nama_barang' => 'Router Test Validasi',
            'kategori_id' => $kategori->id,
            'merk' => 'Test Brand',
            'stok' => 5,
            'satuan' => 'Unit',
            'lokasi' => 'Gudang Test',
            'stok_minimum' => 2,
        ]);

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        $response = $this
            ->actingAs($admin)
            ->from(
                route('barang-keluar.create')
            )
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 6,
                    'keperluan' => 'Pengujian validasi stok',
                ]
            );


        $response->assertRedirect(
            route('barang-keluar.create')
        );

        $response->assertSessionHasErrors(
            'jumlah'
        );


        $barang->refresh();


        $this->assertSame(
            5,
            (int) $barang->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barang->id,
                'stok' => 5,
            ]
        );

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'barang_id' => $barang->id,
                'jumlah' => 6,
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
    | JUMLAH 0 DAN NEGATIF HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_jumlah_barang_keluar_nol_dan_negatif_ditolak_tanpa_mengubah_stok_atau_membuat_audit(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Validasi Jumlah',
            'email' => 'admin.validasi.jumlah@example.com',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Validasi Jumlah',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Validasi Jumlah',
            'telepon' => '081298765432',
            'alamat' => 'Tangerang',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'BRG-JUMLAH-001',
            'nama_barang' => 'Switch Test Validasi',
            'kategori_id' => $kategori->id,
            'merk' => 'Test Brand',
            'stok' => 10,
            'satuan' => 'Unit',
            'lokasi' => 'Gudang Test',
            'stok_minimum' => 2,
        ]);

        $stokAwal =
            (int) $barang->stok;

        $jumlahTransaksiAwal =
            BarangKeluar::count();

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
                route('barang-keluar.create')
            )
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 0,
                    'keperluan' => 'Pengujian jumlah nol',
                ]
            );


        $responseNol->assertRedirect(
            route('barang-keluar.create')
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
            BarangKeluar::count()
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
                route('barang-keluar.create')
            )
            ->post(
                route('barang-keluar.store'),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => -3,
                    'keperluan' => 'Pengujian jumlah negatif',
                ]
            );


        $responseNegatif->assertRedirect(
            route('barang-keluar.create')
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
                'id' => $barang->id,
                'stok' => 10,
            ]
        );

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'barang_id' => $barang->id,
                'jumlah' => 0,
            ]
        );

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'barang_id' => $barang->id,
                'jumlah' => -3,
            ]
        );

        $this->assertSame(
            $jumlahTransaksiAwal,
            BarangKeluar::count()
        );

        $this->assertSame(
            $jumlahAuditAwal,
            ActivityLog::count()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BARANG YANG SAMA TIDAK BOLEH MELEBIHI STOK
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_keluar_pada_barang_yang_sama_tidak_boleh_membuat_stok_minus_atau_audit_palsu(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Update Stok',
            'email' => 'admin.update.stok@example.com',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Update Stok',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Update Stok',
            'telepon' => '081211112222',
            'alamat' => 'Tangerang',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'BRG-UPDATE-001',
            'nama_barang' => 'Router Update Test',
            'kategori_id' => $kategori->id,
            'merk' => 'Test Brand',
            'stok' => 2,
            'satuan' => 'Unit',
            'lokasi' => 'Gudang Update',
            'stok_minimum' => 1,
        ]);

        $barangKeluar = BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Admin Lama',
            'keperluan' => 'Instalasi jaringan',
            'foto' => null,
        ]);

        $stokSebelum =
            (int) $barang->stok;

        $jumlahLama =
            (int) $barangKeluar->jumlah;

        $tanggalLama =
            $barangKeluar->tanggal;

        $keperluanLama =
            $barangKeluar->keperluan;

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'barang-keluar.edit',
                    $barangKeluar
                )
            )
            ->put(
                route(
                    'barang-keluar.update',
                    $barangKeluar
                ),
                [
                    'barang_id' => $barang->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 6,
                    'keperluan' => 'Jumlah update yang melebihi stok',
                ]
            );


        $response->assertRedirect(
            route(
                'barang-keluar.edit',
                $barangKeluar
            )
        );

        $response->assertSessionHasErrors(
            'jumlah'
        );


        $barang->refresh();


        $this->assertSame(
            $stokSebelum,
            (int) $barang->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barang->id,
                'stok' => 2,
            ]
        );


        $barangKeluar->refresh();


        $this->assertSame(
            $jumlahLama,
            (int) $barangKeluar->jumlah
        );

        $this->assertEquals(
            $tanggalLama,
            $barangKeluar->tanggal
        );

        $this->assertSame(
            $keperluanLama,
            $barangKeluar->keperluan
        );

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'barang_id' => $barang->id,
                'jumlah' => 3,
                'keperluan' => 'Instalasi jaringan',
            ]
        );

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'jumlah' => 6,
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
    | PINDAH BARANG: STOK BARANG BARU TIDAK CUKUP
    |--------------------------------------------------------------------------
    */

    public function test_update_barang_keluar_ke_barang_lain_dengan_stok_tidak_cukup_harus_rollback_semua_perubahan(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Pindah Barang',
            'email' => 'admin.pindah.barang@example.com',
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Perangkat Pindah Barang',
        ]);

        $teknisi = Teknisi::create([
            'nama' => 'Teknisi Pindah Barang',
            'telepon' => '081233344455',
            'alamat' => 'Tangerang',
        ]);

        $barangA = Barang::create([
            'kode_barang' => 'BRG-PINDAH-A',
            'nama_barang' => 'Router Barang A',
            'kategori_id' => $kategori->id,
            'merk' => 'Brand A',
            'stok' => 7,
            'satuan' => 'Unit',
            'lokasi' => 'Rak A',
            'stok_minimum' => 2,
        ]);

        $barangB = Barang::create([
            'kode_barang' => 'BRG-PINDAH-B',
            'nama_barang' => 'Router Barang B',
            'kategori_id' => $kategori->id,
            'merk' => 'Brand B',
            'stok' => 2,
            'satuan' => 'Unit',
            'lokasi' => 'Rak B',
            'stok_minimum' => 1,
        ]);

        $barangKeluar = BarangKeluar::create([
            'barang_id' => $barangA->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Admin Lama',
            'keperluan' => 'Instalasi awal',
            'foto' => null,
        ]);

        $stokBarangASebelum =
            (int) $barangA->stok;

        $stokBarangBSebelum =
            (int) $barangB->stok;

        $barangIdLama =
            (int) $barangKeluar->barang_id;

        $jumlahLama =
            (int) $barangKeluar->jumlah;

        $tanggalLama =
            $barangKeluar->tanggal;

        $keperluanLama =
            $barangKeluar->keperluan;

        $jumlahTransaksiSebelum =
            BarangKeluar::count();

        $jumlahAuditSebelum =
            ActivityLog::count();


        $response = $this
            ->actingAs($admin)
            ->from(
                route(
                    'barang-keluar.edit',
                    $barangKeluar
                )
            )
            ->put(
                route(
                    'barang-keluar.update',
                    $barangKeluar
                ),
                [
                    'barang_id' => $barangB->id,
                    'teknisi_id' => $teknisi->id,
                    'tanggal' => '2026-10-12',
                    'jumlah' => 4,
                    'keperluan' => 'Percobaan pindah ke Barang B',
                ]
            );


        $response->assertRedirect(
            route(
                'barang-keluar.edit',
                $barangKeluar
            )
        );

        $response->assertSessionHasErrors(
            'jumlah'
        );


        $barangA->refresh();
        $barangB->refresh();


        $this->assertSame(
            $stokBarangASebelum,
            (int) $barangA->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barangA->id,
                'stok' => 7,
            ]
        );

        $this->assertSame(
            $stokBarangBSebelum,
            (int) $barangB->stok
        );

        $this->assertDatabaseHas(
            'barangs',
            [
                'id' => $barangB->id,
                'stok' => 2,
            ]
        );


        $barangKeluar->refresh();


        $this->assertSame(
            $barangIdLama,
            (int) $barangKeluar->barang_id
        );

        $this->assertSame(
            $barangA->id,
            (int) $barangKeluar->barang_id
        );

        $this->assertSame(
            $jumlahLama,
            (int) $barangKeluar->jumlah
        );

        $this->assertEquals(
            $tanggalLama,
            $barangKeluar->tanggal
        );

        $this->assertSame(
            $keperluanLama,
            $barangKeluar->keperluan
        );

        $this->assertDatabaseHas(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'barang_id' => $barangA->id,
                'jumlah' => 3,
                'keperluan' => 'Instalasi awal',
            ]
        );

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'id' => $barangKeluar->id,
                'barang_id' => $barangB->id,
                'jumlah' => 4,
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
    | TEKNISI TIDAK BOLEH UPDATE TRANSAKSI MILIK TEKNISI LAIN
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_mengubah_transaksi_barang_keluar_milik_teknisi_lain(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI A
        |--------------------------------------------------------------------------
        */

        $userTeknisiA = User::factory()->create([
            'name' =>
                'Teknisi A User',

            'email' =>
                'teknisi.a.security@example.com',

            'role' =>
                'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI A
        |--------------------------------------------------------------------------
        */

        $teknisiA = Teknisi::create([
            'user_id' =>
                $userTeknisiA->id,

            'nama' =>
                'Teknisi A',

            'telepon' =>
                '081200000001',

            'alamat' =>
                'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USER TEKNISI B
        |--------------------------------------------------------------------------
        */

        $userTeknisiB = User::factory()->create([
            'name' =>
                'Teknisi B User',

            'email' =>
                'teknisi.b.security@example.com',

            'role' =>
                'teknisi',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROFIL TEKNISI B
        |--------------------------------------------------------------------------
        */

        $teknisiB = Teknisi::create([
            'user_id' =>
                $userTeknisiB->id,

            'nama' =>
                'Teknisi B',

            'telepon' =>
                '081200000002',

            'alamat' =>
                'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' =>
                'Perangkat Security',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' =>
                'BRG-SECURITY-001',

            'nama_barang' =>
                'Router Security Test',

            'kategori_id' =>
                $kategori->id,

            'merk' =>
                'Security Brand',

            'stok' =>
                8,

            'satuan' =>
                'Unit',

            'lokasi' =>
                'Gudang Security',

            'stok_minimum' =>
                2,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI MILIK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $barangKeluar = BarangKeluar::create([
            'barang_id' =>
                $barang->id,

            'teknisi_id' =>
                $teknisiB->id,

            'tanggal' =>
                '2026-10-11',

            'jumlah' =>
                2,

            'pengguna' =>
                $userTeknisiB->name,

            'keperluan' =>
                'Instalasi milik Teknisi B',

            'foto' =>
                null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT
        |--------------------------------------------------------------------------
        */

        $stokSebelum =
            (int) $barang->stok;

        $barangIdSebelum =
            (int) $barangKeluar->barang_id;

        $teknisiIdSebelum =
            (int) $barangKeluar->teknisi_id;

        $jumlahSebelum =
            (int) $barangKeluar->jumlah;

        $tanggalSebelum =
            $barangKeluar->tanggal;

        $keperluanSebelum =
            $barangKeluar->keperluan;

        $jumlahAuditSebelum =
            ActivityLog::count();


        /*
        |--------------------------------------------------------------------------
        | TEKNISI A COBA UPDATE TRANSAKSI TEKNISI B
        |--------------------------------------------------------------------------
        |
        | Teknisi tidak mengirim teknisi_id karena controller menggunakan
        | profil teknisi dari akun login.
        |
        */

        $response = $this
            ->actingAs($userTeknisiA)
            ->put(
                route(
                    'barang-keluar.update',
                    $barangKeluar
                ),
                [
                    'barang_id' =>
                        $barang->id,

                    'tanggal' =>
                        '2026-10-12',

                    'jumlah' =>
                        1,

                    'keperluan' =>
                        'Percobaan perubahan ilegal',
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | HARUS 403 FORBIDDEN
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


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI HARUS TETAP MILIK TEKNISI B
        |--------------------------------------------------------------------------
        */

        $barangKeluar->refresh();


        $this->assertSame(
            $barangIdSebelum,
            (int) $barangKeluar->barang_id
        );


        $this->assertSame(
            $teknisiIdSebelum,
            (int) $barangKeluar->teknisi_id
        );


        $this->assertSame(
            $teknisiB->id,
            (int) $barangKeluar->teknisi_id
        );


        /*
        |--------------------------------------------------------------------------
        | DATA TRANSAKSI TIDAK BOLEH BERUBAH
        |--------------------------------------------------------------------------
        */

        $this->assertSame(
            $jumlahSebelum,
            (int) $barangKeluar->jumlah
        );


        $this->assertEquals(
            $tanggalSebelum,
            $barangKeluar->tanggal
        );


        $this->assertSame(
            $keperluanSebelum,
            $barangKeluar->keperluan
        );


        /*
        |--------------------------------------------------------------------------
        | DATA ILEGAL TIDAK BOLEH TERSIMPAN
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseMissing(
            'barang_keluars',
            [
                'id' =>
                    $barangKeluar->id,

                'jumlah' =>
                    1,

                'keperluan' =>
                    'Percobaan perubahan ilegal',
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
        | PROFIL TEKNISI A TETAP ADA
        |--------------------------------------------------------------------------
        |
        | Assertion ini juga memastikan relasi akun Teknisi A dibuat dengan benar.
        |
        */

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' =>
                    $teknisiA->id,

                'user_id' =>
                    $userTeknisiA->id,
            ]
        );
    }
}