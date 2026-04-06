<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    protected OrderService $orderService;
    protected EmailService $emailService;
    protected SMSService $smsService;
    protected InvoiceService $invoiceService;

    public function __construct(
        OrderService $orderService,
        EmailService $emailService,
        SMSService $smsService,
        InvoiceService $invoiceService
    ) {
        $this->orderService = $orderService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
        $this->invoiceService = $invoiceService;
    }

    // Need authentication middleware mapped in routes

    public function index(Request $request)
    {
        $orders = Order::with(['customer', 'items', 'statusHistory'])->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'items', 'statusHistory', 'payments', 'invoices', 'shipments', 'emailLogs', 'smsLogs'])->findOrFail($id);
        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $order = Order::findOrFail($id);
        $this->orderService->updateStatus($order, $request->input('status'), $request->input('notes'));

        return response()->json(['message' => 'Status updated successfully', 'order' => $order]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|string|in:unpaid,partial,paid,failed,refunded'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->input('payment_status')]);

        return response()->json(['message' => 'Payment status updated']);
    }

    public function addShipment(Request $request, $id)
    {
        $request->validate([
            'shipping_method' => 'required|string',
            'tracking_number' => 'nullable|string',
            'shipping_notes' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $this->orderService->addShipment($order, $request->all());

        return response()->json(['message' => 'Shipment added successfully', 'order' => $order]);
    }

    public function generateInvoice($id)
    {
        $order = Order::findOrFail($id);
        $invoice = $this->invoiceService->createInvoice($order);

        return response()->json(['message' => 'Invoice generated successfully', 'invoice' => $invoice]);
    }

    public function resendEmail($logId)
    {
        $this->emailService->resendEmail($logId);
        return response()->json(['message' => 'Email resend triggered']);
    }

    public function resendSms($logId)
    {
        $this->smsService->resendSms($logId);
        return response()->json(['message' => 'SMS resend triggered']);
    }
}
