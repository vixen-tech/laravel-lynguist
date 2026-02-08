<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Vixen\Lynguist\Lynguist;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;

class Scan extends Command
{
    protected $signature = 'lynguist:scan {--upload : Upload translations to Lynguist.com}';

    protected $description = 'Scan files for translations';

    public function handle(Lynguist $lynguist): void
    {
        $files = $lynguist->getScannableFiles(config('lynguist.scannable_paths'));

        $terms = collect(
            progress(
                label: 'Scanning files...',
                steps: $files,
                callback: fn (SplFileInfo $file) => $lynguist->extractFrom(File::get($file)),
            )
        )->flatten()->unique()->values();

        spin(function () use ($lynguist, $terms) {
            $lynguist->store($terms);
            $lynguist->generateTypeScriptFile($terms);
        }, 'Saving translations...');

        $this->info(sprintf('Scan completed. Found %s translation keys.', $terms->count()));

        if ($this->option('upload')) {
            $this->call('lynguist:upload');
        }
    }
}
