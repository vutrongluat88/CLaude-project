<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only treats an API request as stateful (session + cookies) when it
        // originates from a configured SPA domain. Browsers always send this header;
        // the test client does not, so set it to mirror how the Nuxt app calls the API.
        $this->withHeader('Origin', config('app.frontend_url'));
    }
}
