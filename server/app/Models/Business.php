<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'district',
        'address',
        'phone',
        'website',
        'image_url',
        'user_id',
        'status', // pending, approved, rejected
        'review_count',
        'package_type',
        'adverts_remaining',
        'social_media',
        'social_features_remaining',
    ];
    protected $casts = [
        'social_media' => 'array',
        'social_features_remaining' => 'integer',
    ];
    /**
     * Get the user that owns the business.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the operating hours for the business.
     */
    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class);
    }

    /**
     * Get the reviews for the business.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Get the adverts for the business.
     */
    public function adverts(): HasMany
    {
        return $this->hasMany(Advert::class);
    }
    
    /**
     * Get the products for the business.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    /**
     * Get the package that the business is subscribed to.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
