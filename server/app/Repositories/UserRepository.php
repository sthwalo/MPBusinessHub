<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     * 
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user with hashed password
     * 
     * @param array $data
     * @return User
     */
    public function createUser(array $data)
    {
        // Password is automatically hashed via the User model's password mutator
        return $this->create($data);
    }
}
