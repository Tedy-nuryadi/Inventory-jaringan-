<?php

namespace Tests\Feature;

use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementUpdateTest extends TestCase
{
    use RefreshDatabase;


    /*
    |--------------------------------------------------------------------------
    | ADMIN -> TEKNISI TANPA PROFIL HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_mengubah_user_menjadi_teknisi_tanpa_profil_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => '',
                ]
            );

        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );

        $response->assertSessionHasErrors(
            'teknisi_id'
        );

        $userTarget->refresh();

        $this->assertEquals(
            'admin',
            $userTarget->role
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $userTarget->id,
                'role' => 'admin',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN -> TEKNISI BERHASIL + PROFIL TERHUBUNG
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_mengubah_user_menjadi_teknisi_dan_menghubungkan_profil(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $profilTeknisi = Teknisi::create([
            'user_id' => null,
            'nama' => 'Teknisi Untuk Update',
            'telepon' => '081111111111',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => $profilTeknisi->id,
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $response->assertSessionHas(
            'success'
        );

        $userTarget->refresh();

        $profilTeknisi->refresh();

        $this->assertEquals(
            'teknisi',
            $userTarget->role
        );

        $this->assertEquals(
            $userTarget->id,
            $profilTeknisi->user_id
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $profilTeknisi->id,
                'user_id' => $userTarget->id,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI -> ADMIN HARUS MELEPAS PROFIL TEKNISI
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_mengubah_teknisi_menjadi_admin_dan_melepas_profil_teknisi(): void
    {
        $adminLogin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $profilTeknisi = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Teknisi Akan Jadi Admin',
            'telepon' => '082222222222',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($adminLogin)
            ->put(
                route(
                    'users.update',
                    $userTeknisi
                ),
                [
                    'role' => 'admin',
                    'teknisi_id' => '',
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $userTeknisi->refresh();

        $profilTeknisi->refresh();

        $this->assertEquals(
            'admin',
            $userTeknisi->role
        );

        $this->assertNull(
            $profilTeknisi->user_id
        );

        $this->assertDatabaseHas(
            'teknisis',
            [
                'id' => $profilTeknisi->id,
                'user_id' => null,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI BISA DIPINDAHKAN KE PROFIL TEKNISI LAIN
    |--------------------------------------------------------------------------
    */

    public function test_admin_bisa_memindahkan_user_teknisi_ke_profil_teknisi_lain(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $profilLama = Teknisi::create([
            'user_id' => $userTeknisi->id,
            'nama' => 'Profil Teknisi Lama',
            'telepon' => '083333333333',
            'alamat' => 'Tangerang',
        ]);

        $profilBaru = Teknisi::create([
            'user_id' => null,
            'nama' => 'Profil Teknisi Baru',
            'telepon' => '084444444444',
            'alamat' => 'Serang',
        ]);

        $response = $this->actingAs($admin)
            ->put(
                route(
                    'users.update',
                    $userTeknisi
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => $profilBaru->id,
                ]
            );

        $response->assertRedirect(
            route('users.index')
        );

        $profilLama->refresh();

        $profilBaru->refresh();

        $userTeknisi->refresh();

        $this->assertEquals(
            'teknisi',
            $userTeknisi->role
        );

        $this->assertNull(
            $profilLama->user_id
        );

        $this->assertEquals(
            $userTeknisi->id,
            $profilBaru->user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROFIL MILIK USER LAIN TIDAK BOLEH DIAMBIL
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_menghubungkan_user_ke_profil_teknisi_milik_user_lain(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTeknisiLain = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $profilMilikOrangLain = Teknisi::create([
            'user_id' => $userTeknisiLain->id,
            'nama' => 'Profil Milik Teknisi Lain',
            'telepon' => '085555555555',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => $profilMilikOrangLain->id,
                ]
            );

        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );

        $response->assertSessionHasErrors(
            'teknisi_id'
        );

        $userTarget->refresh();

        $profilMilikOrangLain->refresh();

        $this->assertEquals(
            'admin',
            $userTarget->role
        );

        $this->assertEquals(
            $userTeknisiLain->id,
            $profilMilikOrangLain->user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN LOGIN TIDAK BOLEH MENURUNKAN ROLE DIRINYA SENDIRI
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_mengubah_role_dirinya_sendiri_menjadi_teknisi(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $profilTeknisi = Teknisi::create([
            'user_id' => null,
            'nama' => 'Profil Untuk Self Demotion',
            'telepon' => '086666666666',
            'alamat' => 'Tangerang',
        ]);

        $response = $this->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $admin
                )
            )
            ->put(
                route(
                    'users.update',
                    $admin
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => $profilTeknisi->id,
                ]
            );

        $response->assertRedirect(
            route(
                'users.edit',
                $admin
            )
        );

        $response->assertSessionHas(
            'error'
        );

        $admin->refresh();

        $profilTeknisi->refresh();

        $this->assertEquals(
            'admin',
            $admin->role
        );

        $this->assertNull(
            $profilTeknisi->user_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT MENAMPILKAN PROFIL YANG BOLEH DIPILIH
    |--------------------------------------------------------------------------
    */

    public function test_form_edit_hanya_menampilkan_profil_sendiri_dan_profil_yang_belum_memiliki_akun(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $profilSendiri = Teknisi::create([
            'user_id' => $userTarget->id,
            'nama' => 'Profil Teknisi Sendiri',
            'telepon' => '087111111111',
            'alamat' => 'Tangerang',
        ]);

        $profilKosong = Teknisi::create([
            'user_id' => null,
            'nama' => 'Profil Teknisi Kosong',
            'telepon' => '087222222222',
            'alamat' => 'Serang',
        ]);

        $userLain = User::factory()->create([
            'role' => 'teknisi',
        ]);

        Teknisi::create([
            'user_id' => $userLain->id,
            'nama' => 'Profil Teknisi Milik Orang Lain',
            'telepon' => '087333333333',
            'alamat' => 'Bekasi',
        ]);

        $response = $this->actingAs($admin)
            ->get(
                route(
                    'users.edit',
                    $userTarget
                )
            );

        $response->assertOk();

        $response->assertViewIs(
            'users.edit'
        );

        $response->assertViewHas(
            'teknisiTerhubung',
            function ($teknisi) use ($profilSendiri) {

                return $teknisi !== null
                    && $teknisi->id === $profilSendiri->id;
            }
        );

        $response->assertViewHas(
            'teknisis',
            function ($teknisis) use (
                $profilSendiri,
                $profilKosong
            ) {

                $ids = $teknisis
                    ->pluck('id')
                    ->all();

                return count($ids) === 2
                    && in_array(
                        $profilSendiri->id,
                        $ids
                    )
                    && in_array(
                        $profilKosong->id,
                        $ids
                    );
            }
        );

        $response->assertSee(
            'Profil Teknisi Sendiri'
        );

        $response->assertSee(
            'Profil Teknisi Kosong'
        );

        $response->assertDontSee(
            'Profil Teknisi Milik Orang Lain'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH MEMBUKA EDIT USER
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_membuka_form_edit_user(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->get(
                route(
                    'users.edit',
                    $userTarget
                )
            );

        $response->assertForbidden();
    }


    /*
    |--------------------------------------------------------------------------
    | TEKNISI TIDAK BOLEH UPDATE USER
    |--------------------------------------------------------------------------
    */

    public function test_teknisi_tidak_bisa_mengupdate_user(): void
    {
        $userTeknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($userTeknisi)
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'teknisi',
                    'teknisi_id' => '',
                ]
            );

        $response->assertForbidden();

        $userTarget->refresh();

        $this->assertEquals(
            'admin',
            $userTarget->role
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE ILEGAL HARUS DITOLAK
    |--------------------------------------------------------------------------
    */

    public function test_admin_tidak_bisa_update_user_dengan_role_ilegal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $userTarget = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from(
                route(
                    'users.edit',
                    $userTarget
                )
            )
            ->put(
                route(
                    'users.update',
                    $userTarget
                ),
                [
                    'role' => 'superadmin',
                ]
            );

        $response->assertRedirect(
            route(
                'users.edit',
                $userTarget
            )
        );

        $response->assertSessionHasErrors(
            'role'
        );

        $userTarget->refresh();

        $this->assertEquals(
            'admin',
            $userTarget->role
        );
    }
}