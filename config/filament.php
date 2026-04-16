<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    |
    | This is the path where your Filament admin panel will be accessible from.
    | By default, it is accessible from /admin
    |
    */
    'path' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Filament Domain
    |--------------------------------------------------------------------------
    |
    | This is the domain where Filament will be accessible from.
    |
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    | Whether to show breadcrumbs in the admin panel.
    |
    */
    'breadcrumbs' => true,

    /*
    |--------------------------------------------------------------------------
    | Dark Mode
    |--------------------------------------------------------------------------
    |
    | Whether to support dark mode in the admin panel.
    |
    */
    'dark_mode' => true,

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | The timezone for the Filament admin panel.
    |
    */
    'timezone' => config('app.timezone'),
];
