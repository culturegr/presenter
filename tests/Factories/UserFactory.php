<?php

use Faker\Generator as Faker;
use CultureGr\Presenter\Tests\Fixtures\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'firstname' => $faker->name,
        'lastname' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt($faker->password)
    ];
});
