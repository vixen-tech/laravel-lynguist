<?php

namespace Vixen\Lynguist\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vixen\Lynguist\LynguistServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LynguistServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('lynguist.output_path', __DIR__ . '/Samples/output');
        $app['config']->set('lynguist.types_path', __DIR__ . '/Samples/translations.d.ts');
        $app['config']->set('lynguist.scannable_paths', [
            __DIR__ . '/Samples/folder1/subfolder',
            __DIR__ . '/Samples/folder2',
            __DIR__ . '/Samples/folder2/subfolder',
        ]);
    }
}
