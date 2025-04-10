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
    public function __invoke($items)
    {
        // Create a collection from the items
        $collection = collect($items);

        // Create a mock paginator
        $mock = Mockery::mock(\Illuminate\Contracts\Pagination\Paginator::class)
            ->shouldReceive('items')->andReturn($items)
            ->shouldReceive('path')->andReturn('http://example.com/pagination')
            ->shouldReceive('lastPage')->andReturn(100)
            ->shouldReceive('previousPageUrl')->andReturn(null)
            ->shouldReceive('nextPageUrl')->andReturn(2)
            ->shouldReceive('currentPage')->andReturn(1)
            ->shouldReceive('firstItem')->andReturn(1)
            ->shouldReceive('perPage')->andReturn(15)
            ->shouldReceive('lastItem')->andReturn(10)
            ->shouldReceive('total')->andReturn(10)
            ->shouldReceive('url')->withArgs([1])
            ->andReturn('http://example.com/pagination?page=1')->shouldReceive('url')
            ->withArgs([100])->andReturn('http://example.com/pagination?page=100')
            ->getMock();

        // Create a new mock for appends with only the methods needed for Presenter::pagination
        $appendedMock = Mockery::mock(\Illuminate\Contracts\Pagination\Paginator::class)
            ->shouldReceive('items')->andReturn($items)
            ->shouldReceive('path')->andReturn('http://example.com/pagination')
            ->shouldReceive('lastPage')->andReturn(100)
            ->shouldReceive('previousPageUrl')->andReturn(null)
            ->shouldReceive('nextPageUrl')->andReturn('http://example.com/pagination?foo=bar&page=2')
            ->shouldReceive('currentPage')->andReturn(1)
            ->shouldReceive('firstItem')->andReturn(1)
            ->shouldReceive('perPage')->andReturn(15)
            ->shouldReceive('lastItem')->andReturn(10)
            ->shouldReceive('total')->andReturn(10)
            ->shouldReceive('url')->with(1)->andReturn('http://example.com/pagination?foo=bar&page=1')
            ->shouldReceive('url')->with(100)->andReturn('http://example.com/pagination?foo=bar&page=100')
            ->getMock();

        // Add support for appends method
        $mock->shouldReceive('appends')
            ->with(['foo' => 'bar'])
            ->andReturn($appendedMock);

        // Create a new mock for withQueryString with only the methods needed for Presenter::pagination
        $queryStringMock = Mockery::mock(\Illuminate\Contracts\Pagination\Paginator::class)
            ->shouldReceive('items')->andReturn($items)
            ->shouldReceive('path')->andReturn('http://example.com/pagination')
            ->shouldReceive('lastPage')->andReturn(100)
            ->shouldReceive('previousPageUrl')->andReturn(null)
            ->shouldReceive('nextPageUrl')->andReturn('http://example.com/pagination?query=test&page=2')
            ->shouldReceive('currentPage')->andReturn(1)
            ->shouldReceive('firstItem')->andReturn(1)
            ->shouldReceive('perPage')->andReturn(15)
            ->shouldReceive('lastItem')->andReturn(10)
            ->shouldReceive('total')->andReturn(10)
            ->shouldReceive('url')->with(1)->andReturn('http://example.com/pagination?query=test&page=1')
            ->shouldReceive('url')->with(100)->andReturn('http://example.com/pagination?query=test&page=100')
            ->getMock();

        // Add support for withQueryString method
        $mock->shouldReceive('withQueryString')
            ->withNoArgs()
            ->andReturn($queryStringMock);

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
