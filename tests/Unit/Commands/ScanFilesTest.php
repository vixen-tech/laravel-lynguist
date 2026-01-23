<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('scans and extracts translations from files', function () {
    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Artisan::call('lynguist:scan');

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2)
        ->and(File::exists(config('lynguist.types_path')))->toBeTrue();

    File::delete(File::allFiles(config('lynguist.output_path')));
    File::delete(config('lynguist.types_path'));
});

it('scans and uploads translations to Lynguist.com', function () {
    Http::fake();
    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Http::shouldReceive('acceptJson->asJson->post')->once();

    Artisan::call('lynguist:scan --upload');

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2)
        ->and(File::exists(config('lynguist.types_path')))->toBeTrue();

    File::delete(File::allFiles(config('lynguist.output_path')));
    File::delete(config('lynguist.types_path'));
});

it('uploads translations to Lynguist.com', function () {
    Http::fake();
    Config::set('lynguist.output_path', __DIR__ . '/../../Samples/upload');
    Config::set('lynguist.languages', ['en']);

    expect(File::files(config('lynguist.output_path')))->toHaveCount(1);

    Http::shouldReceive('acceptJson->asJson->post')->once();

    Artisan::call('lynguist:upload');

    expect(File::files(config('lynguist.output_path')))->toHaveCount(1);

    File::delete(config('lynguist.types_path'));
});
