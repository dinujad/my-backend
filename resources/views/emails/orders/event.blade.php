<x-mail::message>
# Order Update: {{ ucfirst($eventType) }}

Hello,

The status of your order **#{{ $order->order_number }}** has been updated to **{{ ucfirst($eventType) }}**.

@if($eventType === 'shipped')
@if($order->shipments->last() && $order->shipments->last()->tracking_number)
You can track your order using the following tracking number: {{ $order->shipments->last()->tracking_number }}
@endif
@endif

**Total Amount:** Rs. {{ number_format($order->total, 2) }}

If you have any questions, feel free to contact our support team or reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
