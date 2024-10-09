<?php

namespace CultureGr\Presenter\Tests\Fixtures;

use CultureGr\Presenter\Presenter;

class UserPresenter extends Presenter
{
    public function toArray(): array
    {
        return $this->model->toArray();
    }
}
