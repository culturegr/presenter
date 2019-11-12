<?php

namespace CultureGr\Presenter;

use Illuminate\Support\ServiceProvider;

class PresenterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([MakePresenterCommand::class]);
    }
}
