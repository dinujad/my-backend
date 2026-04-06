<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Customer;
use App\Models\Address;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class CheckoutService
{
    /**
     * Process checkout form (merged logic).
     * 
     * @param Request $request
     * @return Order
     * @throws Exception
     */
    public function processCheckout(Request $request): Order
    {
        return DB::transaction(function () use ($request) {
            $user = null;
            $customer = null;

            $items = json_decode($request->input('items'), true);
            if (!is_array($items) || empty($items)) {
                throw new Exception("Invalid items payload");
            }

            // 1. Handle "Register while Checkout"
            if ($request->boolean('register')) {
                $user = User::firstOrCreate(
                    ['email' => $request->input('customer_email')],
                    [
                        'name' => $request->input('customer_name') ?? $request->input('customer_first_name') . ' ' . $request->input('customer_last_name'),
                        'password' => Hash::make($request->input('password')),
                        'role' => 'customer',
                    ]
                );

                // Auto login session (if using Sanctum/web guard, this handles web; for API, might need token)
                Auth::login($user);
            } else {
                // If logged in already, get user
                $user = Auth::guard('sanctum')->user() ?? Auth::user();
            }

            // Name handling (since frontend might send customer_name or first/last names)
            $firstName = $request->input('customer_first_name') ?? explode(' ', $request->input('customer_name'))[0] ?? '';
            $lastName = $request->input('customer_last_name') ?? explode(' ', $request->input('customer_name'), 2)[1] ?? '';
            $fullName = trim($firstName . ' ' . $lastName) ?: $request->input('customer_name');

            // 2. Create or Update Customer Profile
            $customer = Customer::firstOrCreate(
                ['email' => $request->input('customer_email')],
                [
                    'name' => $fullName,
                    'phone' => $request->input('customer_phone') ?? null,
                    'user_id' => $user ? $user->id : null,
                ]
            );

            if ($user && !$customer->user_id) {
                $customer->user_id = $user->id;
                $customer->save();
            }

            // 3. Save Address
            $addressData = [
                'type' => 'shipping',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->input('customer_email'),
                'phone' => $request->input('customer_phone') ?? null,
                'line1' => $request->input('customer_address') ?? $request->input('customer_address_line1') ?? '',
                'line2' => $request->input('customer_address_line2') ?? null,
                'city' => $request->input('customer_city') ?? $request->input('customer_district') ?? '',
                'district' => $request->input('customer_district') ?? null,
                'postal_code' => $request->input('customer_postal_code') ?? null,
                'country' => $request->input('customer_country') ?? 'Sri Lanka',
                'customer_id' => $customer->id,
                'user_id' => $user ? $user->id : null,
            ];
            
            $address = Address::create($addressData);

            // 4. Create Order with 0 totals initially
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => 0,
                'total' => 0,
                'currency' => 'LKR',
                'notes' => $request->input('notes'),
                'shipping_address' => collect($addressData)->except(['customer_id', 'user_id', 'is_default', 'type'])->toArray(),
            ]);

            $subtotal = 0;

            // 5. Create Order Items (Preserving previous logic)
            foreach ($items as $index => $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $unitPrice = (float) $product->price;
                $variationId = $itemData['product_variation_id'] ?? $itemData['variation_id'] ?? null;
                if ($variationId) {
                    $variation = ProductVariation::query()
                        ->where('id', $variationId)
                        ->where('product_id', $product->id)
                        ->first();
                    if ($variation) {
                        $sale = $variation?->sale_price;
                        $unitPrice = (float) (($sale !== null && $sale !== '' && (float) $sale > 0)
                            ? $sale
                            : $variation?->price);
                    }
                }

                $customizationFee = floatval($itemData['customization_fee'] ?? 0);
                $qty = intval($itemData['quantity'] ?? 1);
                $totalItemPrice = ($unitPrice + $customizationFee) * $qty;
                $subtotal += $totalItemPrice;

                $customizations = is_array($itemData['customizations'] ?? null)
                    ? $itemData['customizations']
                    : [];

                foreach ($request->allFiles() as $fileKey => $uploadedFile) {
                    if (str_starts_with($fileKey, "item_{$index}_file_")) {
                        $fieldLabel = str_replace("item_{$index}_file_", '', $fileKey);
                        $path = $uploadedFile->store('orders', 'public');
                        $customizations[$fieldLabel] = '/storage/' . ltrim($path, '/');
                    }
                }

                $lineName = $itemData['product_name'] ?? $product->name;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variation_id' => $variationId ?? null,
                    'product_name' => $lineName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'customization_fee' => $customizationFee,
                    'total_price' => $totalItemPrice,
                    'customizations' => ! empty($customizations) ? $customizations : null,
                ]);
            }

            // Resolve shipping cost from district + method
            $shippingCost = 0.0;
            $district = $request->input('customer_district');
            $shippingMethodId = (int) ($request->input('shipping_method_id') ?? 0);

            if ($district && $shippingMethodId) {
                $shippingCost = app(ShippingService::class)
                    ->resolveMethodPrice($shippingMethodId, $district, $subtotal);
            } elseif ($request->input('shipping_cost') !== null) {
                // Fallback: explicit shipping_cost sent from frontend
                $shippingCost = (float) $request->input('shipping_cost');
            }

            $discount = (float) ($request->input('discount_amount') ?? 0);
            $total = $subtotal + $shippingCost - $discount;

            $shippingMethodName = null;
            if ($shippingMethodId) {
                $shippingMethodName = \App\Models\ShippingMethod::find($shippingMethodId)?->name;
            }

            $order->update([
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'shipping_method' => $shippingMethodName ?? $request->input('shipping_method'),
                'shipping_district' => $district,
                'discount_amount' => $discount,
                'total' => $total,
            ]);

            // 6. Update Customer Stats
            $customer->increment('total_orders');
            $customer->total_spent += $total;
            $customer->save();

            // 8. Resolve payment method and create initial Payment record for COD
            $paymentMethodCode = $request->input('payment_method', 'cod');
            $paymentMethod = \App\Models\PaymentMethod::where('code', $paymentMethodCode)->first();

            if ($paymentMethodCode === 'cod') {
                \App\Models\Payment::create([
                    'order_id'          => $order->id,
                    'payment_method'    => 'cod',
                    'payment_method_id' => $paymentMethod?->id,
                    'amount'            => $total,
                    'currency'          => 'LKR',
                    'status'            => 'pending',
                    'payment_details'   => 'Cash on Delivery',
                ]);
            }
            // For PayHere: payment record is created by notify callback

            // 7. Initial Status History
            $order->statusHistory()->create([
                'status' => 'pending',
                'notes' => 'Order placed via ' . strtoupper($paymentMethodCode) . '.',
                'customer_notified' => false,
            ]);

            return $order;
        });
    }
}
