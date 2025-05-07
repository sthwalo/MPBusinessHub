<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the invoices for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $invoices = $user->invoices()
            ->with(['business', 'payment'])
            ->latest()
            ->paginate(10);

        return response()->json($invoices);
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['business', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($invoice);
    }

    /**
     * Download invoice as PDF
     */
    public function download($id)
    {
        $invoice = Invoice::with(['business', 'payment', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $data = [
            'invoice' => $invoice,
            'business' => $invoice->business,
            'user' => $invoice->user,
        ];

        $pdf = PDF::loadView('invoices.pdf', $data);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Send invoice via email
     */
    public function send($id)
    {
        $invoice = Invoice::where('user_id', Auth::id())
            ->findOrFail($id);

        //Implement email sending logic
        $this->invoiceService->sendInvoice($invoice);

        return response()->json(['message' => 'Invoice sent successfully']);
    }
}