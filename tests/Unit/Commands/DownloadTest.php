<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    File::delete(File::allFiles(__DIR__.'/../../Samples/lang'));
});

afterEach(function () {
    File::delete(File::allFiles(__DIR__.'/../../Samples/lang'));
    File::delete(config('lynguist.types_path'));
});

it('downloads translations and generates the TypeScript declaration file', function () {
    Config::set('lynguist.output_path', __DIR__.'/../../Samples/lang');

    Http::fake([
        'lynguist.com/api/translations' => Http::response([
            'translations' => [
                'en' => ['greeting' => 'Hello!'],
                'fr' => ['greeting' => 'Bonjour !'],
            ],
        ]),
    ]);

    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Artisan::call('lynguist:download');

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2)
        ->and(File::exists(config('lynguist.types_path')))->toBeTrue()
        ->and(File::get(config('lynguist.types_path')))->toContain("'greeting': string");
});

it('does not generate the TypeScript declaration file when no translations are returned', function () {
    Config::set('lynguist.output_path', __DIR__.'/../../Samples/lang');

    Http::fake([
        'lynguist.com/api/translations' => Http::response(['translations' => null]),
    ]);

    Artisan::call('lynguist:download');

    expect(File::exists(config('lynguist.types_path')))->toBeFalse();
});