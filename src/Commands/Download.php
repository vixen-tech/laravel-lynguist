<?php

namespace Vixen\Lynguist\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Vixen\Lynguist\Lynguist;

use function Laravel\Prompts\spin;

class Download extends Command
{
    protected $signature = 'lynguist:download';

    protected $description = 'Download translations from Lynguist.com';

    public function handle(Lynguist $lynguist): void
    {
        $response = spin(
            fn () => Http::acceptJson()
                ->asJson()
                ->timeout(config('lynguist.connect.timeout', 120))
                ->withToken(config('lynguist.connect.api_token'))
                ->get('https://lynguist.com/api/translations'),
            'Downloading translations...'
        );

        $response->onError(function (Response $response) {
            $this->error('An error occurred while downloading translations: ' . $response->body());
        });

        if ($response->successful()) {
            $translations = $response->json('translations');

            if ($translations) {
                $lynguist->sync($translations);
                $this->info('Download completed.');
            } else {
                $this->warn('No translations found in response.');
            }
        }
    }
}
