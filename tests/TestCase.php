<?php

namespace Tests;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

/**
 * Class TestCase
 *
 * Used for implementing common method across test cases
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Make sure root facade is set for http client
         */
        Http::swap(new Factory());
    }
}
