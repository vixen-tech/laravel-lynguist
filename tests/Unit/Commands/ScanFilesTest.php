<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::delete(File::allFiles(__DIR__.'/../../Samples/lang'));
});

afterEach(function () {
    File::delete(File::allFiles(__DIR__.'/../../Samples/lang'));
    File::delete(config('lynguist.types_path'));
});

it('scans and extracts translations from files', function () {
    Config::set('lynguist.output_path', __DIR__.'/../../Samples/lang');
    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Artisan::call('lynguist:scan');

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2)
        ->and(File::exists(config('lynguist.types_path')))->toBeTrue();
});

it('scans and uploads translations to Lynguist.com', function () {
    Http::fake();
    Config::set('lynguist.output_path', __DIR__.'/../../Samples/lang');
    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Http::shouldReceive('acceptJson->asJson->timeout->withToken->post')->once();

    Artisan::call('lynguist:scan --upload');

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2)
        ->and(File::exists(config('lynguist.types_path')))->toBeTrue();
});

it('uploads translations to Lynguist.com', function () {
    Http::fake();
    Config::set('lynguist.output_path', __DIR__ . '/../../Samples/upload');
    Config::set('lynguist.languages', ['en']);

    expect(File::files(config('lynguist.output_path')))->toHaveCount(1);

    Http::shouldReceive('acceptJson->asJson->timeout->withToken->post')->once();

    Artisan::call('lynguist:upload');

    expect(File::files(config('lynguist.output_path')))->toHaveCount(1);
});
