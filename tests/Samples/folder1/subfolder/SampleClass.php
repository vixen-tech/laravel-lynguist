<?php

class SampleClass
{
    public function __construct()
    {
        __('sample-class');
    }

    public function welcome(): string
    {
        /** @noinspection PhpUnnecessaryDoubleQuotesInspection */
        return __("welcome-double-quotes");
    }
}
