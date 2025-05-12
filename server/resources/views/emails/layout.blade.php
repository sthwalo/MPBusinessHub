<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title : config('app.name') }}</title>
    <style>
        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Header */
        .header {
            background-color: #4A6FDC;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        /* Content */
        .content {
            padding: 30px;
        }
        
        /* Payment details */
        .payment-details, .package-features, .error-message, .next-steps {
            background-color: #f5f7fa;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        
        .payment-details h3, .package-features h3, .error-message h3, .next-steps h3 {
            margin-top: 0;
            color: #4A6FDC;
            font-size: 18px;
        }
        
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .payment-details table td {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .payment-details table tr:last-child td {
            border-bottom: none;
        }
        
        /* Package features */
        .package-features ul {
            padding-left: 20px;
            margin: 10px 0;
        }
        
        .package-features li {
            margin-bottom: 8px;
        }
        
        /* Error message */
        .error-message .error {
            color: #e53935;
            font-weight: 600;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #e53935;
        }
        
        /* CTA Button */
        .cta-button {
            text-align: center;
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            background-color: #4A6FDC;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
        }
        
        /* Footer */
        .footer {
            background-color: #f5f7fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div>
        @yield('content')
    </div>
</body>
</html>
