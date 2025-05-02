<?php

namespace App\Repositories;

use App\Models\Business;

class BusinessRepository extends BaseRepository
{
    /**
     * BusinessRepository constructor.
     * 
     * @param Business $model
     */
    public function __construct(Business $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a business by user ID
     * 
     * @param int $userId
     * @return Business|null
     */
    public function findByUserId(int $userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Create a new business with associated user ID
     * 
     * @param array $data
     * @param int $userId
     * @return Business
     */
    public function createBusiness(array $data, int $userId)
    {
        $businessData = array_merge($data, [
            'user_id' => $userId,
            'status' => 'pending' // Default status for new businesses
        ]);
        
        return $this->create($businessData);
    }
}
