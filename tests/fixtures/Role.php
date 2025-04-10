<?php

namespace CultureGr\Presenter\Tests\Fixtures;

use CultureGr\Presenter\Presentable;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use Presentable;
    
    protected $fillable = [
        'name', 'description'
    ];
    
    public function fullTitle()
    {
        return $this->name . ' - ' . $this->description;
    }
}
