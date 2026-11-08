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

class RiwayatSayaPdfTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | TEKNISI BISA DOWNLOAD PDF RIWAYAT SAYA
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_bisa_download_pdf_riwayat_saya(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi PDF Riwayat Saya',
            'telepon' => '081234567890',
            'alamat' => 'Tangerang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Modem',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'MDM-RIWAYAT-PDF-001',
            'nama_barang' => 'Modem Riwayat PDF',
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
            'pengguna' => 'Pelanggan PDF Riwayat Saya',
            'keperluan' => 'Pemasangan PDF Riwayat',
            'tujuan' => 'Site PDF Riwayat',
            'foto' => null,
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get('/riwayat-saya/pdf');

        $response->assertOk();

        $response->assertDownload(
            'riwayat-pemakaian-saya.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF HANYA MILIK TEKNISI LOGIN + MENGIKUTI FILTER PERIODE
    |--------------------------------------------------------------------------
    */

    public function test_pdf_riwayat_saya_hanya_berisi_data_milik_teknisi_login_dan_mengikuti_filter_periode(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TEKNISI ANDI - USER LOGIN
        |--------------------------------------------------------------------------
        */

        $userAndi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiAndi = Teknisi::create([
            'user_id' => $userAndi->id,
            'nama' => 'Teknisi Andi PDF Saya',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);


        /*
        |--------------------------------------------------------------------------
        | TEKNISI BUDI - USER LAIN
        |--------------------------------------------------------------------------
        */

        $userBudi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $teknisiBudi = Teknisi::create([
            'user_id' => $userBudi->id,
            'nama' => 'Teknisi Budi PDF Saya',
            'telepon' => '082222222222',
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
            'kode_barang' => 'RTR-RIWAYAT-PDF-002',
            'nama_barang' => 'Router PDF Riwayat Saya',
            'kategori_id' => $kategori->id,
            'merk' => 'MikroTik',
            'stok' => 50,
            'satuan' => 'unit',
            'lokasi' => 'Gudang A',
            'stok_minimum' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI - DI LUAR PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-09-20',
            'jumlah' => 4,
            'pengguna' => 'Pelanggan Andi September PDF',
            'keperluan' => 'Pekerjaan Andi September PDF',
            'tujuan' => 'Site Andi September PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | ANDI - TARGET DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiAndi->id,
            'tanggal' => '2026-10-05',
            'jumlah' => 6,
            'pengguna' => 'Pelanggan Target Andi PDF Saya',
            'keperluan' => 'Pekerjaan Target Andi PDF',
            'tujuan' => 'Site Target Andi PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | BUDI - DALAM PERIODE
        |--------------------------------------------------------------------------
        */

        BarangKeluar::create([
            'barang_id' => $barang->id,
            'teknisi_id' => $teknisiBudi->id,
            'tanggal' => '2026-10-06',
            'jumlah' => 9,
            'pengguna' => 'Pelanggan Budi PDF Saya',
            'keperluan' => 'Pekerjaan Budi PDF',
            'tujuan' => 'Site Budi PDF',
            'foto' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | MOCK PDF DENGAN TYPE YANG BENAR
        |--------------------------------------------------------------------------
        */

        $mockPdf = Mockery::mock(
            DomPdf::class
        );


        $mockPdf
            ->shouldReceive('setPaper')
            ->once()
            ->with(
                'a4',
                'landscape'
            )
            ->andReturnSelf();


        $mockPdf
            ->shouldReceive('download')
            ->once()
            ->with(
                'riwayat-pemakaian-saya.pdf'
            )
            ->andReturn(
                response(
                    'FAKE PDF',
                    200,
                    [
                        'Content-Type' =>
                            'application/pdf',

                        'Content-Disposition' =>
                            'attachment; filename="riwayat-pemakaian-saya.pdf"',
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
                'laporan.teknisi.saya-pdf',

                Mockery::on(
                    function ($data) use ($teknisiAndi) {

                        /*
                        |--------------------------------------------------------------------------
                        | VARIABEL WAJIB ADA
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !isset(
                                $data['teknisi'],
                                $data['barangKeluars'],
                                $data['totalTransaksi'],
                                $data['totalJumlahPemakaian']
                            )
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | TEKNISI HARUS ANDI
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $data['teknisi']->id
                            !== $teknisiAndi->id
                        ) {
                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | HANYA SATU TRANSAKSI
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
                        | DATA TARGET
                        |--------------------------------------------------------------------------
                        */

                        $barangKeluar =
                            $barangKeluars->first();


                        return
                            $barangKeluar->teknisi_id
                                === $teknisiAndi->id

                            && $barangKeluar->tanggal
                                ->format('Y-m-d')
                                === '2026-10-05'

                            && $barangKeluar->pengguna
                                === 'Pelanggan Target Andi PDF Saya'

                            && $barangKeluar->keperluan
                                === 'Pekerjaan Target Andi PDF'

                            && (int) $barangKeluar->jumlah
                                === 6

                            && $data['totalTransaksi']
                                === 1

                            && (int) $data['totalJumlahPemakaian']
                                === 6;
                    }
                )
            )
            ->andReturn(
                $mockPdf
            );


        /*
        |--------------------------------------------------------------------------
        | REQUEST SEBAGAI ANDI
        |--------------------------------------------------------------------------
        |
        | teknisi_id Budi sengaja disisipkan.
        | Controller harus tetap memakai teknisi Andi dari akun login.
        |
        */

        $response = $this->actingAs($userAndi)
            ->get(
                '/riwayat-saya/pdf'
                . '?tanggal_mulai=2026-10-01'
                . '&tanggal_selesai=2026-10-10'
                . '&teknisi_id='
                . $teknisiBudi->id
            );


        /*
        |--------------------------------------------------------------------------
        | ASSERT
        |--------------------------------------------------------------------------
        */

        $response->assertOk();

        $response->assertDownload(
            'riwayat-pemakaian-saya.pdf'
        );
    }
}