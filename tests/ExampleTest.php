<?php

namespace Mblarsen\LaravelDynamicFactories\Tests;

use Orchestra\Testbench\TestCase;
use Mblarsen\LaravelDynamicFactories\LaravelDynamicFactoriesServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelDynamicFactoriesServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
