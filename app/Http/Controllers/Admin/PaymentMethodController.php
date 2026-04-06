<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayhereSettings;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PaymentMethodController extends Controller
{
    public function __construct(private PaymentMethodService $service) {}

    // ─── Payment Methods CRUD ────────────────────────────────────────────────

    public function index()
    {
        $methods  = PaymentMethod::orderBy('sort_order')->get();
        $settings = PayhereSettings::instance();

        return view('admin.payments.index', compact('methods', 'settings'));
    }

    public function create()
    {
        return view('admin.payments.method-form', ['method' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:payment_methods,code',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type'        => 'required|in:offline,online',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        PaymentMethod::create($data);
        $this->service->clearCache();

        return redirect()->route('admin.payments.index')->with('success', 'Payment method created.');
    }

    public function edit(PaymentMethod $payment)
    {
        return view('admin.payments.method-form', ['method' => $payment]);
    }

    public function update(Request $request, PaymentMethod $payment)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type'        => 'required|in:offline,online',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $payment->update($data);
        $this->service->clearCache();

        return redirect()->route('admin.payments.index')->with('success', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $payment)
    {
        // Protect built-in methods
        if (in_array($payment->code, ['cod', 'payhere'])) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Cannot delete default payment methods.');
        }

        $payment->delete();
        $this->service->clearCache();

        return redirect()->route('admin.payments.index')->with('success', 'Payment method deleted.');
    }

    // ─── PayHere Settings ───────────────────────────────────────────────────

    public function updatePayhereSettings(Request $request)
    {
        $request->validate([
            'merchant_id_live'       => 'nullable|string|max:100',
            'merchant_secret_live'   => 'nullable|string|max:500',
            'merchant_id_sandbox'    => 'nullable|string|max:100',
            'merchant_secret_sandbox'=> 'nullable|string|max:500',
            'mode'                   => 'required|in:sandbox,live',
        ]);

        $settings = PayhereSettings::instance();

        $settings->merchant_id_live    = $request->merchant_id_live;
        $settings->merchant_id_sandbox = $request->merchant_id_sandbox;
        $settings->mode                = $request->mode;

        // Only update secrets if provided (non-empty means admin is setting a new value)
        if ($request->filled('merchant_secret_live')) {
            $settings->merchant_secret_live = Crypt::encryptString($request->merchant_secret_live);
        }
        if ($request->filled('merchant_secret_sandbox')) {
            $settings->merchant_secret_sandbox = Crypt::encryptString($request->merchant_secret_sandbox);
        }

        $settings->save();

        return redirect()->route('admin.payments.index')->with('success', 'PayHere settings saved.');
    }

    /**
     * Quick toggle: enable / disable a payment method via AJAX-friendly POST.
     */
    public function toggle(PaymentMethod $payment)
    {
        $payment->update(['is_active' => ! $payment->is_active]);
        $this->service->clearCache();

        return redirect()->route('admin.payments.index')
            ->with('success', "Payment method {$payment->name} " . ($payment->is_active ? 'enabled' : 'disabled') . '.');
    }
}
