<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-1">

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">

                Audit Activity

            </h2>

            <p class="text-sm text-gray-500">

                Riwayat aktivitas pengguna dan perubahan data pada sistem.

            </p>

        </div>

    </x-slot>


    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- VALIDATION ERROR --}}
            @if ($errors->any())

                <div
                    class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >

                    <ul class="list-disc ps-5 space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- FILTER --}}
            <div
                class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-sm sm:rounded-lg"
            >

                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                >

                    <h3 class="font-semibold text-gray-800">

                        Filter Aktivitas

                    </h3>

                </div>


                <div class="p-6">

                    <form
                        method="GET"
                        action="{{ route('activity-logs.index') }}"
                    >

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4"
                        >


                            {{-- USER --}}
                            <div>

                                <label
                                    for="user_id"
                                    class="block mb-1 text-sm font-medium text-gray-700"
                                >
                                    User
                                </label>


                                <select
                                    id="user_id"
                                    name="user_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                    <option value="">
                                        Semua User
                                    </option>


                                    @foreach ($users as $user)

                                        <option
                                            value="{{ $user->id }}"
                                            @selected(
                                                (string) request('user_id')
                                                === (string) $user->id
                                            )
                                        >

                                            {{ $user->name }}

                                            @if ($user->email)
                                                - {{ $user->email }}
                                            @endif

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- ACTION --}}
                            <div>

                                <label
                                    for="action"
                                    class="block mb-1 text-sm font-medium text-gray-700"
                                >
                                    Action
                                </label>


                                <select
                                    id="action"
                                    name="action"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                    <option value="">
                                        Semua Action
                                    </option>

                                    <option
                                        value="create"
                                        @selected(request('action') === 'create')
                                    >
                                        Create
                                    </option>

                                    <option
                                        value="update"
                                        @selected(request('action') === 'update')
                                    >
                                        Update
                                    </option>

                                    <option
                                        value="delete"
                                        @selected(request('action') === 'delete')
                                    >
                                        Delete
                                    </option>

                                </select>

                            </div>


                            {{-- MODULE --}}
                            <div>

                                <label
                                    for="module"
                                    class="block mb-1 text-sm font-medium text-gray-700"
                                >
                                    Modul
                                </label>


                                <select
                                    id="module"
                                    name="module"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                    <option value="">
                                        Semua Modul
                                    </option>


                                    @foreach ($modules as $module)

                                        <option
                                            value="{{ $module }}"
                                            @selected(request('module') === $module)
                                        >

                                            {{ ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $module
                                                )
                                            ) }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            {{-- DATE FROM --}}
                            <div>

                                <label
                                    for="date_from"
                                    class="block mb-1 text-sm font-medium text-gray-700"
                                >
                                    Tanggal Awal
                                </label>


                                <input
                                    id="date_from"
                                    type="date"
                                    name="date_from"
                                    value="{{ request('date_from') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                            </div>


                            {{-- DATE TO --}}
                            <div>

                                <label
                                    for="date_to"
                                    class="block mb-1 text-sm font-medium text-gray-700"
                                >
                                    Tanggal Akhir
                                </label>


                                <input
                                    id="date_to"
                                    type="date"
                                    name="date_to"
                                    value="{{ request('date_to') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >

                            </div>

                        </div>


                        <div
                            class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-3"
                        >

                            <a
                                href="{{ route('activity-logs.index') }}"
                                class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                            >
                                Reset
                            </a>


                            <button
                                type="submit"
                                class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                            >
                                Terapkan Filter
                            </button>

                        </div>

                    </form>

                </div>

            </div>


            {{-- ACTIVITY TABLE --}}
            <div
                class="overflow-hidden bg-white border border-gray-200 shadow-sm sm:rounded-lg"
            >

                <div class="overflow-x-auto">

                    <table
                        class="min-w-full divide-y divide-gray-200"
                    >

                        <thead class="bg-gray-50">

                            <tr>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap"
                                >
                                    Waktu
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap"
                                >
                                    User
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                >
                                    Action
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                >
                                    Modul
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                >
                                    Deskripsi
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                >
                                    Detail Perubahan
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap"
                                >
                                    IP Address
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            class="bg-white divide-y divide-gray-200"
                        >

                            @forelse ($activityLogs as $activityLog)

                                @php

                                    $formatValue = function ($value) {

                                        if (is_null($value)) {
                                            return '-';
                                        }

                                        if (is_bool($value)) {
                                            return $value
                                                ? 'Ya'
                                                : 'Tidak';
                                        }

                                        if (is_array($value)) {

                                            return json_encode(
                                                $value,
                                                JSON_UNESCAPED_UNICODE
                                                | JSON_UNESCAPED_SLASHES
                                            );
                                        }

                                        if ($value === '') {
                                            return '-';
                                        }

                                        return (string) $value;
                                    };


                                    $oldValues =
                                        is_array($activityLog->old_values)
                                            ? $activityLog->old_values
                                            : [];


                                    $newValues =
                                        is_array($activityLog->new_values)
                                            ? $activityLog->new_values
                                            : [];


                                    $badgeClass = match (
                                        $activityLog->action
                                    ) {

                                        'create' =>
                                            'bg-green-100 text-green-800',

                                        'update' =>
                                            'bg-yellow-100 text-yellow-800',

                                        'delete' =>
                                            'bg-red-100 text-red-800',

                                        default =>
                                            'bg-gray-100 text-gray-800',
                                    };

                                @endphp


                                <tr class="hover:bg-gray-50">


                                    {{-- WAKTU --}}
                                    <td
                                        class="px-4 py-4 text-sm text-gray-700 whitespace-nowrap"
                                    >

                                        {{ $activityLog->created_at
                                            ? $activityLog->created_at
                                                ->format('d/m/Y H:i')
                                            : '-' }}

                                    </td>


                                    {{-- USER --}}
                                    <td
                                        class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap"
                                    >

                                        <div class="font-medium">

                                            {{ $activityLog->user?->name
                                                ?? 'User terhapus' }}

                                        </div>


                                        @if ($activityLog->user?->email)

                                            <div
                                                class="mt-1 text-xs text-gray-500"
                                            >

                                                {{ $activityLog->user->email }}

                                            </div>

                                        @endif

                                    </td>


                                    {{-- ACTION --}}
                                    <td class="px-4 py-4">

                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}"
                                        >

                                            {{ strtoupper(
                                                $activityLog->action
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- MODULE --}}
                                    <td
                                        class="px-4 py-4 text-sm text-gray-700"
                                    >

                                        <span
                                            class="font-medium"
                                        >

                                            {{ ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $activityLog->module
                                                )
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- DESCRIPTION --}}
                                    <td
                                        class="px-4 py-4 text-sm text-gray-700 min-w-[260px]"
                                    >

                                        {{ $activityLog->description
                                            ?? '-' }}

                                    </td>


                                    {{-- DETAIL --}}
                                    <td
                                        class="px-4 py-4 min-w-[430px]"
                                    >

                                        <div
                                            class="mb-2 text-xs font-semibold text-gray-700"
                                        >
                                            Detail Perubahan
                                        </div>


                                        <div
                                            class="grid grid-cols-1 xl:grid-cols-2 gap-3"
                                        >


                                            {{-- SEBELUM --}}
                                            <div
                                                class="overflow-hidden rounded-lg border border-red-200"
                                            >

                                                <div
                                                    class="px-3 py-2 bg-red-50 text-xs font-semibold text-red-700"
                                                >
                                                    Sebelum
                                                </div>


                                                <div class="px-3 py-2">

                                                    @forelse ($oldValues as $key => $value)

                                                        <div
                                                            class="py-2 border-b border-gray-100 last:border-b-0"
                                                        >

                                                            <div
                                                                class="mb-1 text-[11px] font-semibold uppercase text-gray-400"
                                                            >

                                                                {{ ucwords(
                                                                    str_replace(
                                                                        '_',
                                                                        ' ',
                                                                        $key
                                                                    )
                                                                ) }}

                                                            </div>


                                                            <div
                                                                class="text-xs text-gray-700 break-words"
                                                            >

                                                                {{ $formatValue(
                                                                    $value
                                                                ) }}

                                                            </div>

                                                        </div>

                                                    @empty

                                                        <div
                                                            class="py-2 text-xs italic text-gray-400"
                                                        >
                                                            Tidak ada data sebelumnya.
                                                        </div>

                                                    @endforelse

                                                </div>

                                            </div>


                                            {{-- SESUDAH --}}
                                            <div
                                                class="overflow-hidden rounded-lg border border-green-200"
                                            >

                                                <div
                                                    class="px-3 py-2 bg-green-50 text-xs font-semibold text-green-700"
                                                >
                                                    Sesudah
                                                </div>


                                                <div class="px-3 py-2">

                                                    @forelse ($newValues as $key => $value)

                                                        <div
                                                            class="py-2 border-b border-gray-100 last:border-b-0"
                                                        >

                                                            <div
                                                                class="mb-1 text-[11px] font-semibold uppercase text-gray-400"
                                                            >

                                                                {{ ucwords(
                                                                    str_replace(
                                                                        '_',
                                                                        ' ',
                                                                        $key
                                                                    )
                                                                ) }}

                                                            </div>


                                                            <div
                                                                class="text-xs text-gray-700 break-words"
                                                            >

                                                                {{ $formatValue(
                                                                    $value
                                                                ) }}

                                                            </div>

                                                        </div>

                                                    @empty

                                                        <div
                                                            class="py-2 text-xs italic text-gray-400"
                                                        >
                                                            Tidak ada data sesudahnya.
                                                        </div>

                                                    @endforelse

                                                </div>

                                            </div>


                                        </div>

                                    </td>


                                    {{-- IP --}}
                                    <td
                                        class="px-4 py-4 text-xs font-mono text-gray-600 whitespace-nowrap"
                                    >

                                        {{ $activityLog->ip_address
                                            ?? '-' }}

                                    </td>


                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center text-sm text-gray-500"
                                    >

                                        Belum ada aktivitas yang tercatat.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- PAGINATION --}}
                @if ($activityLogs->hasPages())

                    <div
                        class="px-6 py-4 border-t border-gray-200"
                    >

                        {{ $activityLogs->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>