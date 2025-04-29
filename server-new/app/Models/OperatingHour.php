<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatingHour extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'day_of_week',
        'opening_time',
        'closing_time',
        'is_closed',
    ];

    /**
     * Get the business that owns these operating hours.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}