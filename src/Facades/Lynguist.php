<?php

namespace Vixen\Lynguist\Facades;

use Illuminate\Support\Facades\Facade;

class Lynguist extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lynguist';
    }
}
