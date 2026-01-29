<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Vixen\Lynguist\Facades\Lynguist;

it('scans directories for translation terms', function () {
    $dirs = config('lynguist.scannable_paths');

    expect(Lynguist::scan($dirs))->toHaveCount(6);

    Config::set('lynguist.allowed_extensions', null);

    expect(Lynguist::scan($dirs))->toHaveCount(7);
});

it('accepts a single directory', function () {
    $dir = __DIR__ . '/../../config';

    expect(Lynguist::scan($dir))->toHaveCount(0);
});

it('merges existing and new translations', function () {
    $terms = Lynguist::scan(config('lynguist.scannable_paths'));
    Config::set('lynguist.output_path', __DIR__ . '/../Samples');

    expect(Lynguist::merge($terms, 'en'))->toMatchArray([
        'blade-string' => null,
        'choice-directive' => null,
        'recursively-included' => null,
        'sample-class' => null,
        'simple-string' => 'Simple String',
        'welcome-double-quotes' => null,
    ]);
});

it('parses complex strings', function () {
    $path = __DIR__ . '/../Samples/other';
    Config::set('lynguist.output_path', $path);

    expect(Lynguist::scan($path))->toMatchArray([
        "It's good, :name",
        'Welcome to \":name\"',
    ]);
});

it('uses custom search functions', function () {
    Config::set('lynguist.search_for', ['__', 'trans', 'Label']);

    expect(Lynguist::scan(__DIR__ . '/../Samples/customsearch'))
        ->toContain('Default __')
        ->toContain('custom search function');
});

it('stores translations in language files', function () {
    $terms = Lynguist::scan(config('lynguist.scannable_paths'));

    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Lynguist::store($terms);

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2);

    File::delete(File::allFiles(config('lynguist.output_path')));
})->todo('Add assertions for each term.');

it('generates TypeScript declaration file', function () {
    $terms = Lynguist::scan(config('lynguist.scannable_paths'));

    Lynguist::generateTypeScriptFile($terms);

    $contents = File::get(config('lynguist.types_path'));

    expect(File::exists(config('lynguist.types_path')))->toBeTrue()
        ->and($contents)->toContain(
            "import '@vixen/lynguist'",
            "declare module '@vixen/lynguist/dist/types'",
            'interface LynguistTranslations',
            "'sample-class': string",
            "'welcome-double-quotes': string",
            "'blade-string': string",
            "'choice-directive': string",
            "'simple-string': string",
            "'recursively-included': string",
        );
});

it('returns all translations of a given language', function () {
    Config::set('lynguist.output_path', __DIR__ . '/../Samples');

    expect(Lynguist::translations())->toHaveCount(6);
});

it('syncs all translations for all languages', function () {
    expect(File::allFiles(config('lynguist.output_path')))->toBeEmpty();

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
        ],
        'fr' => [
            'greeting' => 'Bonjour !',
        ],
    ]);

    expect(File::allFiles(config('lynguist.output_path')))->toHaveCount(2);

    File::delete(File::allFiles(config('lynguist.output_path')));
});
