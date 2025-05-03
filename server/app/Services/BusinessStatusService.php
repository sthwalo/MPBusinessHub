<?php

namespace App\Services;

use App\Models\Business;
use App\Notifications\BusinessStatusChanged;
use Illuminate\Support\Facades\Log;

class BusinessStatusService
{
    /**
     * Available business statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    
    /**
     * Get all possible business statuses
     * 
     * @return array
     */
    public function getAllStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED
        ];
    }
    
    /**
     * Update the status of a business
     * 
     * @param Business $business
     * @param string $newStatus
     * @param string|null $reason
     * @return Business
     * @throws \InvalidArgumentException
     */
    public function updateStatus(Business $business, string $newStatus, ?string $reason = null): Business
    {
        // Validate the status
        if (!in_array($newStatus, $this->getAllStatuses())) {
            throw new \InvalidArgumentException("Invalid business status: {$newStatus}");
        }
        
        // Check if status is actually changing
        if ($business->status === $newStatus) {
            return $business;
        }
        
        $oldStatus = $business->status;
        
        // Update the business status
        $business->status = $newStatus;
        $business->status_changed_at = now();
        $business->status_reason = $reason;
        $business->save();
        
        // Log the status change
        Log::info("Business ID {$business->id} status changed from {$oldStatus} to {$newStatus}");
        
        // Notify the business owner
        $business->user->notify(new BusinessStatusChanged($business, $oldStatus, $newStatus, $reason));
        
        return $business;
    }
    
    /**
     * Approve a business
     * 
     * @param Business $business
     * @param string|null $reason
     * @return Business
     */
    public function approveBusiness(Business $business, ?string $reason = null): Business
    {
        return $this->updateStatus($business, self::STATUS_APPROVED, $reason);
    }
    
    /**
     * Reject a business
     * 
     * @param Business $business
     * @param string|null $reason
     * @return Business
     */
    public function rejectBusiness(Business $business, ?string $reason = null): Business
    {
        return $this->updateStatus($business, self::STATUS_REJECTED, $reason);
    }
    
    /**
     * Set a business back to pending
     * 
     * @param Business $business
     * @param string|null $reason
     * @return Business
     */
    public function setPending(Business $business, ?string $reason = null): Business
    {
        return $this->updateStatus($business, self::STATUS_PENDING, $reason);
    }
}