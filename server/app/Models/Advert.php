<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advert extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'adverts_remaining',
        'last_adverts_reset',
        'package_type'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_adverts_reset' => 'date',
    ];

    /**
     * Get the business that owns the advert.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}