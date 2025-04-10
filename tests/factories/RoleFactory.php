<?php

use Faker\Generator as Faker;
use CultureGr\Presenter\Tests\Fixtures\Role;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentence
    ];
});
