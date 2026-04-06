<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Quotation;
use App\Models\QuotationNote;
use App\Services\QuoteRequestService;
use App\Services\QuotationService;
use App\Services\QuotationPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminQuoteController extends Controller
{
    public function __construct(
        private QuoteRequestService $requestService,
        private QuotationService $quotationService,
        private QuotationPdfService $pdfService,
    ) {}

    // ─────────────────────────────────────────
    // QUOTE REQUESTS
    // ─────────────────────────────────────────

    public function index(Request $request)
    {
        $query = QuoteRequest::with(['items', 'assignedTo', 'quotation'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate($request->input('per_page', 20));
        return response()->json($requests);
    }

    public function show(int $id)
    {
        $qr = QuoteRequest::with([
            'items.product',
            'items.productVariation',
            'customer',
            'assignedTo',
            'statusLogs.changedBy',
            'quotations.items',
            'quotations.whatsappLogs.sentBy',
            'quotations.emailLogs.sentBy',
            'quotations.statusLogs.changedBy',
        ])->findOrFail($id);

        return response()->json($qr);
    }

    public function updateStatus(Request $request, int $id)
    {
        $qr = QuoteRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', array_keys(QuoteRequest::$statuses)),
            'note'   => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->requestService->updateStatus(
            $qr,
            $request->input('status'),
            $request->user()->id,
            $request->input('note')
        );

        return response()->json(['message' => 'Status updated.', 'status' => $qr->fresh()->status]);
    }

    public function assignStaff(Request $request, int $id)
    {
        $qr = QuoteRequest::findOrFail($id);
        $request->validate(['user_id' => 'required|exists:users,id']);
        $qr->update(['assigned_to' => $request->input('user_id')]);
        return response()->json(['message' => 'Assigned.']);
    }

    public function updateAdminNotes(Request $request, int $id)
    {
        $qr = QuoteRequest::findOrFail($id);
        $request->validate(['admin_notes' => 'nullable|string|max:5000']);
        $qr->update(['admin_notes' => $request->input('admin_notes')]);
        return response()->json(['message' => 'Notes saved.']);
    }

    // ─────────────────────────────────────────
    // QUOTATIONS
    // ─────────────────────────────────────────

    public function createQuotation(Request $request, int $requestId)
    {
        $qr = QuoteRequest::findOrFail($requestId);

        $validator = Validator::make($request->all(), [
            'valid_until'       => 'nullable|date',
            'payment_terms'     => 'nullable|string',
            'delivery_details'  => 'nullable|string',
            'terms_conditions'  => 'nullable|string',
            'notes'             => 'nullable|string',
            'discount_amount'   => 'nullable|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'items'             => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:500',
            'items.*.sku'              => 'nullable|string|max:100',
            'items.*.product_id'       => 'nullable|integer|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.item_notes'       => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quotation = $this->quotationService->createFromRequest($qr, $validator->validated(), $request->user()->id);

        // Update quote request status
        $this->requestService->updateStatus($qr, 'quoted', $request->user()->id, 'Quotation created.');

        return response()->json($quotation->load('items'), 201);
    }

    public function updateQuotation(Request $request, int $quotationId)
    {
        $quotation = Quotation::findOrFail($quotationId);

        $validator = Validator::make($request->all(), [
            'valid_until'       => 'nullable|date',
            'payment_terms'     => 'nullable|string',
            'delivery_details'  => 'nullable|string',
            'terms_conditions'  => 'nullable|string',
            'notes'             => 'nullable|string',
            'discount_amount'   => 'nullable|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'items'             => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:500',
            'items.*.sku'              => 'nullable|string|max:100',
            'items.*.product_id'       => 'nullable|integer|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.item_notes'       => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quotation = $this->quotationService->updateQuotation($quotation, $validator->validated(), $request->user()->id);

        return response()->json($quotation->load('items'));
    }

    public function sendWhatsApp(Request $request, int $quotationId)
    {
        $quotation = Quotation::with(['items', 'quoteRequest'])->findOrFail($quotationId);

        // Prevent accidental duplicate sends
        $lastSent = $quotation->whatsappLogs()->where('success', true)->latest('sent_at')->first();
        if ($lastSent && $lastSent->sent_at->diffInMinutes(now()) < 5 && !$request->boolean('force')) {
            return response()->json([
                'success' => false,
                'message' => 'This quotation was already sent within the last 5 minutes. Pass force=true to resend.',
            ], 409);
        }

        $result = $this->quotationService->sendViaWhatsApp($quotation, $request->user()->id);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function generatePdf(int $quotationId)
    {
        $quotation = Quotation::with(['items', 'quoteRequest'])->findOrFail($quotationId);
        $path      = $this->pdfService->generate($quotation);

        return response()->json([
            'message'  => 'Document generated.',
            'pdf_url'  => $this->pdfService->getPublicUrl($quotation),
            'pdf_path' => $path,
        ]);
    }

    public function addNote(Request $request, int $quotationId)
    {
        $quotation = Quotation::findOrFail($quotationId);
        $request->validate([
            'note'        => 'required|string|max:2000',
            'is_internal' => 'nullable|boolean',
        ]);

        $note = QuotationNote::create([
            'quotation_id' => $quotation->id,
            'created_by'   => $request->user()->id,
            'note'         => $request->input('note'),
            'is_internal'  => $request->boolean('is_internal', true),
        ]);

        return response()->json($note->load('createdBy'), 201);
    }

    public function updateQuotationStatus(Request $request, int $quotationId)
    {
        $quotation = Quotation::findOrFail($quotationId);
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Quotation::$statuses)),
            'note'   => 'nullable|string|max:500',
        ]);

        $this->quotationService->updateStatus(
            $quotation,
            $request->input('status'),
            $request->user()->id,
            $request->input('note')
        );

        return response()->json(['message' => 'Status updated.', 'status' => $quotation->fresh()->status]);
    }

    public function statuses()
    {
        return response()->json([
            'request_statuses'   => QuoteRequest::$statuses,
            'quotation_statuses' => Quotation::$statuses,
        ]);
    }
}
