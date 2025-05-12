@extends('emails.layout')

@section('content')
<div class="email-container">
    <div class="header">
        <h1>Payment Successful</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $user->name }},</p>
        
        <p>Your payment for <strong>{{ $business->name }}</strong> has been successfully processed.</p>
        
        <div class="payment-details">
            <h3>Payment Details</h3>
            <table>
                <tr>
                    <td><strong>Invoice Number:</strong></td>
                    <td>#{{ $invoice->id }}</td>
                </tr>
                <tr>
                    <td><strong>Package:</strong></td>
                    <td>{{ $package->name }}</td>
                </tr>
                <tr>
                    <td><strong>Amount:</strong></td>
                    <td>{{ $currency }} {{ number_format($amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>{{ $date }}</td>
                </tr>
            </table>
        </div>
        
        <div class="package-features">
            <h3>Your {{ $package->name }} Package Includes:</h3>
            <ul>
                @if($package->product_limit)
                    <li>{{ $package->product_limit }} products</li>
                @endif
                
                @if($package->social_media_limit)
                    <li>{{ $package->social_media_limit }} social media accounts</li>
                @endif
                
                @if($package->has_analytics)
                    <li>Business analytics</li>
                @endif
                
                @if($package->has_premium_support)
                    <li>Premium support</li>
                @endif
            </ul>
        </div>
        
        <p>Thank you for choosing {{ $app_name }}. If you have any questions, please contact our support team at <a href="mailto:{{ $support_email }}">{{ $support_email }}</a>.</p>
        
        <div class="cta-button">
            <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $app_name }}. All rights reserved.</p>
        <p>Mpumalanga Business Hub, South Africa</p>
    </div>
</div>
@endsection
