<x-app-layout>

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit User
        </h2>

    </x-slot>


    <div class="py-8">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">


            {{-- HEADER --}}
            <div class="page-header">

                <div>

                    <h1>
                        Edit User
                    </h1>

                    <p>
                        Ubah identitas akun, password, role, dan hubungan dengan profil Teknisi.
                    </p>

                </div>


                <a
                    href="{{ route('users.index') }}"
                    class="btn-back"
                >
                    Kembali
                </a>

            </div>


            {{-- ERROR --}}
            @if(session('error'))

                <div class="alert-error">
                    {{ session('error') }}
                </div>

            @endif


            {{-- VALIDATION --}}
            @if($errors->any())

                <div class="alert-error">

                    <strong>
                        Data belum dapat disimpan.
                    </strong>

                    <ul>

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <div class="form-card">


                {{-- INFORMASI USER --}}
                <div class="user-info">

                    <div class="info-item">

                        <span>
                            Role Saat Ini
                        </span>

                        <strong>
                            {{ ucfirst($user->role) }}
                        </strong>

                    </div>


                    <div class="info-item">

                        <span>
                            Profil Teknisi
                        </span>

                        <strong>
                            {{ $teknisiTerhubung?->nama ?? 'Belum terhubung' }}
                        </strong>

                    </div>

                </div>


                <form
                    method="POST"
                    action="{{ route('users.update', $user) }}"
                    id="edit-user-form"
                >

                    @csrf
                    @method('PUT')


                    {{-- NAMA --}}
                    <div class="form-group">

                        <label for="name">
                            Nama
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            maxlength="255"
                            autocomplete="name"
                        >

                        <div class="form-help">
                            Nama pengguna yang akan tampil pada sistem.
                        </div>

                        @error('name')

                            <div class="field-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- EMAIL --}}
                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                        >

                        <div class="form-help">
                            Email harus unik dan tidak boleh digunakan oleh akun lain.
                        </div>

                        @error('email')

                            <div class="field-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- PASSWORD BARU --}}
                    <div class="form-group">

                        <label for="password">
                            Password Baru
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="8"
                        >

                        <div class="form-help">
                            Kosongkan apabila password tidak ingin diubah.
                            Jika diisi, password minimal 8 karakter.
                        </div>

                        @error('password')

                            <div class="field-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- KONFIRMASI PASSWORD --}}
                    <div class="form-group">

                        <label for="password_confirmation">
                            Konfirmasi Password Baru
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            minlength="8"
                        >

                        <div class="form-help">
                            Isi sama persis dengan Password Baru.
                        </div>

                    </div>


                    {{-- INFO KEAMANAN PASSWORD --}}
                    <div class="password-info">

                        <strong>Keamanan password:</strong>

                        Password lama tidak pernah ditampilkan pada halaman ini.

                        Apabila Password Baru dan Konfirmasi Password dibiarkan kosong,
                        password akun akan tetap sama.

                    </div>


                    {{-- ROLE --}}
                    <div class="form-group">

                        <label for="role">
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            required
                        >

                            <option
                                value="admin"
                                @selected(
                                    old('role', $user->role)
                                    === 'admin'
                                )
                            >
                                Admin
                            </option>


                            <option
                                value="teknisi"
                                @selected(
                                    old('role', $user->role)
                                    === 'teknisi'
                                )
                            >
                                Teknisi
                            </option>

                        </select>


                        @error('role')

                            <div class="field-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- PROFIL TEKNISI --}}
                    <div
                        class="form-group"
                        id="teknisi-wrapper"
                    >

                        <label for="teknisi_id">
                            Profil Teknisi
                        </label>


                        <select
                            id="teknisi_id"
                            name="teknisi_id"
                        >

                            <option value="">
                                -- Pilih Profil Teknisi --
                            </option>


                            @foreach($teknisis as $teknisi)

                                <option
                                    value="{{ $teknisi->id }}"
                                    @selected(
                                        old(
                                            'teknisi_id',
                                            $teknisiTerhubung?->id
                                        ) == $teknisi->id
                                    )
                                >
                                    {{ $teknisi->nama }}
                                </option>

                            @endforeach

                        </select>


                        <div class="form-help">

                            Wajib dipilih apabila role adalah
                            <strong>Teknisi</strong>.

                            Profil yang sedang digunakan akun lain tidak akan ditampilkan.

                        </div>


                        @error('teknisi_id')

                            <div class="field-error">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- INFO PERUBAHAN KE ADMIN --}}
                    <div
                        class="info-box"
                        id="admin-info"
                    >

                        Jika role diubah menjadi
                        <strong>Admin</strong>,
                        hubungan akun dengan profil Teknisi akan dilepas.

                    </div>


                    {{-- ACTION --}}
                    <div class="form-actions">

                        <a
                            href="{{ route('users.index') }}"
                            class="btn-cancel"
                        >
                            Batal
                        </a>


                        <button
                            type="submit"
                            class="btn-save"
                        >
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #111827;
        }

        .page-header p {
            margin-top: 6px;
            margin-bottom: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;

            box-shadow:
                0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .user-info {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 22px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-item {
            padding: 14px;
            background: #f9fafb;
            border-radius: 9px;
        }

        .info-item span {
            display: block;
            margin-bottom: 5px;

            font-size: 11px;
            font-weight: 700;

            color: #6b7280;

            text-transform: uppercase;
        }

        .info-item strong {
            font-size: 14px;
            color: #111827;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;

            font-size: 13px;
            font-weight: 700;

            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            min-height: 44px;

            border: 1px solid #d1d5db;
            border-radius: 9px;

            padding: 10px 12px;

            background: #ffffff;
            color: #111827;

            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);
        }

        .form-help {
            margin-top: 7px;

            color: #6b7280;

            font-size: 12px;
            line-height: 1.5;
        }

        .field-error {
            margin-top: 6px;

            font-size: 12px;

            color: #dc2626;
        }

        .alert-error {
            margin-bottom: 20px;

            padding: 15px 18px;

            border: 1px solid #fecaca;
            border-radius: 10px;

            background: #fef2f2;
            color: #991b1b;

            font-size: 13px;
        }

        .alert-error ul {
            margin-top: 8px;

            padding-left: 20px;

            list-style: disc;
        }

        .info-box {
            margin-bottom: 18px;

            padding: 14px 16px;

            border: 1px solid #bfdbfe;
            border-radius: 9px;

            background: #eff6ff;
            color: #1e40af;

            font-size: 13px;
            line-height: 1.6;
        }

        .password-info {
            margin-bottom: 20px;

            padding: 14px 16px;

            border: 1px solid #fde68a;
            border-radius: 9px;

            background: #fffbeb;
            color: #92400e;

            font-size: 13px;
            line-height: 1.6;
        }

        .password-info strong {
            color: #78350f;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;

            gap: 10px;

            margin-top: 10px;
        }

        .btn-back,
        .btn-cancel,
        .btn-save {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-height: 42px;

            padding: 10px 17px;

            border-radius: 9px;

            text-decoration: none;

            font-size: 13px;
            font-weight: 700;
        }

        .btn-back,
        .btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-save {
            border: none;

            background: #2563eb;
            color: #ffffff;

            cursor: pointer;
        }

        .btn-save:hover {
            background: #1d4ed8;
        }

        @media (max-width: 640px) {

            .page-header {
                flex-direction: column;
            }

            .user-info {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn-back,
            .btn-cancel,
            .btn-save {
                width: 100%;
            }

        }

    </style>


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const role =
                    document.getElementById('role');

                const teknisiWrapper =
                    document.getElementById(
                        'teknisi-wrapper'
                    );

                const adminInfo =
                    document.getElementById(
                        'admin-info'
                    );


                function updateRoleForm()
                {
                    if (role.value === 'teknisi') {

                        teknisiWrapper.style.display =
                            'block';

                        adminInfo.style.display =
                            'none';

                    } else {

                        teknisiWrapper.style.display =
                            'none';

                        adminInfo.style.display =
                            'block';

                    }
                }


                role.addEventListener(
                    'change',
                    updateRoleForm
                );


                updateRoleForm();

            }
        );

    </script>

</x-app-layout>