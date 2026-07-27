<?php

namespace Vixen\Lynguist\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Vixen\Lynguist\Jobs\DownloadTranslations;

class SyncController extends Controller
{
    public function __invoke(): Response
    {
        DownloadTranslations::dispatch();

        return response()->noContent();
    }
}
