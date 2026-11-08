<nav
    x-data="{ mobileOpen: false, adminOpen: false, userOpen: false }"
    class="ij-navbar"
>

    <style>

        [x-cloak] {
            display: none !important;
        }


        .ij-navbar {
            position: relative;
            z-index: 50;
            background: rgba(255,255,255,.98);
            border-top: 4px solid #0891b2;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 3px 14px rgba(15,23,42,.06);
            font-family: inherit;
        }


        .ij-navbar * {
            box-sizing: border-box;
        }


        .ij-navbar-wrap {
            max-width: 1536px;
            margin: 0 auto;
            padding: 0 28px;
        }


        .ij-navbar-row {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }


        .ij-navbar-left {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 28px;
        }



        /* =========================================================
           BRAND
        ========================================================= */

        .ij-brand {
            flex-shrink: 0;
        }


        .ij-brand-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }


        .ij-brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .ij-brand-logo svg {
            width: auto;
            height: 42px;
            color: #0f172a;
        }


        .ij-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.05;
        }


        .ij-brand-title {
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -.2px;
        }


        .ij-brand-subtitle {
            margin-top: 4px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }



        /* =========================================================
           DESKTOP MENU
        ========================================================= */

        .ij-desktop-menu {
            display: flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
        }


        .ij-menu-link,
        .ij-dropdown-button {

            position: relative;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            min-height: 40px;

            padding: 0 11px;

            border: 1px solid transparent;
            border-radius: 9px;

            color: #475569;
            background: transparent;

            font-size: 13px;
            font-weight: 600;
            line-height: 1;

            white-space: nowrap;
            text-decoration: none;

            transition:
                background .18s ease,
                color .18s ease,
                border-color .18s ease,
                box-shadow .18s ease,
                transform .18s ease;
        }


        .ij-dropdown-button {
            cursor: pointer;
        }


        .ij-menu-link:hover,
        .ij-dropdown-button:hover,
        .ij-dropdown-button.is-active {

            color: #0f172a;
            background: #f8fafc;
            border-color: #e2e8f0;
        }


        .ij-menu-link.is-active {

            color: #0e7490;

            background: #ecfeff;

            border-color: #a5f3fc;

            box-shadow:
                0 2px 6px rgba(8,145,178,.08);
        }


        .ij-menu-link.is-active::after {

            content: "";

            position: absolute;

            left: 12px;
            right: 12px;
            bottom: -17px;

            height: 3px;

            border-radius: 999px 999px 0 0;

            background: #0891b2;
        }


        .ij-menu-icon {

            width: 17px;
            height: 17px;

            flex-shrink: 0;
        }



        /* =========================================================
           LAPORAN STOK
        ========================================================= */

        .ij-report-link {

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            min-height: 40px;

            padding: 0 13px;

            border: 1px solid #0e7490;

            border-radius: 9px;

            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #0891b2 0%,
                    #0e7490 100%
                );

            font-size: 13px;
            font-weight: 700;

            white-space: nowrap;

            text-decoration: none;

            box-shadow:
                0 5px 12px rgba(8,145,178,.18);

            transition:
                transform .18s ease,
                box-shadow .18s ease,
                filter .18s ease;
        }


        .ij-report-link:hover {

            transform: translateY(-1px);

            box-shadow:
                0 7px 16px rgba(8,145,178,.24);

            filter: brightness(1.03);
        }



        /* =========================================================
           DROPDOWN
        ========================================================= */

        .ij-dropdown-wrap {
            position: relative;
        }


        .ij-chevron {

            width: 14px;
            height: 14px;

            transition:
                transform .2s ease;
        }


        .ij-chevron.is-open {
            transform: rotate(180deg);
        }


        .ij-dropdown-panel {

            position: absolute;

            top: calc(100% + 10px);
            right: 0;

            width: 245px;

            padding: 8px;

            background: #ffffff;

            border: 1px solid #e2e8f0;

            border-radius: 14px;

            box-shadow:
                0 18px 45px rgba(15,23,42,.12),
                0 4px 12px rgba(15,23,42,.05);
        }


        .ij-dropdown-title {

            padding: 8px 10px 7px;

            color: #94a3b8;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .ij-dropdown-item {

            display: flex;
            align-items: center;

            gap: 11px;

            width: 100%;

            padding: 10px 11px;

            border: 0;

            border-radius: 9px;

            color: #334155;

            background: transparent;

            text-align: left;

            text-decoration: none;

            cursor: pointer;

            transition:
                background .18s ease,
                color .18s ease;
        }


        .ij-dropdown-item:hover {

            color: #0f172a;

            background: #f8fafc;
        }


        .ij-dropdown-item.is-active {

            color: #0e7490;

            background: #ecfeff;
        }


        .ij-dropdown-item-icon {

            display: flex;
            align-items: center;
            justify-content: center;

            width: 34px;
            height: 34px;

            flex-shrink: 0;

            border-radius: 8px;

            color: #0e7490;

            background: #ecfeff;
        }


        .ij-dropdown-item-icon svg {

            width: 17px;
            height: 17px;
        }


        .ij-dropdown-item-copy {
            min-width: 0;
        }


        .ij-dropdown-item-name {

            display: block;

            color: inherit;

            font-size: 13px;
            font-weight: 700;
        }


        .ij-dropdown-item-desc {

            display: block;

            margin-top: 2px;

            color: #94a3b8;

            font-size: 11px;
            font-weight: 500;
        }


        .ij-dropdown-divider {

            height: 1px;

            margin: 7px 4px;

            background: #f1f5f9;
        }



        /* =========================================================
           USER
        ========================================================= */

        .ij-navbar-right {

            flex-shrink: 0;

            display: flex;
            align-items: center;
        }


        .ij-user-button {

            display: flex;
            align-items: center;

            gap: 9px;

            padding:
                5px 8px 5px 5px;

            border:
                1px solid #e2e8f0;

            border-radius: 999px;

            color: #334155;

            background: #ffffff;

            cursor: pointer;

            box-shadow:
                0 2px 8px rgba(15,23,42,.04);

            transition:
                border-color .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }


        .ij-user-button:hover {

            background: #f8fafc;

            border-color: #cbd5e1;

            box-shadow:
                0 4px 10px rgba(15,23,42,.07);
        }


        .ij-user-avatar {

            width: 32px;
            height: 32px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 999px;

            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #0891b2,
                    #0f766e
                );

            font-size: 13px;
            font-weight: 800;
        }


        .ij-user-copy {

            min-width: 0;

            display: flex;

            flex-direction: column;

            align-items: flex-start;
        }


        .ij-user-name {

            max-width: 125px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #0f172a;

            font-size: 12px;
            font-weight: 700;
        }


        .ij-user-role {

            margin-top: 2px;

            color: #94a3b8;

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .07em;

            text-transform: uppercase;
        }


        .ij-user-panel {
            width: 260px;
        }


        .ij-user-info {
            padding:
                10px 11px 12px;
        }


        .ij-user-info-name {

            color: #0f172a;

            font-size: 14px;
            font-weight: 800;
        }


        .ij-user-info-email {

            margin-top: 3px;

            overflow: hidden;

            color: #64748b;

            font-size: 11px;

            text-overflow: ellipsis;

            white-space: nowrap;
        }



        /* =========================================================
           MOBILE
        ========================================================= */

        .ij-mobile-toggle {

            display: none;

            align-items: center;
            justify-content: center;

            width: 42px;
            height: 42px;

            border:
                1px solid #e2e8f0;

            border-radius: 10px;

            color: #475569;

            background: #ffffff;

            cursor: pointer;
        }


        .ij-mobile-toggle svg {

            width: 22px;
            height: 22px;
        }


        .ij-mobile-panel {

            border-top:
                1px solid #f1f5f9;

            background: #ffffff;
        }


        .ij-mobile-inner {

            padding:
                14px 18px 18px;
        }


        .ij-mobile-section-label {

            margin:
                14px 7px 7px;

            color: #94a3b8;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .ij-mobile-link {

            display: flex;
            align-items: center;

            gap: 11px;

            width: 100%;

            min-height: 46px;

            margin-bottom: 4px;

            padding:
                0 12px;

            border:
                1px solid transparent;

            border-radius: 10px;

            color: #475569;

            background: transparent;

            font-size: 14px;
            font-weight: 600;

            text-decoration: none;

            transition:
                background .18s ease,
                color .18s ease,
                border-color .18s ease;
        }


        .ij-mobile-link:hover {

            color: #0f172a;

            background: #f8fafc;
        }


        .ij-mobile-link.is-active {

            color: #0e7490;

            background: #ecfeff;

            border-color: #cffafe;
        }


        .ij-mobile-link svg {

            width: 19px;
            height: 19px;

            flex-shrink: 0;
        }


        .ij-mobile-report {

            color: #ffffff !important;

            background:
                linear-gradient(
                    135deg,
                    #0891b2,
                    #0e7490
                ) !important;

            border-color:
                #0e7490 !important;
        }


        .ij-mobile-user-card {

            margin-top: 16px;

            padding: 14px;

            border:
                1px solid #e2e8f0;

            border-radius: 12px;

            background: #f8fafc;
        }


        .ij-mobile-user-top {

            display: flex;

            align-items: center;

            gap: 11px;

            margin-bottom: 12px;
        }


        .ij-mobile-user-details {
            min-width: 0;
        }


        .ij-mobile-user-name {

            color: #0f172a;

            font-size: 14px;
            font-weight: 800;
        }


        .ij-mobile-user-email {

            margin-top: 2px;

            overflow: hidden;

            color: #64748b;

            font-size: 11px;

            text-overflow: ellipsis;

            white-space: nowrap;
        }



        @media (max-width: 1180px) {

            .ij-desktop-menu,
            .ij-navbar-right {

                display: none;
            }


            .ij-mobile-toggle {

                display: inline-flex;
            }


            .ij-navbar-row {

                min-height: 66px;
            }

        }



        @media (max-width: 640px) {

            .ij-navbar-wrap {

                padding:
                    0 16px;
            }


            .ij-brand-text {

                display: none;
            }


            .ij-brand-logo svg {

                height: 38px;
            }

        }

    </style>



    <div class="ij-navbar-wrap">

        <div class="ij-navbar-row">


            {{-- LEFT --}}
            <div class="ij-navbar-left">


                {{-- BRAND --}}
                <div class="ij-brand">

                    <a
                        href="{{ route('dashboard') }}"
                        class="ij-brand-link"
                    >

                        <span class="ij-brand-logo">

                            <x-application-logo
                                class="block fill-current"
                            />

                        </span>


                        <span class="ij-brand-text">

                            <span class="ij-brand-title">
                                Inventory Jaringan
                            </span>

                            <span class="ij-brand-subtitle">
                                Monitoring System
                            </span>

                        </span>

                    </a>

                </div>



                {{-- DESKTOP MENU --}}
                <div class="ij-desktop-menu">


                    {{-- DASHBOARD --}}
                    <a
                        href="{{ route('dashboard') }}"
                        class="ij-menu-link
                        {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                    >

                        <svg
                            class="ij-menu-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M3 3h7v7H3z"/>
                            <path d="M14 3h7v7h-7z"/>
                            <path d="M3 14h7v7H3z"/>
                            <path d="M14 14h7v7h-7z"/>
                        </svg>

                        Dashboard

                    </a>



                    {{-- ADMIN MENU --}}
                    @if(auth()->user()->role === 'admin')


                        {{-- BARANG --}}
                        <a
                            href="{{ route('barang.index') }}"
                            class="ij-menu-link
                            {{ request()->routeIs('barang.*') ? 'is-active' : '' }}"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>

                            Barang

                        </a>



                        {{-- KATEGORI --}}
                        <a
                            href="{{ route('kategori.index') }}"
                            class="ij-menu-link
                            {{ request()->routeIs('kategori.*') ? 'is-active' : '' }}"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M3 3h7v7H3z"/>
                                <path d="M14 3h7v7h-7z"/>
                                <path d="M3 14h7v7H3z"/>
                                <path d="M14 14h7v7h-7z"/>
                            </svg>

                            Kategori

                        </a>



                        {{-- BARANG MASUK --}}
                        <a
                            href="{{ route('barang-masuk.index') }}"
                            class="ij-menu-link
                            {{ request()->routeIs('barang-masuk.*') ? 'is-active' : '' }}"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 3v12"/>
                                <path d="m7 10 5 5 5-5"/>
                                <path d="M5 21h14"/>
                            </svg>

                            Barang Masuk

                        </a>



                        {{-- TEKNISI --}}
                        <a
                            href="{{ route('teknisi.index') }}"
                            class="ij-menu-link
                            {{ request()->routeIs('teknisi.*') ? 'is-active' : '' }}"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21a8 8 0 0 1 16 0"/>
                            </svg>

                            Teknisi

                        </a>


                    @endif



                    {{-- BARANG KELUAR --}}
                    <a
                        href="{{ route('barang-keluar.index') }}"
                        class="ij-menu-link
                        {{ request()->routeIs('barang-keluar.*') ? 'is-active' : '' }}"
                    >

                        <svg
                            class="ij-menu-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M12 21V9"/>
                            <path d="m17 14-5-5-5 5"/>
                            <path d="M5 3h14"/>
                        </svg>

                        Barang Keluar

                    </a>



                    {{-- RIWAYAT SAYA KHUSUS TEKNISI --}}
                    @if(auth()->user()->role === 'teknisi')

                        <a
                            href="{{ route('riwayat.saya') }}"
                            class="ij-menu-link
                            {{ request()->routeIs('riwayat.saya*') ? 'is-active' : '' }}"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v6h6"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>

                            Riwayat Saya

                        </a>

                    @endif



                    {{-- ADMIN --}}
                    @if(auth()->user()->role === 'admin')


                        {{-- LAPORAN STOK --}}
                        <a
                            href="{{ route('laporan.stok.index') }}"
                            class="ij-report-link"
                        >

                            <svg
                                class="ij-menu-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 19V9"/>
                                <path d="M10 19V5"/>
                                <path d="M16 19v-7"/>
                                <path d="M22 19H2"/>
                            </svg>

                            Laporan Stok

                        </a>



                        {{-- ADMIN DROPDOWN --}}
                        <div
                            class="ij-dropdown-wrap"
                            @click.away="adminOpen = false"
                        >

                            <button
                                type="button"
                                @click="adminOpen = !adminOpen"
                                class="ij-dropdown-button
                                {{
                                    request()->routeIs('users.*')
                                    || request()->routeIs('activity-logs.*')
                                    ? 'is-active'
                                    : ''
                                }}"
                            >

                                <svg
                                    class="ij-menu-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle cx="12" cy="12" r="3"/>

                                    <path
                                        d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.14.37.35.7.6 1 .3.3.68.48 1.1.5H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51.5Z"
                                    />

                                </svg>

                                Admin


                                <svg
                                    class="ij-chevron"
                                    :class="{ 'is-open': adminOpen }"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >

                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06Z"
                                        clip-rule="evenodd"
                                    />

                                </svg>

                            </button>



                            <div
                                x-cloak
                                x-show="adminOpen"
                                x-transition
                                class="ij-dropdown-panel"
                            >

                                <div class="ij-dropdown-title">
                                    Administrasi Sistem
                                </div>



                                {{-- MANAGEMENT USER --}}
                                <a
                                    href="{{ route('users.index') }}"
                                    class="ij-dropdown-item
                                    {{ request()->routeIs('users.*') ? 'is-active' : '' }}"
                                >

                                    <span class="ij-dropdown-item-icon">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>

                                    </span>


                                    <span class="ij-dropdown-item-copy">

                                        <span class="ij-dropdown-item-name">
                                            Management User
                                        </span>

                                        <span class="ij-dropdown-item-desc">
                                            Kelola akun dan role pengguna
                                        </span>

                                    </span>

                                </a>



                                {{-- AUDIT ACTIVITY --}}
                                <a
                                    href="{{ route('activity-logs.index') }}"
                                    class="ij-dropdown-item
                                    {{ request()->routeIs('activity-logs.*') ? 'is-active' : '' }}"
                                >

                                    <span class="ij-dropdown-item-icon">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="M9 11l3 3L22 4"/>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                        </svg>

                                    </span>


                                    <span class="ij-dropdown-item-copy">

                                        <span class="ij-dropdown-item-name">
                                            Audit Activity
                                        </span>

                                        <span class="ij-dropdown-item-desc">
                                            Pantau aktivitas sistem
                                        </span>

                                    </span>

                                </a>


                            </div>

                        </div>


                    @endif


                </div>

            </div>



            {{-- RIGHT USER --}}
            <div class="ij-navbar-right">


                <div
                    class="ij-dropdown-wrap"
                    @click.away="userOpen = false"
                >


                    <button
                        type="button"
                        class="ij-user-button"
                        @click="userOpen = !userOpen"
                    >


                        <span class="ij-user-avatar">

                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                        </span>



                        <span class="ij-user-copy">

                            <span class="ij-user-name">
                                {{ Auth::user()->name }}
                            </span>

                            <span class="ij-user-role">
                                {{ Auth::user()->role }}
                            </span>

                        </span>



                        <svg
                            class="ij-chevron"
                            :class="{ 'is-open': userOpen }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >

                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06Z"
                                clip-rule="evenodd"
                            />

                        </svg>


                    </button>



                    <div
                        x-cloak
                        x-show="userOpen"
                        x-transition
                        class="ij-dropdown-panel ij-user-panel"
                    >


                        <div class="ij-user-info">

                            <div class="ij-user-info-name">
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ij-user-info-email">
                                {{ Auth::user()->email }}
                            </div>

                        </div>



                        <div class="ij-dropdown-divider"></div>



                        {{-- PROFILE --}}
                        <a
                            href="{{ route('profile.edit') }}"
                            class="ij-dropdown-item"
                        >


                            <span class="ij-dropdown-item-icon">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>

                            </span>



                            <span class="ij-dropdown-item-copy">

                                <span class="ij-dropdown-item-name">
                                    Profile
                                </span>

                                <span class="ij-dropdown-item-desc">
                                    Pengaturan akun
                                </span>

                            </span>


                        </a>



                        {{-- LOGOUT --}}
                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >

                            @csrf


                            <button
                                type="submit"
                                class="ij-dropdown-item"
                            >


                                <span
                                    class="ij-dropdown-item-icon"
                                    style="
                                        color:#dc2626;
                                        background:#fef2f2;
                                    "
                                >

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M10 17l5-5-5-5"/>
                                        <path d="M15 12H3"/>
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                    </svg>

                                </span>



                                <span class="ij-dropdown-item-copy">

                                    <span
                                        class="ij-dropdown-item-name"
                                        style="color:#dc2626;"
                                    >
                                        Log Out
                                    </span>

                                    <span class="ij-dropdown-item-desc">
                                        Keluar dari aplikasi
                                    </span>

                                </span>


                            </button>


                        </form>


                    </div>


                </div>


            </div>



            {{-- MOBILE BUTTON --}}
            <button
                type="button"
                class="ij-mobile-toggle"
                @click="mobileOpen = !mobileOpen"
                aria-label="Buka menu navigasi"
            >


                <svg
                    x-show="!mobileOpen"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M4 6h16"/>
                    <path d="M4 12h16"/>
                    <path d="M4 18h16"/>
                </svg>



                <svg
                    x-cloak
                    x-show="mobileOpen"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>


            </button>


        </div>

    </div>



    {{-- MOBILE MENU --}}
    <div
        x-cloak
        x-show="mobileOpen"
        x-transition
        class="ij-mobile-panel"
    >


        <div class="ij-mobile-inner">


            <div class="ij-mobile-section-label">
                Menu Utama
            </div>



            {{-- DASHBOARD --}}
            <a
                href="{{ route('dashboard') }}"
                class="ij-mobile-link
                {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M3 3h7v7H3z"/>
                    <path d="M14 3h7v7h-7z"/>
                    <path d="M3 14h7v7H3z"/>
                    <path d="M14 14h7v7h-7z"/>
                </svg>

                Dashboard

            </a>



            {{-- ADMIN MOBILE --}}
            @if(auth()->user()->role === 'admin')


                <a
                    href="{{ route('barang.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('barang.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>

                    Barang

                </a>



                <a
                    href="{{ route('kategori.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('kategori.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 3h7v7H3z"/>
                        <path d="M14 3h7v7h-7z"/>
                        <path d="M3 14h7v7H3z"/>
                        <path d="M14 14h7v7h-7z"/>
                    </svg>

                    Kategori

                </a>



                <a
                    href="{{ route('barang-masuk.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('barang-masuk.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 3v12"/>
                        <path d="m7 10 5 5 5-5"/>
                        <path d="M5 21h14"/>
                    </svg>

                    Barang Masuk

                </a>



                <a
                    href="{{ route('teknisi.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('teknisi.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>

                    Teknisi

                </a>


            @endif



            {{-- BARANG KELUAR --}}
            <a
                href="{{ route('barang-keluar.index') }}"
                class="ij-mobile-link
                {{ request()->routeIs('barang-keluar.*') ? 'is-active' : '' }}"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 21V9"/>
                    <path d="m17 14-5-5-5 5"/>
                    <path d="M5 3h14"/>
                </svg>

                Barang Keluar

            </a>



            {{-- RIWAYAT MOBILE KHUSUS TEKNISI --}}
            @if(auth()->user()->role === 'teknisi')

                <a
                    href="{{ route('riwayat.saya') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('riwayat.saya*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M3 12a9 9 0 1 0 3-6.7"/>
                        <path d="M3 4v6h6"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>

                    Riwayat Saya

                </a>

            @endif



            {{-- ADMIN MOBILE --}}
            @if(auth()->user()->role === 'admin')


                <div class="ij-mobile-section-label">
                    Laporan
                </div>


                <a
                    href="{{ route('laporan.stok.index') }}"
                    class="ij-mobile-link ij-mobile-report"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M4 19V9"/>
                        <path d="M10 19V5"/>
                        <path d="M16 19v-7"/>
                        <path d="M22 19H2"/>
                    </svg>

                    Laporan Stok

                </a>



                <div class="ij-mobile-section-label">
                    Administrasi
                </div>



                <a
                    href="{{ route('users.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('users.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>

                    Management User

                </a>



                <a
                    href="{{ route('activity-logs.index') }}"
                    class="ij-mobile-link
                    {{ request()->routeIs('activity-logs.*') ? 'is-active' : '' }}"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>

                    Audit Activity

                </a>


            @endif



            {{-- MOBILE USER --}}
            <div class="ij-mobile-user-card">


                <div class="ij-mobile-user-top">


                    <div class="ij-user-avatar">

                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                    </div>



                    <div class="ij-mobile-user-details">


                        <div class="ij-mobile-user-name">

                            {{ Auth::user()->name }}

                        </div>


                        <div class="ij-mobile-user-email">

                            {{ Auth::user()->email }}

                        </div>


                    </div>


                </div>



                <a
                    href="{{ route('profile.edit') }}"
                    class="ij-mobile-link"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>

                    Profile

                </a>



                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf


                    <button
                        type="submit"
                        class="ij-mobile-link"
                        style="
                            cursor:pointer;
                            color:#dc2626;
                        "
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        </svg>

                        Log Out

                    </button>


                </form>


            </div>


        </div>


    </div>


</nav>