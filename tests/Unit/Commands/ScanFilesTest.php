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
