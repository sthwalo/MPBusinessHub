<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all resources
     * 
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * Find resource by id
     * 
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Find resource by specific column & value
     * 
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return mixed
     */
    public function findByField(string $field, $value, array $columns = ['*']);

    /**
     * Create new resource
     * 
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update resource
     * 
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id);

    /**
     * Delete resource
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id);
}
