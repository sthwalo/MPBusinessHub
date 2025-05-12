@extends('emails.layout')

@section('content')
<div class="email-container">
    <div class="header">
        <h1>Payment Failed</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $user->name }},</p>
        
        <p>We regret to inform you that your payment for <strong>{{ $business->name }}</strong> could not be processed successfully.</p>
        
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
                <tr>
                    <td><strong>Error:</strong></td>
                    <td>{{ $error_message }}</td>
                </tr>
            </table>
        </div>
        
        <div class="error-message">
            <h3>What Happened?</h3>
            <p>Your payment could not be processed due to the following reason:</p>
            <p class="error">{{ $error_message }}</p>
        </div>
        
        <div class="next-steps">
            <h3>What to Do Next</h3>
            <ul>
                <li>Check that your payment details are correct</li>
                <li>Ensure you have sufficient funds in your account</li>
                <li>Try again with a different payment method</li>
                <li>Contact your bank if the problem persists</li>
            </ul>
        </div>
        
        <p>If you need assistance, please contact our support team at <a href="mailto:{{ $support_email }}">{{ $support_email }}</a>.</p>
        
        <div class="cta-button">
            <a href="{{ url('/dashboard/upgrade-plan') }}" class="button">Try Again</a>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $app_name }}. All rights reserved.</p>
        <p>Mpumalanga Business Hub, South Africa</p>
    </div>
</div>
@endsection
