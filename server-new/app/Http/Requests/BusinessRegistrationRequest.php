<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Public registration is allowed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Business Information
            'businessName' => 'required|string|max:255',
            'category' => 'required|string|in:Tourism,Agriculture,Construction,Events',
            'district' => 'required|string|in:Mbombela,Emalahleni,Bushbuckridge',
            'description' => 'required|string|min:50|max:500',
            
            // Contact Information
            'phone' => 'required|string|regex:/^\+?[0-9\s-]{10,15}$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'website' => 'nullable|string|url',
            'address' => 'required|string|max:255',
            
            // Account Information
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'businessName.required' => 'Business name is required',
            'category.required' => 'Please select a category',
            'category.in' => 'Please select a valid category',
            'district.required' => 'Please select a district',
            'district.in' => 'Please select a valid district',
            'description.required' => 'Business description is required',
            'description.min' => 'Description should be at least 50 characters',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Please enter a valid phone number',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered. Please use a different email or login.',
            'website.url' => 'Please enter a valid website URL',
            'address.required' => 'Business address is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
}
