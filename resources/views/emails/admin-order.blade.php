<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
  .wrap { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .header { background: #111827; color: #fff; padding: 28px 36px; }
  .header h1 { margin: 0; font-size: 20px; }
  .header p { margin: 4px 0 0; opacity: .8; font-size: 13px; }
  .body { padding: 32px 36px; }
  .badge { display: inline-block; background: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; border-radius: 20px; padding: 4px 14px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
  h2 { font-size: 16px; color: #333; margin: 0 0 16px; }
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  .info-table td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
  .info-table td:first-child { color: #888; width: 38%; }
  .items-table { width: 100%; border-collapse: collapse; }
  .items-table th { background: #f7f8fa; padding: 9px 12px; font-size: 12px; text-align: left; color: #666; }
  .items-table td { padding: 9px 12px; font-size: 14px; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
  .btn { display: inline-block; background: #111827; color: #fff; padding: 13px 28px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 24px; }
  .footer { padding: 20px 36px; background: #f9f9f9; font-size: 12px; color: #aaa; text-align: center; }
</style>
</head>
<body>
@php
  $address = is_array($order->shipping_address) ? $order->shipping_address : [];
  $name = trim(($address['first_name'] ?? '').' '.($address['last_name'] ?? ''));
  if ($name === '') {
      $name = $order->customer->name ?? 'Customer';
  }
  $payment = $order->payments->last();
@endphp
<div class="wrap">
  <div class="header">
    <h1>Print Works LK</h1>
    <p>Order confirmed</p>
  </div>
  <div class="body">
    <div class="badge">Order #{{ $order->order_number }}</div>
    <h2>Customer</h2>
    <table class="info-table">
      <tr><td>Name</td><td><strong>{{ $name }}</strong></td></tr>
      <tr><td>Email</td><td>{{ $address['email'] ?? $order->customer->email ?? '—' }}</td></tr>
      <tr><td>Phone</td><td>{{ $address['phone'] ?? $order->customer->phone ?? '—' }}</td></tr>
      <tr><td>Address</td><td>{{ collect([$address['line1'] ?? null, $address['line2'] ?? null, $address['city'] ?? null, $address['district'] ?? null, $address['postal_code'] ?? null])->filter()->implode(', ') ?: '—' }}</td></tr>
      <tr><td>Payment</td><td>{{ strtoupper($payment?->payment_method ?? $order->payment_status) }} · {{ ucfirst($order->payment_status) }}</td></tr>
      <tr><td>Shipping</td><td>{{ $order->shipping_method ?: '—' }}@if($order->shipping_district) ({{ $order->shipping_district }})@endif</td></tr>
    </table>

    <h2>Items</h2>
    <table class="items-table">
      <thead>
        <tr>
          <th>Product</th>
          <th style="text-align:center;width:60px;">Qty</th>
          <th style="text-align:right;width:110px;">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>
            <strong>{{ $item->product_name }}</strong>
            @if((float) $item->customization_fee > 0)
              <br><small style="color:#888;">Customization Rs. {{ number_format($item->customization_fee, 2) }}</small>
            @endif
            @if(is_array($item->customizations) && count($item->customizations))
              @foreach($item->customizations as $label => $value)
                @if(is_scalar($value) && $value !== '')
                  <br><small style="color:#666;">{{ is_string($label) ? $label.': ' : '' }}{{ $value }}</small>
                @endif
              @endforeach
            @endif
          </td>
          <td style="text-align:center;">{{ $item->quantity }}</td>
          <td style="text-align:right;">Rs. {{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <table class="info-table" style="margin-top:18px;">
      <tr><td>Subtotal</td><td style="text-align:right;">Rs. {{ number_format($order->subtotal, 2) }}</td></tr>
      <tr><td>Shipping</td><td style="text-align:right;">Rs. {{ number_format($order->shipping_cost, 2) }}</td></tr>
      @if((float) $order->discount_amount > 0)
      <tr><td>Discount</td><td style="text-align:right;">Rs. {{ number_format($order->discount_amount, 2) }}</td></tr>
      @endif
      <tr><td><strong>Total</strong></td><td style="text-align:right;"><strong>Rs. {{ number_format($order->total, 2) }}</strong></td></tr>
    </table>

    @if($order->notes)
    <div style="margin-top:8px;padding:14px;background:#f9f9f9;border-radius:6px;font-size:14px;">
      <strong>Notes:</strong><br>{{ $order->notes }}
    </div>
    @endif

    <a href="{{ rtrim(config('app.url'), '/') }}/admin/orders/{{ $order->id }}" class="btn">
      Open order →
    </a>
  </div>
  <div class="footer">Print Works LK &bull; printworks.lk</div>
</div>
</body>
</html>
