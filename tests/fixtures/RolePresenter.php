<?php

namespace CultureGr\Presenter\Tests\Fixtures;

use CultureGr\Presenter\Presenter;

class RolePresenter extends Presenter
{
    public function toArray(): array
    {
        return $this->model->toArray();
    }
}
