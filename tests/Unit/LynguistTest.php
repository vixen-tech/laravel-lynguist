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

it('creates the output directory when storing translations', function () {
    $base = __DIR__ . '/../Samples/lang';
    $path = "{$base}/nested";
    Config::set('lynguist.output_path', $path);
    File::deleteDirectory($base);

    $terms = Lynguist::scan(config('lynguist.scannable_paths'));

    expect(File::isDirectory($path))->toBeFalse();

    Lynguist::store($terms);

    expect(File::isDirectory($path))->toBeTrue()
        ->and(File::allFiles($path))->toHaveCount(2);

    File::deleteDirectory($base);
    File::ensureDirectoryExists($base);
});

it('generates TypeScript declaration file', function () {
    $terms = Lynguist::scan(config('lynguist.scannable_paths'));

    Lynguist::generateTypeScriptFile($terms);

    $contents = File::get(config('lynguist.types_path'));

    expect(File::exists(config('lynguist.types_path')))->toBeTrue()
        ->and($contents)->toContain(
            "import '@vixen-tech/lynguist'",
            "declare module '@vixen-tech/lynguist/dist/types'",
            'interface LynguistTranslations',
            "'sample-class': string",
            "'welcome-double-quotes': string",
            "'blade-string': string",
            "'choice-directive': string",
            "'simple-string': string",
            "'recursively-included': string",
        );
});

it('creates the output directory for the TypeScript declaration file', function () {
    $base = __DIR__ . '/../Samples/lang';
    $path = "{$base}/nested/translations.d.ts";
    Config::set('lynguist.types_path', $path);
    File::deleteDirectory($base);

    $terms = Lynguist::scan(config('lynguist.scannable_paths'));

    expect(File::exists($path))->toBeFalse();

    Lynguist::generateTypeScriptFile($terms);

    expect(File::exists($path))->toBeTrue();

    File::deleteDirectory($base);
    File::ensureDirectoryExists($base);
});

it('returns all translations of a given language', function () {
    Config::set('lynguist.output_path', __DIR__ . '/../Samples');

    expect(Lynguist::translations())->toHaveCount(6);
});

it('creates the output directory when it does not exist', function () {
    $path = __DIR__ . '/../Samples/lang';
    Config::set('lynguist.output_path', $path);
    File::deleteDirectory($path);

    expect(File::isDirectory($path))->toBeFalse();

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
        ],
    ]);

    expect(File::isDirectory($path))->toBeTrue()
        ->and(File::exists("{$path}/en.json"))->toBeTrue();

    File::deleteDirectory($path);
    File::ensureDirectoryExists($path);
});

it('creates nested output directories recursively', function () {
    $base = __DIR__ . '/../Samples/lang';
    $path = "{$base}/nested/deep";
    Config::set('lynguist.output_path', $path);
    File::deleteDirectory($base);

    expect(File::isDirectory($path))->toBeFalse();

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
        ],
    ]);

    expect(File::isDirectory($path))->toBeTrue()
        ->and(File::exists("{$path}/en.json"))->toBeTrue();

    File::deleteDirectory($base);
    File::ensureDirectoryExists($base);
});

it('syncs all translations for all languages', function () {
    $path = __DIR__ . '/../Samples/lang';
    Config::set('lynguist.output_path', $path);
    File::ensureDirectoryExists($path);
    File::delete(File::allFiles($path));

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
        ],
        'fr' => [
            'greeting' => 'Bonjour !',
        ],
    ]);

    expect(File::allFiles($path))->toHaveCount(2);

    File::delete(File::allFiles($path));
});

it('replaces the file entirely when syncing without merge', function () {
    $path = __DIR__ . '/../Samples/lang';
    Config::set('lynguist.output_path', $path);
    File::ensureDirectoryExists($path);
    File::delete(File::allFiles($path));

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
            'farewell' => 'Goodbye!',
        ],
    ]);

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hi!',
        ],
    ]);

    expect(json_decode(File::get("{$path}/en.json"), associative: true))
        ->toBe(['greeting' => 'Hi!']);

    File::delete(File::allFiles($path));
});

it('merges new translations with existing ones when merge is true', function () {
    $path = __DIR__ . '/../Samples/lang';
    Config::set('lynguist.output_path', $path);
    File::ensureDirectoryExists($path);
    File::delete(File::allFiles($path));

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hello!',
            'farewell' => 'Goodbye!',
        ],
    ]);

    Lynguist::sync([
        'en' => [
            'greeting' => 'Hi!',
            'welcome' => 'Welcome!',
        ],
    ], merge: true);

    expect(json_decode(File::get("{$path}/en.json"), associative: true))
        ->toBe([
            'farewell' => 'Goodbye!',
            'greeting' => 'Hi!',
            'welcome' => 'Welcome!',
        ]);

    File::delete(File::allFiles($path));
});
