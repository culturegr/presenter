<?php


namespace CultureGr\Presenter\Tests\Fixtures;


use Mockery;

class Paginator
{
    public function __invoke($users)
    {
        return Mockery::mock(\Illuminate\Contracts\Pagination\Paginator::class)
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
            ->mock();
    }
}
