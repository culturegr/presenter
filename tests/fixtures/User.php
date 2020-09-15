<?php

namespace CultureGr\Presenter\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password'
    ];

    protected $hidden = [
        'password'
    ];

    public function fullname()
    {
        return $this->firstname.' '.$this->lastname;
    }
}
