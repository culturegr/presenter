<?php

namespace CultureGr\Presenter\Tests\Fixtures;

use Illuminate\Support\Collection;
use Mockery;

/**
 * A paginator fixture that creates a mock paginator with the __call method
 * to proxy to the collection, making it compatible with the presentCollection macro.
 */
class Paginator
{
    public function __invoke($users)
    {
        // Create a collection from the users
        $collection = collect($users);

        // Create a mock paginator
        $mock = Mockery::mock(\Illuminate\Contracts\Pagination\Paginator::class)
            ->shouldReceive('items')
            ->andReturn($users)
            ->shouldReceive('path')
            ->andReturn('http://example.com/pagination')
            ->shouldReceive('lastPage')
            ->andReturn(100)
            ->shouldReceive('previousPageUrl')
            ->andReturn(null)
            ->shouldReceive('nextPageUrl')
            ->andReturn(2)
            ->shouldReceive('currentPage')
            ->andReturn(1)
            ->shouldReceive('firstItem')
            ->andReturn(1)
            ->shouldReceive('perPage')
            ->andReturn(15)
            ->shouldReceive('lastItem')
            ->andReturn(10)
            ->shouldReceive('total')
            ->andReturn(10)
            ->getMock();

        // Mock the presentCollection method to call the static pagination method
        // This simulates the end result of the CollectionMacros detection mechanism
        $mock->shouldReceive('presentCollection')
            ->with(Mockery::any())
            ->andReturnUsing(function ($presenterClass) use ($mock) {
                // Call the static pagination method directly
                // This is what would happen if the CollectionMacros class detected it was called from a paginator
                return $presenterClass::pagination($mock);
            });

        return $mock;
    }
}
