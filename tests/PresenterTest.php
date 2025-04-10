<?php

namespace CultureGr\Presenter\Tests;

use CultureGr\Presenter\Presenter;
use CultureGr\Presenter\Tests\Fixtures\User;
use CultureGr\Presenter\Tests\Fixtures\Paginator;
use CultureGr\Presenter\Tests\Fixtures\UserPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PresenterTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $user = factory(User::class)->make();
        $presenter = $this->createPresenter($user);

        $this->assertInstanceOf(Presenter::class, $presenter);

    }

    /** @test */
    public function it_proxies_the_original_model_attributes()
    {
        $user = factory(User::class)->make();
        $presenter = $this->createPresenter($user);

        $this->assertSame($user->firstname, $presenter->firstname);
        $this->assertSame($user->lastname, $presenter->lastname);
        $this->assertSame($user->email, $presenter->email);
    }

    /** @test */
    public function it_proxies_the_original_model_methods()
    {
        $user = factory(User::class)->make();
        $presenter = $this->createPresenter($user);

        $this->assertSame($user->fullname(), $presenter->fullname());
    }

    /** @test */
    public function it_wraps_the_original_model()
    {
        $user = factory(User::class)->make();
        $presenter = $this->createPresenter($user);

        $this->assertSame($user, $presenter->getModel());
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting single model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_single_model()
    {
        $user = factory(User::class)->make();

        $presentedUser = UserPresenter::make($user);

        $this->assertInstanceOf(UserPresenter::class, $presentedUser);
    }

    /** @test */
    public function a_presented_model_can_be_serialized()
    {
        $user = factory(User::class)->make();

        $presentedUser = UserPresenter::make($user);

        $this->assertSame($user->toArray(), $presentedUser->toArray());
    }

    /** @test */
    public function it_automatically_converts_to_array_when_needed()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email,
                ];
            }
        };

        // Access the presenter as an array
        $presenterFullname = $presenter['fullname'];
        $presenterEmail = $presenter['email'];

        $this->assertEquals($user->fullname(), $presenterFullname);
        $this->assertEquals($user->email, $presenterEmail);
    }

    /** @test */
    public function it_automatically_converts_to_json_when_needed()
    {
        $user = factory(User::class)->make([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com'
        ]);

        $presenter = new class($user) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email,
                ];
            }
        };

        $jsonPresentation = json_encode($presenter);

        $this->assertJsonStringEqualsJsonString(
            '{"fullname":"John Doe","email":"john@example.com"}',
            $jsonPresentation
        );
    }

    /** @test */
    public function a_presented_model_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        };

        $this->assertSame([
            'fullname' => $user->fullname(),
            'email' => $user->email
        ], $presenter->toArray());
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting collections
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_collection_of_models()
    {
        $users = factory(User::class, 3)->make();

        $presentedUsers = UserPresenter::collection($users);

        $this->assertInstanceOf(Collection::class, $presentedUsers);
        $this->assertInstanceOf(UserPresenter::class, $presentedUsers->first());
    }

    /** @test */
    public function a_presented_collection_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();

        $presentedUsers = UserPresenter::collection($users);

        $this->assertSame($users->toArray(), $presentedUsers->toArray());
    }

    /** @test */
    public function a_presented_collection_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();

        // TODO: Make a UserCollectionPresenter fixture
        $presentedUsers = (new class($users->first()) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        })::collection($users);

        $this->assertSame([
            'fullname' => $users[0]->fullname(),
            'email' => $users[0]->email
        ], $presentedUsers->toArray()[0]);
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting paginated models
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_paginated_collection_of_models()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = UserPresenter::pagination($paginator);

        $this->assertInstanceOf(Collection::class, $presentedUsers);
        $this->assertInstanceOf(UserPresenter::class, $presentedUsers['data']->first());
    }

    /** @test */
    public function a_presented_paginated_collection_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = UserPresenter::pagination($paginator);

        $this->assertSame($users->toArray(), $presentedUsers->toArray()['data']);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        // TODO: Make a UserCollectionPresenter fixture
        $presentedUsers = (new class($users->first()) extends Presenter
        {
            public function toArray(): array
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        })::pagination($paginator);

        $this->assertSame([
            'fullname' => $users[0]->fullname(),
            'email' => $users[0]->email
        ], $presentedUsers->toArray()['data'][0]);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_links_keys()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = ($this->createPresenter($users->first()))::pagination($paginator);

        $this->assertSame([
            "first" => "http://example.com/pagination?page=1",
            "last" => "http://example.com/pagination?page=100",
            "prev" => null,
            "next" => 2
        ], $presentedUsers->toArray()['links']);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_meta_keys()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = ($this->createPresenter($users->first()))::pagination($paginator);

        $this->assertSame([
            'current_page' => 1,
            'from' => 1,
            'last_page' => 100,
            'path' => 'http://example.com/pagination',
            'per_page' => 15,
            'to' => 10,
            'total' => 10
        ], $presentedUsers->toArray()['meta']);
    }

    /** @test */
    public function it_preserves_appended_parameters_in_pagination_links()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        // Append parameters to the paginator
        $paginatorWithAppends = $paginator->appends(['foo' => 'bar']);

        // Present the paginator
        $presentedUsers = UserPresenter::pagination($paginatorWithAppends);

        // Get the links array
        $links = $presentedUsers->toArray()['links'];

        // Verify that all links include the appended parameters
        $this->assertStringContainsString('foo=bar', $links['first']);
        $this->assertStringContainsString('foo=bar', $links['last']);
        $this->assertStringContainsString('foo=bar', $links['next']);
    }

    /** @test */
    public function it_preserves_query_string_parameters_in_pagination_links()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        // Use withQueryString to add query parameters
        $paginatorWithQueryString = $paginator->withQueryString();

        // Present the paginator
        $presentedUsers = UserPresenter::pagination($paginatorWithQueryString);

        // Get the links array
        $links = $presentedUsers->toArray()['links'];

        // Verify that all links include the query string parameters
        $this->assertStringContainsString('query=test', $links['first']);
        $this->assertStringContainsString('query=test', $links['last']);
        $this->assertStringContainsString('query=test', $links['next']);
    }

    private function createPresenter(Model $model): Presenter
    {
        return new class($model) extends Presenter
        {
            public function toArray(): array
            {
                return $this->model->toArray();
            }
        };
    }
}
