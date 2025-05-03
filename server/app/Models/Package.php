<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_monthly',
        'price_annual',
        'advert_limit',
        'product_limit',
        'features',
        'is_active'
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'advert_limit' => 'integer',
        'product_limit' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the businesses with this package.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}