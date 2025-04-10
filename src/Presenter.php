<?php

namespace CultureGr\Presenter;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\Paginator;
use JsonSerializable;


abstract class Presenter implements Arrayable, Jsonable, JsonSerializable, ArrayAccess
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Convert the Presenter to an array.
     *
     * This abstract method should be implemented by child classes to define
     * how the Presenter object is converted to an array representation.
     * The implementation should return an associative array containing
     * the presented data of the model.
     *
     * @return array The array representation of the Presenter.
     */
    abstract public function toArray(): array;

    /**
     * Create a new Presenter instance.
     *
     * @param Model $model
     * @return Presenter
     */
    public static function make(Model $model): Presenter
    {
        return new static($model);
    }

    /**
     * Create a collection of presented models.
     *
     * @param Collection $models
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
     * This method creates a structured collection containing the presented models
     * along with pagination metadata. It properly preserves any query parameters
     * that have been appended to the paginator using the appends() method.
     *
     * @param Paginator $paginator
     * @return Collection
     */
    public static function pagination(Paginator $paginator): Collection
    {
        return collect([
            'data' => static::collection(collect($paginator->items())),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => (int)$paginator->currentPage(),
                'from' => (int)$paginator->firstItem(),
                'last_page' => (int)$paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => (int)$paginator->perPage(),
                'to' => (int)$paginator->lastItem(),
                'total' => (int)$paginator->total(),
            ]
        ]);
    }

    /**
     * Property overloading.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->model->$name;
    }

    /**
     * Method overloading.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
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
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Determine if an offset exists in the Presenter.
     *
     * This method is part of the ArrayAccess interface implementation.
     * It checks if the given offset exists in the array representation of the Presenter.
     *
     * @param mixed $offset The offset to check for existence.
     * @return bool True if the offset exists, false otherwise.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    /**
     * Get the value at a given offset in the Presenter.
     *
     * This method is part of the ArrayAccess interface implementation.
     * It retrieves the value associated with the given offset from the array representation of the Presenter.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed The value at the specified offset.
     * @throws \Exception If the offset does not exist.
     */
    public function offsetGet($offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return $this->toArray()[$offset];
        }

        throw new \Exception("Undefined offset: $offset");
    }

    public function offsetSet($offset, $value): void
    {
        // Presenters are typically read-only, so this can be left empty or throw an exception
    }

    public function offsetUnset($offset): void
    {
        // Presenters are typically read-only, so this can be left empty or throw an exception
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * This method is part of the JsonSerializable interface implementation.
     * It returns the array representation of the Presenter, which will be
     * used when encoding the object to JSON.
     *
     * @return array The array to be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Check if a relationship has been eager-loaded.
     *
     * @param string $relationship
     * @return mixed|null
     */
    public function whenLoaded(string $relationship)
    {
        if (!$this->model->relationLoaded($relationship)) {
            return null;
        }

        return $this->model->$relationship;
    }
}
