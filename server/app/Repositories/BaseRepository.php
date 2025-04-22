<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * 
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * @inheritDoc
     */
    public function findByField(string $field, $value, array $columns = ['*'])
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(array $data, int $id)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
