<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Vixen\Lynguist\Lynguist;

class ScanFiles extends Command
{
    protected $signature = 'lynguist:scan {--upload : Upload translations to Lynguist.com}';

    protected $description = 'Scan files for translations';

    public function handle(Lynguist $lynguist): void
    {
        $this->line('Scanning files...');

        $terms = $lynguist->scan(config('lynguist.scannable_paths'));
        $lynguist->store($terms);
        $lynguist->generateTypeScriptFile($terms);

        $this->info('Scan completed.');

        if ($this->option('upload')) {
            $this->line('Uploading translations...');

            $translations = collect(config('lynguist.languages'))->mapWithKeys(function (string $language) {
                return [$language => json_decode(File::get(config('lynguist.output_path') . "/{$language}.json"), associative: true)];
            });

            // @todo - DO NOT FORGET TO REPLACE WITH PROD URL!
            $response = Http::acceptJson()
                ->asJson()
                ->withToken(config('lynguist.connect.api_token'))
                ->post('https://lynguist.test/api/translations', compact('translations'));

            $response?->onError(function (Response $response) {
                $this->error('An error occurred while uploading translations: ' . $response->body());
            });

            $this->info('Upload completed.');
        }
    }
}
