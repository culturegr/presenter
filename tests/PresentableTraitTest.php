<?php

namespace CultureGr\Presenter\Tests;

use CultureGr\Presenter\Presenter;
use CultureGr\Presenter\Tests\Fixtures\Role;
use CultureGr\Presenter\Tests\Fixtures\RolePresenter;
use CultureGr\Presenter\Tests\Fixtures\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Tests for the Presentable trait.
 *
 * These tests focus on verifying that the trait methods correctly delegate to the static methods,
 * rather than testing the implementation details which are covered in PresenterTest.php.
 */
class PresentableTraitTest extends TestCase
{
    /** @test */
    public function it_throws_exception_for_invalid_presenter_class()
    {
        $role = factory(Role::class)->make();

        $this->expectException(\InvalidArgumentException::class);

        $role->present(\stdClass::class);
    }

    /** @test */
    public function it_throws_exception_for_invalid_presenter_class_on_collection()
    {
        $roles = factory(Role::class, 1)->make();

        $this->expectException(\InvalidArgumentException::class);
        $roles->presentCollection(\stdClass::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Test equivalence between static methods and trait methods
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function static_make_method_is_equivalent_to_present_trait_method()
    {
        $role = factory(Role::class)->make([
            'name' => 'Admin',
            'description' => 'Administrator'
        ]);

        // Present using the static make method
        $presentedWithStatic = RolePresenter::make($role);

        // Present using the trait method
        $presentedWithTrait = $role->present(RolePresenter::class);

        // Verify that both methods produce exactly the same result
        $this->assertEquals(
            $presentedWithStatic->toArray(),
            $presentedWithTrait->toArray()
        );

        // Verify that both instances are of the same class
        $this->assertInstanceOf(RolePresenter::class, $presentedWithStatic);
        $this->assertInstanceOf(RolePresenter::class, $presentedWithTrait);

        // Verify that both instances have the same model
        $this->assertSame($role, $presentedWithStatic->getModel());
        $this->assertSame($role, $presentedWithTrait->getModel());

        // Verify that both instances behave the same way
        $this->assertEquals($presentedWithStatic->fullTitle(), $presentedWithTrait->fullTitle());
        $this->assertEquals($presentedWithStatic->name, $presentedWithTrait->name);
        $this->assertEquals($presentedWithStatic->description, $presentedWithTrait->description);
    }

    /** @test */
    public function static_collection_method_is_equivalent_to_presentCollection_trait_method()
    {
        // Create a collection of roles using the factory
        $roles = factory(Role::class, 3)->make();

        // Present using the static collection method
        $presentedWithStatic = RolePresenter::collection($roles);

        // Present using the trait method
        $presentedWithTrait = $roles->presentCollection(RolePresenter::class);

        // Verify that both methods produce exactly the same result
        $this->assertEquals(
            $presentedWithStatic->toArray(),
            $presentedWithTrait->toArray()
        );

        // Verify that both collections have the same size
        $this->assertEquals($presentedWithStatic->count(), $presentedWithTrait->count());

        // Verify that both collections contain the same type of objects
        $this->assertInstanceOf(RolePresenter::class, $presentedWithStatic->first());
        $this->assertInstanceOf(RolePresenter::class, $presentedWithTrait->first());

        // Verify that both collections behave the same way
        for ($i = 0; $i < $roles->count(); $i++) {
            $this->assertEquals($presentedWithStatic[$i]->fullTitle(), $presentedWithTrait[$i]->fullTitle());
            $this->assertEquals($presentedWithStatic[$i]->name, $presentedWithTrait[$i]->name);
            $this->assertEquals($presentedWithStatic[$i]->description, $presentedWithTrait[$i]->description);
        }
    }

    /** @test */
    public function static_pagination_method_is_equivalent_to_presentCollection_trait_method_on_paginator()
    {
        $roles = factory(Role::class, 3)->make();
        $paginator = (new Paginator)($roles);

        // Present the paginator using presentCollection
        $presentedWithTrait = $paginator->presentCollection(RolePresenter::class);

        // Present the paginator using the static pagination method
        $presentedWithStatic = RolePresenter::pagination($paginator);

        // Verify that both methods produce exactly the same result
        $this->assertEquals(
            $presentedWithStatic->toArray(),
            $presentedWithTrait->toArray()
        );

        // Verify that the data structure is identical
        $this->assertEquals(
            $presentedWithStatic['data']->toArray(),
            $presentedWithTrait['data']->toArray()
        );

        $this->assertEquals(
            $presentedWithStatic['links'],
            $presentedWithTrait['links']
        );

        $this->assertEquals(
            $presentedWithStatic['meta'],
            $presentedWithTrait['meta']
        );
    }

    /** @test */
    public function custom_presenter_attributes_are_preserved_in_both_pagination_methods()
    {
        $roles = factory(Role::class, 3)->make();
        $paginator = (new Paginator)($roles);

        // Create a custom presenter class
        $customPresenter = new class($roles->first()) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullTitle' => $this->fullTitle(),
                    'name' => $this->name,
                    'custom' => 'Custom Value'
                ];
            }
        };

        // Get the class name of the anonymous class
        $className = get_class($customPresenter);

        // Present the paginator using both methods
        $presentedWithTrait = $paginator->presentCollection($className);
        $presentedWithStatic = $className::pagination($paginator);

        // Verify that both methods preserve custom attributes
        $this->assertEquals(
            $presentedWithStatic['data'][0]->toArray(),
            $presentedWithTrait['data'][0]->toArray()
        );

        // Verify that custom attributes are present
        $this->assertEquals('Custom Value', $presentedWithTrait['data'][0]->toArray()['custom']);
        $this->assertEquals('Custom Value', $presentedWithStatic['data'][0]->toArray()['custom']);

        // Verify that the entire structure is identical
        $this->assertEquals(
            $presentedWithStatic->toArray(),
            $presentedWithTrait->toArray()
        );
    }

    /** @test */
    public function it_can_access_paginator_methods_after_presenting_with_presentCollection()
    {
        $roles = factory(Role::class, 3)->make();
        $paginator = (new Paginator)($roles);

        // Present the paginator using presentCollection
        $paginator->presentCollection(RolePresenter::class);

        // Verify we can still access the original paginator methods
        $this->assertEquals(1, $paginator->currentPage());
        $this->assertEquals(100, $paginator->lastPage());
        $this->assertEquals(10, $paginator->total());
    }
}
