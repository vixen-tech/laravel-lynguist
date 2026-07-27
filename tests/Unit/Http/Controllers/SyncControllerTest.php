<?php

use Illuminate\Support\Facades\Queue;
use Vixen\Lynguist\Jobs\DownloadTranslations;

it('returns 204 and dispatches a download job', function () {
    Queue::fake();

    $this->post('/lynguist/sync')->assertNoContent();

    Queue::assertPushed(DownloadTranslations::class);
});
