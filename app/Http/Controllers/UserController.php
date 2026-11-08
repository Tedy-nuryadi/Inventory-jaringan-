<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Teknisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR USER
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $users = User::latest()->get();

        return view(
            'users.index',
            compact('users')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH USER
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $teknisis = Teknisi::whereNull('user_id')
            ->orderBy('nama', 'asc')
            ->get();

        return view(
            'users.create',
            compact('teknisis')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN USER BARU
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'teknisi',
                ]),
            ],

            'teknisi_id' => [
                Rule::requiredIf(
                    fn () =>
                        $request->input('role') === 'teknisi'
                ),

                'nullable',
                'integer',

                Rule::exists(
                    'teknisis',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->whereNull('user_id')
                ),
            ],
        ]);


        DB::transaction(function () use (
            $request,
            $validated
        ) {
            $profilTeknisi = null;


            /*
            |--------------------------------------------------------------------------
            | ROLE TEKNISI
            |--------------------------------------------------------------------------
            */

            if ($validated['role'] === 'teknisi') {

                $profilTeknisi = Teknisi::where(
                    'id',
                    $validated['teknisi_id']
                )
                    ->whereNull('user_id')
                    ->lockForUpdate()
                    ->first();


                if (!$profilTeknisi) {

                    throw ValidationException::withMessages([
                        'teknisi_id' =>
                            'Profil teknisi sudah memiliki akun atau tidak tersedia.',
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | BUAT USER
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'name' =>
                    $validated['name'],

                'email' =>
                    $validated['email'],

                'password' =>
                    Hash::make(
                        $validated['password']
                    ),

                'role' =>
                    $validated['role'],

                'is_active' =>
                    true,
            ]);


            /*
            |--------------------------------------------------------------------------
            | HUBUNGKAN PROFIL TEKNISI
            |--------------------------------------------------------------------------
            */

            if (
                $validated['role'] === 'teknisi'
                && $profilTeknisi
            ) {

                $profilTeknisi->user_id =
                    $user->id;

                $profilTeknisi->save();
            }


            /*
            |--------------------------------------------------------------------------
            | AUDIT CREATE USER
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([
                'user_id' =>
                    auth()->id(),

                'action' =>
                    'create',

                'module' =>
                    'user',

                'subject_id' =>
                    $user->id,

                'description' =>
                    'Menambahkan user: '
                    . $user->name
                    . ' (' . $user->email . ')',

                'old_values' =>
                    null,

                'new_values' => [
                    'name' =>
                        $user->name,

                    'email' =>
                        $user->email,

                    'role' =>
                        $user->role,

                    'teknisi_id' =>
                        $profilTeknisi?->id,

                    'is_active' =>
                        (bool) $user->is_active,
                ],

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        });


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User baru berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM EDIT USER
    |--------------------------------------------------------------------------
    */

    public function edit(User $user)
    {
        $teknisis = Teknisi::where(
            function ($query) use ($user) {

                $query
                    ->whereNull('user_id')
                    ->orWhere(
                        'user_id',
                        $user->id
                    );
            }
        )
            ->orderBy('nama', 'asc')
            ->get();


        $teknisiTerhubung = Teknisi::where(
            'user_id',
            $user->id
        )->first();


        return view(
            'users.edit',
            compact(
                'user',
                'teknisis',
                'teknisiTerhubung'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        User $user
    )
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore(
                    $user->id
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | PASSWORD OPSIONAL
            |--------------------------------------------------------------------------
            */

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',

                Rule::in([
                    'admin',
                    'teknisi',
                ]),
            ],

            'teknisi_id' => [

                Rule::requiredIf(
                    fn () =>
                        $request->input('role') === 'teknisi'
                ),

                'nullable',
                'integer',

                Rule::exists(
                    'teknisis',
                    'id'
                )->where(
                    function ($query) use ($user) {

                        $query
                            ->whereNull('user_id')
                            ->orWhere(
                                'user_id',
                                $user->id
                            );
                    }
                ),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | ADMIN LOGIN TIDAK BOLEH MENURUNKAN ROLE DIRI SENDIRI
        |--------------------------------------------------------------------------
        */

        if (
            $user->id === auth()->id()
            && $validated['role'] !== 'admin'
        ) {

            return back()
                ->with(
                    'error',
                    'Admin yang sedang login tidak dapat mengubah role dirinya sendiri menjadi Teknisi.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE + AUDIT DALAM SATU TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $user,
            $validated
        ) {

            /*
            |--------------------------------------------------------------------------
            | PROFIL TEKNISI LAMA
            |--------------------------------------------------------------------------
            */

            $profilLama = Teknisi::where(
                'user_id',
                $user->id
            )
                ->lockForUpdate()
                ->first();


            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT SEBELUM UPDATE
            |--------------------------------------------------------------------------
            |
            | Password tidak dimasukkan ke Activity Log.
            |
            */

            $oldValues = [
                'name' =>
                    $user->name,

                'email' =>
                    $user->email,

                'role' =>
                    $user->role,

                'teknisi_id' =>
                    $profilLama?->id,

                'is_active' =>
                    (bool) $user->is_active,
            ];


            /*
            |--------------------------------------------------------------------------
            | UPDATE NAMA
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'name',
                    $validated
                )
            ) {

                $user->name =
                    $validated['name'];
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE EMAIL
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'email',
                    $validated
                )
            ) {

                $user->email =
                    $validated['email'];
            }


            /*
            |--------------------------------------------------------------------------
            | RESET PASSWORD OPSIONAL
            |--------------------------------------------------------------------------
            |
            | Password hanya diproses apabila benar-benar diisi.
            | Password tidak dicatat ke Activity Log.
            |
            */

            if (
                !empty(
                    $validated['password']
                    ?? null
                )
            ) {

                $user->password =
                    Hash::make(
                        $validated['password']
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | PROFIL BARU
            |--------------------------------------------------------------------------
            */

            $profilBaru = null;


            /*
            |--------------------------------------------------------------------------
            | ROLE BARU = TEKNISI
            |--------------------------------------------------------------------------
            */

            if (
                $validated['role']
                === 'teknisi'
            ) {

                $profilBaru = Teknisi::where(
                    'id',
                    $validated['teknisi_id']
                )
                    ->where(
                        function ($query) use ($user) {

                            $query
                                ->whereNull('user_id')
                                ->orWhere(
                                    'user_id',
                                    $user->id
                                );
                        }
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | PROFIL TIDAK TERSEDIA
                |--------------------------------------------------------------------------
                */

                if (!$profilBaru) {

                    throw ValidationException::withMessages([
                        'teknisi_id' =>
                            'Profil teknisi sudah digunakan oleh akun lain atau tidak tersedia.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | LEPAS PROFIL LAMA JIKA BERPINDAH
                |--------------------------------------------------------------------------
                */

                if (
                    $profilLama
                    && $profilLama->id
                        !== $profilBaru->id
                ) {

                    $profilLama->user_id =
                        null;

                    $profilLama->save();
                }


                /*
                |--------------------------------------------------------------------------
                | HUBUNGKAN PROFIL BARU
                |--------------------------------------------------------------------------
                */

                $profilBaru->user_id =
                    $user->id;

                $profilBaru->save();


                /*
                |--------------------------------------------------------------------------
                | UPDATE ROLE
                |--------------------------------------------------------------------------
                */

                $user->role =
                    'teknisi';

                $user->save();
            }


            /*
            |--------------------------------------------------------------------------
            | ROLE BARU = ADMIN
            |--------------------------------------------------------------------------
            */

            else {

                /*
                | Lepaskan profil Teknisi apabila ada.
                */

                if ($profilLama) {

                    $profilLama->user_id =
                        null;

                    $profilLama->save();
                }


                $user->role =
                    'admin';

                $user->save();

                $profilBaru =
                    null;
            }


            /*
            |--------------------------------------------------------------------------
            | REFRESH USER
            |--------------------------------------------------------------------------
            */

            $user->refresh();


            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT SESUDAH UPDATE
            |--------------------------------------------------------------------------
            */

            $newValues = [
                'name' =>
                    $user->name,

                'email' =>
                    $user->email,

                'role' =>
                    $user->role,

                'teknisi_id' =>
                    $profilBaru?->id,

                'is_active' =>
                    (bool) $user->is_active,
            ];


            /*
            |--------------------------------------------------------------------------
            | AUDIT UPDATE USER
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([
                'user_id' =>
                    auth()->id(),

                'action' =>
                    'update',

                'module' =>
                    'user',

                'subject_id' =>
                    $user->id,

                'description' =>
                    'Memperbarui user: '
                    . $user->name
                    . ' (' . $user->email . ')'
                    . ' dari role '
                    . $oldValues['role']
                    . ' menjadi '
                    . $newValues['role'],

                'old_values' =>
                    $oldValues,

                'new_values' =>
                    $newValues,

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        });


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Data user berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | AKTIF / NONAKTIF USER
    |--------------------------------------------------------------------------
    */

    public function toggleActive(
        Request $request,
        User $user
    )
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN TIDAK BOLEH MENONAKTIFKAN DIRI SENDIRI
        |--------------------------------------------------------------------------
        */

        if ($user->id === auth()->id()) {

            return back()->with(
                'error',
                'Akun yang sedang digunakan tidak dapat dinonaktifkan.'
            );
        }


        DB::transaction(function () use (
            $request,
            $user
        ) {

            /*
            |--------------------------------------------------------------------------
            | STATUS LAMA DAN BARU
            |--------------------------------------------------------------------------
            */

            $statusLama =
                (bool) $user->is_active;

            $statusBaru =
                !$statusLama;


            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */

            $user->is_active =
                $statusBaru;

            $user->save();


            /*
            |--------------------------------------------------------------------------
            | AUDIT ACTIVITY
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([
                'user_id' =>
                    auth()->id(),

                'action' =>
                    'update',

                'module' =>
                    'user',

                'subject_id' =>
                    $user->id,

                'description' =>
                    $statusBaru
                        ? 'Mengaktifkan akun user: '
                            . $user->name
                            . ' (' . $user->email . ')'
                        : 'Menonaktifkan akun user: '
                            . $user->name
                            . ' (' . $user->email . ')',

                'old_values' => [
                    'is_active' =>
                        $statusLama,
                ],

                'new_values' => [
                    'is_active' =>
                        $statusBaru,
                ],

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | REFRESH STATUS
        |--------------------------------------------------------------------------
        */

        $user->refresh();


        return back()->with(
            'success',
            $user->is_active
                ? 'User berhasil diaktifkan.'
                : 'User berhasil dinonaktifkan.'
        );
    }
}