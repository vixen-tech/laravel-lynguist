<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Vixen\Lynguist\Lynguist;

class ScanFiles extends Command
{
    protected $signature = 'lynguist:scan';

    protected $description = 'Scan files for translations.';

    public function handle(Lynguist $lynguist): void
    {
        $this->line('Scanning files...');

        $terms = $lynguist->scan(config('lynguist.scannable_paths'));
        $lynguist->store($terms);
        $lynguist->generateTypeScriptFile($terms);

        $this->info('Scan completed.');
    }
}
