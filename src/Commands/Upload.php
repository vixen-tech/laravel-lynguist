<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\spin;

class Upload extends Command
{
    protected $signature = 'lynguist:upload';

    protected $description = 'Upload translations to Lynguist.com';

    public function handle(): void
    {
        $translations = collect(config('lynguist.languages'))->mapWithKeys(function (string $language) {
            return [$language => json_decode(File::get(config('lynguist.output_path') . "/{$language}.json"), associative: true)];
        });

        $response = spin(
            fn () => Http::acceptJson()
                ->asJson()
                ->timeout(config('lynguist.connect.timeout', 120))
                ->withToken(config('lynguist.connect.api_token'))
                ->post('https://lynguist.com/api/translations', ['translations' => $translations]),
            'Uploading translations...'
        );

        $response?->onError(function (Response $response) {
            $this->error('An error occurred while uploading translations: ' . $response->body());
        });

        if ($response?->successful()) {
            $this->info('Upload completed.');
        }
    }
}
