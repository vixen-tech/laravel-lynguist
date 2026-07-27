<?php

namespace Vixen\Lynguist\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Vixen\Lynguist\Lynguist;

class DownloadTranslations implements ShouldQueue
{
    use Queueable;

    /**
     * @throws ConnectionException
     */
    public function handle(Lynguist $lynguist): void
    {
        $response = Http::acceptJson()
            ->asJson()
            ->timeout(config('lynguist.connect.timeout', 120))
            ->withToken(config('lynguist.connect.api_token'))
            ->get('https://lynguist.com/api/translations');

        if ($response->successful()) {
            $translations = $response->json('translations');

            if ($translations) {
                $lynguist->sync($translations);
            }
        }
    }
}
