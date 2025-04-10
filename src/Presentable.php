<?php

namespace CultureGr\Presenter;

trait Presentable
{
    /**
     * Present the model using the given presenter class.
     *
     * @param string $presenterClass The presenter class to use
     * @return Presenter The presenter instance
     * @throws \InvalidArgumentException If the presenter class is not a valid Presenter
     */
    public function present(string $presenterClass): Presenter
    {
        if (!is_subclass_of($presenterClass, Presenter::class)) {
            throw new \InvalidArgumentException(
                "The presenter class must be a subclass of " . Presenter::class
            );
        }

        return $presenterClass::make($this);
    }

    /**
     * Note: This method is not directly implemented in the trait.
     * It's documented here for clarity, but the actual implementation
     * is provided through Laravel's Collection macros.
     *
     * Present a collection of models using the given presenter class.
     * This works for both regular collections and paginated collections.
     *
     * @param string $presenterClass The presenter class to use
     * @return \Illuminate\Support\Collection The collection of presenter instances
     * @throws \InvalidArgumentException If the presenter class is not a valid Presenter
     */
    // public function presentCollection(string $presenterClass): \Illuminate\Support\Collection
}
