<?php

namespace App\Services;

use App\Models\Business;
use App\Models\OperatingHour;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BusinessProfileService
{
    /**
     * Update a business profile
     *
     * @param Business $business
     * @param array $data
     * @return array
     */
    public function updateProfile(Business $business, array $data)
    {
        try {
            DB::beginTransaction();
            
            // Update basic business information
            $business->name = $data['name'] ?? $business->name;
            $business->category = $data['category'] ?? $business->category;
            $business->district = $data['district'] ?? $business->district;
            $business->description = $data['description'] ?? $business->description;
            $business->phone = $data['phone'] ?? $business->phone;
            $business->website = $data['website'] ?? $business->website;
            $business->address = $data['address'] ?? $business->address;
            
            // Update social media links if provided
            if (isset($data['social_media'])) {
                $business->social_media = $data['social_media'];
            }
            
            // Save the business
            $business->save();
            
            // Update operating hours if provided
            if (isset($data['operating_hours'])) {
                $this->updateOperatingHours($business, $data['operating_hours']);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Business profile updated successfully',
                'business' => $business
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating business profile: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while updating the business profile: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update operating hours for a business
     *
     * @param Business $business
     * @param array $operatingHours
     * @return void
     */
    protected function updateOperatingHours(Business $business, array $operatingHours)
    {
        // Delete existing operating hours
        OperatingHour::where('business_id', $business->id)->delete();
        
        // Create new operating hours
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($daysOfWeek as $day) {
            if (isset($operatingHours[$day])) {
                $hours = $operatingHours[$day];
                
                // Check if the business is closed on this day
                $isClosed = $hours === 'Closed' || empty($hours);
                
                // Parse opening and closing times if not closed
                $openingTime = null;
                $closingTime = null;
                
                if (!$isClosed && strpos($hours, ' - ') !== false) {
                    list($openingTime, $closingTime) = explode(' - ', $hours);
                }
                
                // Create the operating hour record
                OperatingHour::create([
                    'business_id' => $business->id,
                    'day_of_week' => $day,
                    'is_closed' => $isClosed,
                    'opening_time' => $openingTime,
                    'closing_time' => $closingTime
                ]);
            }
        }
    }
    
    /**
     * Validate business profile data
     *
     * @param array $data
     * @return array
     */
    public function validateProfileData(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'district' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'website' => 'sometimes|nullable|url|max:255',
            'address' => 'sometimes|required|string|max:255',
            'social_media' => 'sometimes|nullable|array',
            'social_media.facebook' => 'sometimes|nullable|url',
            'social_media.twitter' => 'sometimes|nullable|url',
            'social_media.instagram' => 'sometimes|nullable|url',
            'social_media.linkedin' => 'sometimes|nullable|url',
            'operating_hours' => 'sometimes|nullable|array',
            'operating_hours.*' => 'sometimes|nullable|string'
        ]);
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray()
            ];
        }
        
        return [
            'success' => true,
            'data' => $validator->validated()
        ];
    }
}
