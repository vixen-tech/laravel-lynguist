<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Vixen\Lynguist\Jobs\DownloadTranslations;
use Vixen\Lynguist\Lynguist;

beforeEach(function () {
    File::ensureDirectoryExists(config('lynguist.output_path'));
});

afterEach(function () {
    File::deleteDirectory(config('lynguist.output_path'));
});

it('downloads translations and syncs them to disk', function () {
    Http::fake([
        'lynguist.com/api/translations' => Http::response([
            'translations' => [
                'en' => ['greeting' => 'Hello!'],
                'fr' => ['greeting' => 'Bonjour !'],
            ],
        ]),
    ]);

    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    (new DownloadTranslations())->handle(app(Lynguist::class));

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2);

    File::delete(File::allFiles(config('lynguist.output_path')));
});

it('does nothing when the response is unsuccessful', function () {
    Http::fake([
        'lynguist.com/api/translations' => Http::response(null, 500),
    ]);

    (new DownloadTranslations())->handle(app(Lynguist::class));

    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();
});

it('does nothing when translations are missing from the response', function () {
    Http::fake([
        'lynguist.com/api/translations' => Http::response(['translations' => null]),
    ]);

    (new DownloadTranslations())->handle(app(Lynguist::class));

    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();
});
