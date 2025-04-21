<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\BaseRepositoryInterface;

/**
 * Class BaseRepository
 *
 * A generic repository for handling Eloquent model operations dynamically.
 *
 * @template T of Model
 */
abstract class BaseRepository
{
    /**
     * The model class name.
     *
     * @var class-string<T>
     */
    protected string $modelClass;

    /**
     * BaseRepository constructor.
     *
     * @param class-string<T> $modelClass The model class name to instantiate dynamically.
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }


    /**
     * Instantiate the repository statically.
     *
     * @return static
     */
    protected abstract static function instantiate(): static;

    /**
     * Handle dynamic static method calls and forward them to the instance.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = static::instantiate();
        return $instance->__call($method, $arguments);
    }

    /**
     * Handle dynamic method calls on the instance.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        $query = $this->query();
        if (method_exists($query, $method)) {
            return $query->$method(...$arguments);
        }

        if (method_exists($this->modelClass, $method)) {
            return $this->modelClass::$method(...$arguments);
        }

        throw new \BadMethodCallException("Method {$method} does not exist in " . static::class . " or its model.");
    }

    /**
     * Get a query builder instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder<T>
     */
    protected function query()
    {
        return $this->modelClass::query();
    }

    /**
     * Retrieve all records.
     *
     * @return \Illuminate\Database\Eloquent\Collection<T>
     */
    protected function all(): mixed
    {
        return $this->query()->get();
    }

    /**
     * Find a record by ID.
     *
     * @param int|string $id
     * @return T
     */
    protected function find($id): mixed
    {
        return $this->query()->find($id);
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return T
     */
    protected function create(array $data): mixed
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update a record by ID.
     *
     * @param int|string $id
     * @param array $data
     * @return T
     *
     * @throws ModelNotFoundException
     */
    protected function update($id, array $data): mixed
    {
        return $this->query()->where($this->query()->getModel()->getKeyName(), $id)->update($data);
    }

    /**
     * Delete a record by ID.
     *
     * @param int|string $id
     * @return bool|null
     *
     * @throws ModelNotFoundException
     */
    protected function delete($id): mixed
    {
        return $this->query()->where($this->query()->getModel()->getKeyName(), $id)->delete();
    }
}
