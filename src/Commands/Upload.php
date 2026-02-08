<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Upload extends Command
{
    protected $signature = 'lynguist:upload';

    protected $description = 'Upload translations to Lynguist.com';

    public function handle(): void
    {
        $this->line('Uploading translations...');

        $translations = collect(config('lynguist.languages'))->mapWithKeys(function (string $language) {
            return [$language => json_decode(File::get(config('lynguist.output_path') . "/{$language}.json"), associative: true)];
        });

        $response = Http::acceptJson()
            ->asJson()
            ->withToken(config('lynguist.connect.api_token'))
            ->post('https://lynguist.com/api/translations', compact('translations'));

        $response?->onError(function (Response $response) {
            $this->error('An error occurred while uploading translations: ' . $response->body());
        });

        $this->info('Upload completed.');
    }
}
