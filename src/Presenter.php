<?php

namespace CultureGr\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\Paginator;

abstract class Presenter implements Arrayable, Jsonable
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new Presenter instance.
     *
     * @param  Model  $model
     * @return Presenter
     */
    public static function make(Model $model): Presenter
    {
        return new static($model);
    }

    /**
     * Create a collection of presented models.
     *
     * @param  Collection  $models
     * @return Collection
     */
    public static function collection(Collection $models): Collection
    {
        return collect($models)->map(function ($model) {
            return new static($model);
        });
    }

    /**
     * Create a collection of paginated presented models.
     *
     * @param  Paginator  $paginator
     * @return Collection
     */
    public static function pagination(Paginator $paginator): Collection
    {
        return collect([
            'data' => static::collection(collect($paginator->items())),
            'links' => [
                'first' => $paginator->path().'?page=1',
                'last' => $paginator->path().'?page='.$paginator->lastPage(),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => (int) $paginator->currentPage(),
                'from' => (int) $paginator->firstItem(),
                'last_page' => (int) $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => (int) $paginator->perPage(),
                'to' => (int) $paginator->lastItem(),
                'total' => (int) $paginator->total(),
            ]
        ]);
    }

    /**
     * Property overloading.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->model->$name;
    }

    /**
     * Method overloading.
     *
     * @param  string  $method
     * @param  array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Convert the Presenter to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->model->toArray();
    }

    /**
     * Convert the Presenter to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the Presenter to a JSON string.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Check if a relationship has been eager-loaded.
     *
     * @param  string  $relationship
     * @return mixed|null
     */
    protected function whenLoaded(string $relationship)
    {
        if (!$this->model->relationLoaded($relationship)) {
            return null;
        }

        return $this->model->$relationship;
    }
}
