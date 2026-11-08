<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI FILTER
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'action' => [
                'nullable',
                Rule::in([
                    'create',
                    'update',
                    'delete',
                ]),
            ],

            'module' => [
                'nullable',
                'string',
                'max:100',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | QUERY DASAR ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $query = ActivityLog::with('user')
            ->latest();


        /*
        |--------------------------------------------------------------------------
        | FILTER ACTION
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['action'])) {

            $query->where(
                'action',
                $validated['action']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER MODULE
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['module'])) {

            $query->where(
                'module',
                $validated['module']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER USER
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['user_id'])) {

            $query->where(
                'user_id',
                $validated['user_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL AWAL
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['date_from'])) {

            $query->whereDate(
                'created_at',
                '>=',
                $validated['date_from']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL AKHIR
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['date_to'])) {

            $query->whereDate(
                'created_at',
                '<=',
                $validated['date_to']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $activityLogs = $query
            ->paginate(25)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | DATA USER UNTUK FILTER
        |--------------------------------------------------------------------------
        */

        $users = User::query()
            ->orderBy('name', 'asc')
            ->get([
                'id',
                'name',
                'email',
            ]);


        /*
        |--------------------------------------------------------------------------
        | DAFTAR MODULE UNTUK FILTER
        |--------------------------------------------------------------------------
        */

        $modules = ActivityLog::query()
            ->whereNotNull('module')
            ->where('module', '!=', '')
            ->select('module')
            ->distinct()
            ->orderBy('module', 'asc')
            ->pluck('module');


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'activity_logs.index',
            compact(
                'activityLogs',
                'users',
                'modules'
            )
        );
    }
}