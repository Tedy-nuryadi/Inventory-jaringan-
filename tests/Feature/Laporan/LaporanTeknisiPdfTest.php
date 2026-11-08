<?php

namespace Tests\Feature\Laporan;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Kategori;
use App\Models\Teknisi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LaporanTeknisiPdfTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN BISA DOWNLOAD PDF
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_download_pdf_laporan_pemakaian_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi PDF',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-TEK-PDF-001',
            'nama_barang' => 'Modem Teknisi PDF',
            'kategori_id' => $kategori->id,
            'merk' => 'CDATA',
            'stok' => 30,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisi->id,
            'tanggal' => '2026-10-10',
            'jumlah' => 3,
            'pengguna' => 'Pelanggan PDF Teknisi',
            'keperluan' => 'Pemasangan perangkat',
            'tujuan' => 'Site PDF Teknisi',
            'foto' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get('/laporan/teknisi/pdf');

        $response->assertOk();

        $response->assertDownload(
            'laporan-pemakaian-teknisi.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH DOWNLOAD PDF GLOBAL
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_download_pdf_laporan_pemakaian_teknisi_global(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Terbatas PDF',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/laporan/teknisi/pdf');

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | PDF MENGIKUTI FILTER PERIODE + TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_pdf_laporan_pemakaian_teknisi_mengikuti_filter_yang_dipilih(): void
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
        | TEKNISI ANDI
        |--------------------------------------------------------------------------
        */

        $userAndi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiAndi = Teknisi::create([
            'user_id' => $userAndi->id,
            'nama' => 'Teknisi Andi PDF Filter',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI BUDI
        |--------------------------------------------------------------------------
        */

        $userBudi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiBudi = Teknisi::create([
            'user_id' => $userBudi->id,
            'nama' => 'Teknisi Budi PDF Filter',
            'telepon' => '084444444444',
            'alamat' => 'Serang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = Kategori::create([
            'nama_kategori' => 'Router',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BARANG
        |--------------------------------------------------------------------------
        */

        $barang = Barang::create([
            'kode_barang' => 'RTR-PDF-FILTER-001',
            'nama_barang' => 'Router Filter PDF Teknisi',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | TARGET:
        | ANDI + DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Target PDF Teknisi',
            'keperluan' => 'Target PDF Teknisi',
            'tujuan' => 'Site Target PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI TAPI DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 7,
            'pengguna' => 'Pelanggan September PDF',
            'keperluan' => 'Di luar periode PDF',
            'tujuan' => 'Site September PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUDI DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 9,
            'pengguna' => 'Pelanggan Budi PDF',
            'keperluan' => 'Teknisi berbeda PDF',
            'tujuan' => 'Site Budi PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | MOCK PDF YANG BENAR-BENAR BERTIPE DomPdf
        |--------------------------------------------------------------------------
        */

        $mockPdf = Mockery::mock(
            DomPdf::class
        );


        /*
        |--------------------------------------------------------------------------
        | SET PAPER
        |--------------------------------------------------------------------------
        */

        $mockPdf
            ->shouldReceive('setPaper')
            ->once()
            ->with(
                'a4',
                'landscape'
            )
            ->andReturnSelf();


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $mockPdf
            ->shouldReceive('download')
            ->once()
            ->with(
                'laporan-pemakaian-teknisi.pdf'
            )
            ->andReturn(
                response(
                    'FAKE PDF',
                    200,
                    [
                        'Content-Type' =>
                            'application/pdf',

                        'Content-Disposition' =>
                            'attachment; filename="laporan-pemakaian-teknisi.pdf"',
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | PERIKSA DATA YANG MASUK KE VIEW PDF
        |--------------------------------------------------------------------------
        */

        Pdf::shouldReceive('loadView')
            ->once()
            ->with(
                'laporan.teknisi.pdf',

                Mockery::on(
                    function ($data) use ($teknisiAndi) {

                        /*
                        |--------------------------------------------------------------------------
                        | VARIABEL WAJIB ADA
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !isset(
                                $data['barangKeluars'],
                                $data['totalTransaksi'],
                                $data['totalJumlahPemakaian']
                            )
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | HARUS HANYA 1 TRANSAKSI
                        |--------------------------------------------------------------------------
                        */

                        $barangKeluars =
                            $data['barangKeluars'];

                        if (
                            $barangKeluars->count() !== 1
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | AMBIL TRANSAKSI HASIL FILTER
                        |--------------------------------------------------------------------------
                        */

                        $barangKeluar =
                            $barangKeluars->first();


                        /*
                        |--------------------------------------------------------------------------
                        | VALIDASI HASIL FILTER
                        |--------------------------------------------------------------------------
                        */

                        return
                            $barangKeluar->teknisi_id
                                === $teknisiAndi->id

                            && $barangKeluar->tanggal
                                ->format('Y-m-d')
                                === '2026-10-05'

                            && $barangKeluar->pengguna
                                === 'Pelanggan Target PDF Teknisi'

                            && $barangKeluar->keperluan
                                === 'Target PDF Teknisi'

                            && (int) $barangKeluar->jumlah
                                === 4

                            && $data['totalTransaksi']
                                === 1

                            && (int) $data['totalJumlahPemakaian']
                                === 4;
                    }
                )
            )
            ->andReturn(
                $mockPdf
            );


        /*
        |--------------------------------------------------------------------------
        | REQUEST PDF DENGAN FILTER
        |--------------------------------------------------------------------------
        */

        $response = $this->actingAs($admin)
            ->get(
                '/laporan/teknisi/pdf'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&teknisi_id='
                . $teknisiAndi->id
            );


        /*
        |--------------------------------------------------------------------------
        | ASSERT
        |--------------------------------------------------------------------------
        */

        $response->assertOk();

        $response->assertDownload(
            'laporan-pemakaian-teknisi.pdf'
        );
    }
}