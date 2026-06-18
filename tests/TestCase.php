<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Laravel 13's PreventRequestForgery requires both runningInConsole()
        // AND runningUnitTests() to skip CSRF. Disable it explicitly to avoid
        // spurious 419s on POST/PUT/DELETE test requests.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }
}
