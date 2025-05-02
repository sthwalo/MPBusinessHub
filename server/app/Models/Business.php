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
        'user_id',
        'status', // pending, approved, rejected
        'review_count',
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
}