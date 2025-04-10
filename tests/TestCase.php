<?php

namespace CultureGr\Presenter\Tests;

use CultureGr\Presenter\PresenterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            PresenterServiceProvider::class,
        ];
    }
}
