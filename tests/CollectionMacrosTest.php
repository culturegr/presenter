<?php

namespace CultureGr\Presenter\Tests;

use CultureGr\Presenter\Tests\Fixtures\Role;
use CultureGr\Presenter\Tests\Fixtures\RolePresenter;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Tests specifically for the CollectionMacros detection mechanism.
 *
 * These tests verify that the CollectionMacros class correctly detects
 * whether it's being called from a regular collection or a paginator
 * using the debug_backtrace() mechanism.
 */
class CollectionMacrosTest extends TestCase
{
    /** @test */
    public function it_detects_call_from_paginator_using_backtrace()
    {
        // Create a collection of roles
        $roles = factory(Role::class, 3)->make();
        $collection = collect($roles);

        // Create a custom paginator that uses __call to forward to the collection
        // This simulates how Laravel's AbstractPaginator works
        $paginator = new class($roles) implements LengthAwarePaginator {
            protected $items;
            protected $collection;

            public function __construct($items)
            {
                $this->items = $items;
                $this->collection = collect($items);
            }

            // Implement required interface methods
            public function items() { return $this->items; }
            public function count() { return count($this->items); }
            public function perPage() { return 15; }
            public function currentPage() { return 1; }
            public function path() { return 'http://example.com/pagination'; }
            public function firstItem() { return 1; }
            public function lastItem() { return count($this->items); }
            public function lastPage() { return 1; }
            public function url($page) { return 'http://example.com/pagination?page=' . $page; }
            public function total() { return count($this->items); }
            public function hasPages() { return false; }
            public function hasMorePages() { return false; }
            public function getUrlRange($start, $end) { return []; }
            public function nextPageUrl() { return null; }
            public function previousPageUrl() { return null; }
            public function appends($key, $value = null) { return $this; }
            public function fragment($fragment = null) { return $this; }
            public function render($view = null, $data = []) { return ''; }
            public function isEmpty() { return empty($this->items); }
            public function isNotEmpty() { return !$this->isEmpty(); }
            public function links($view = null, $data = []) { return ''; }
            public function toArray() { return $this->items; }

            // This is the key method that simulates Laravel's AbstractPaginator behavior
            // It forwards unknown method calls to the collection
            public function __call($method, $parameters)
            {
                return $this->collection->$method(...$parameters);
            }
        };

        // Present both using presentCollection
        $presentedCollection = $collection->presentCollection(RolePresenter::class);
        $presentedPaginator = $paginator->presentCollection(RolePresenter::class);

        // Verify that the collection returns a simple collection of presenters
        $this->assertInstanceOf(Collection::class, $presentedCollection);
        $this->assertInstanceOf(RolePresenter::class, $presentedCollection->first());

        // Verify that the paginator returns a structured result with data, links, and meta
        // This proves that the backtrace detection mechanism is working
        $this->assertInstanceOf(Collection::class, $presentedPaginator);
        $this->assertArrayHasKey('data', $presentedPaginator->toArray());
        $this->assertArrayHasKey('links', $presentedPaginator->toArray());
        $this->assertArrayHasKey('meta', $presentedPaginator->toArray());
    }
}
