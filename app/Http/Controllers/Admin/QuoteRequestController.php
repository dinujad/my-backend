<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Services\QuoteRequestService;
use App\Services\QuotationService;
use App\Services\QuotationPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteRequestController extends Controller
{
    public function __construct(
        private QuoteRequestService $requestService,
        private QuotationService $quotationService,
        private QuotationPdfService $pdfService,
    ) {}

    public function index(Request $request): View
    {
        $query = QuoteRequest::with(['items', 'assignedTo', 'quotations'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $quoteRequests = $query->paginate(15)->withQueryString();

        return view('admin.quote-requests.index', compact('quoteRequests'));
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        $quoteRequest->load([
            'items.product',
            'items.productVariation',
            'assignedTo',
            'statusLogs.changedBy',
            'quotations.items',
            'quotations.whatsappLogs.sentBy',
        ]);

        return view('admin.quote-requests.show', compact('quoteRequest'));
    }

    public function updateStatus(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(QuoteRequest::$statuses)),
            'note'   => 'nullable|string|max:1000',
        ]);

        $this->requestService->updateStatus($quoteRequest, $data['status'], $request->user()->id, $data['note'] ?? null);

        return back()->with('success', 'Quote request status updated.');
    }

    public function updateNotes(Request $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $quoteRequest->update(['admin_notes' => $data['admin_notes'] ?? null]);

        return back()->with('success', 'Admin notes saved.');
    }

    /**
     * AJAX: create or update quotation (called from Blade Alpine JS — uses web session auth).
     */
    public function saveQuotation(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        $data = $request->validate([
            'valid_until'       => 'nullable|date',
            'payment_terms'     => 'nullable|string',
            'delivery_details'  => 'nullable|string',
            'terms_conditions'  => 'nullable|string',
            'notes'             => 'nullable|string',
            'discount_amount'   => 'nullable|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'items'             => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:500',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.item_notes'       => 'nullable|string|max:500',
        ]);

        $existing = $quoteRequest->quotations()->latest()->first();

        if ($existing) {
            $quotation = $this->quotationService->updateQuotation($existing, $data, $request->user()->id);
        } else {
            $quotation = $this->quotationService->createFromRequest($quoteRequest, $data, $request->user()->id);
            $this->requestService->updateStatus($quoteRequest, 'quoted', $request->user()->id, 'Quotation created.');
        }

        return response()->json($quotation->load('items'));
    }

    /**
     * AJAX: send quotation via WhatsApp (uses web session auth).
     */
    public function sendWhatsApp(Request $request, QuoteRequest $quoteRequest): JsonResponse
    {
        $quotation = $quoteRequest->quotations()->latest()->first();

        if (!$quotation) {
            return response()->json(['success' => false, 'message' => 'No quotation found. Please create one first.'], 404);
        }

        $lastSent = $quotation->whatsappLogs()->where('success', true)->latest('sent_at')->first();
        if ($lastSent && $lastSent->sent_at->diffInMinutes(now()) < 5 && !$request->boolean('force')) {
            return response()->json([
                'success' => false,
                'duplicate' => true,
                'message' => 'Already sent recently. Confirm force resend?',
            ], 409);
        }

        $result = $this->quotationService->sendViaWhatsApp($quotation, $request->user()->id);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Download quotation as printable HTML/PDF.
     */
    public function downloadPdf(QuoteRequest $quoteRequest): \Symfony\Component\HttpFoundation\Response
    {
        $quotation = $quoteRequest->quotations()->with('items')->latest()->first();

        if (!$quotation) {
            return back()->with('error', 'No quotation found.');
        }

        $path = $this->pdfService->generate($quotation);
        $fullPath = storage_path('app/public/' . $path);

        return response()->download($fullPath, 'Quotation-' . $quotation->quote_number . '.html');
    }
}
