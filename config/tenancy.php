<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforce tenant isolation
    |--------------------------------------------------------------------------
    |
    | When true, tenant models return no rows if no company is active for the
    | request ("fail closed"). Only disable for one-off maintenance scripts.
    |
    */

    'enforce' => env('TENANCY_ENFORCE', true),

    /*
    |--------------------------------------------------------------------------
    | API company header
    |--------------------------------------------------------------------------
    |
    | API clients select the active company by sending its UUID in this header.
    | When omitted, the user's last-used company is assumed.
    |
    */

    'header' => 'X-Company',

    'session_key' => 'current_company_id',

    'branch_session_key' => 'current_branch_id',

    'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
];
