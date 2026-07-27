<?php

use Illuminate\Support\Facades\Route;
use Vixen\Lynguist\Controllers\SyncController;

Route::post('/lynguist/sync', SyncController::class);
