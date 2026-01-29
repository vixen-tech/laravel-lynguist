<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language is used as a reference for translations
    | or as a fallback.
    |
    */

    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Languages
    |--------------------------------------------------------------------------
    |
    | An array of language codes ("en", "fr", etc.) that will be used to
    | generate language files.
    |
    */

    'languages' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Search Functions
    |--------------------------------------------------------------------------
    |
    | An array of functions that will be looked for during file scanning.
    | This applies to both PHP and JavaScript functions.
    |
    */

    'search_for' => ['__', 'lang', 'trans', 'trans_choice', 'transChoice', 'choice'],

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    |
    | A path to the directory where languages files (`en.json`, `fr.json`)
    | should be saved.
    |
    */

    'output_path' => lang_path(),

    /*
    |--------------------------------------------------------------------------
    | TypeScript Declaration File
    |--------------------------------------------------------------------------
    |
    | A path to the file that should be used for generated TS types.
    | Possible values: a path string or `null`/`false` to disable this feature.
    |
    */

    'types_path' => resource_path('js/types/translations.d.ts'),

    /*
    |--------------------------------------------------------------------------
    | Folders to Scan
    |--------------------------------------------------------------------------
    |
    | A list of all directories where the scanner should look for translations.
    |
    */

    'scannable_paths' => [
        app_path(),
        resource_path('views'),
        resource_path('js'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions
    |--------------------------------------------------------------------------
    |
    | Only files with the specified extension will be scanned for translations.
    | Allowed values: an array of strings or `null`/`false` to allow all files.
    |
    */

    'allowed_extensions' => ['php', 'js', 'jsx', 'ts', 'tsx', 'vue'],

    /*
    |--------------------------------------------------------------------------
    | Connect
    |--------------------------------------------------------------------------
    |
    | Configure connection with Lynguist.com.
    |
    */

    'connect' => [

        // Generated token for a project
        'api_token' => env('LYNGUIST_API_TOKEN'),

        // URL that is called by Lynguist.com to synchronize translations
        'sync_url' => env('LYNGUIST_SYNC_URL'),

    ],

];
