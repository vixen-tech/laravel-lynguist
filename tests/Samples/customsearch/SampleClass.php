<?php

namespace Vixen\Lynguist\Tests\Samples\customsearch;

#[\Attribute]
class Label
{

}

#[Label('custom search function')]
class SampleClass
{
    public function __construct()
    {
        __('Default __');
    }
}
