<?php

namespace Vixen\Lynguist;

use Illuminate\Support\ServiceProvider;
use Vixen\Lynguist\Commands\Download;
use Vixen\Lynguist\Commands\Scan;
use Vixen\Lynguist\Commands\Upload;

class LynguistServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('lynguist', fn () => new Lynguist());

        $this->mergeConfigFrom(__DIR__.'/../config/lynguist.php', 'lynguist');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/lynguist.php' => $this->app->configPath('lynguist.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Scan::class,
                Download::class,
                Upload::class,
            ]);
        }
    }
}
