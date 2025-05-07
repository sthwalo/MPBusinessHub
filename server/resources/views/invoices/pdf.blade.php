<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-info { margin-bottom: 30px; }
        .invoice-info div { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; font-size: 1.2em; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <h2>#{{ $invoice->invoice_number }}</h2>
    </div>

    <div class="invoice-info">
        <div><strong>Date:</strong> {{ $invoice->issue_date->format('F d, Y') }}</div>
        <div><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</div>
        <div><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
    </div>

    <div>
        <h3>Bill To:</h3>
        <p>
            {{ $business->name }}<br>
            {{ $user->name }}<br>
            {{ $business->email }}<br>
            {{ $business->phone }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->package }} Package Subscription</td>
                <td>R{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <td>VAT (15%)</td>
                <td>R{{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td>R{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
</body>
</html>