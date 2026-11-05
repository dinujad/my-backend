<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'shipping_method',
        'shipping_district',
        'total',
        'currency',
        'notes',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Latest recorded gateway / COD code, e.g. cod, onepay, payzy, payhere. */
    public function paymentMethodCode(): ?string
    {
        $fromPayment = $this->relationLoaded('payments')
            ? optional($this->payments->sortByDesc('id')->first())->payment_method
            : $this->payments()->latest('id')->value('payment_method');

        if (is_string($fromPayment) && $fromPayment !== '') {
            return strtolower($fromPayment);
        }

        $note = $this->relationLoaded('statusHistory')
            ? optional($this->statusHistory->first(
                fn ($row) => is_string($row->notes) && str_starts_with($row->notes, 'Order placed via ')
            ))->notes
            : $this->statusHistory()->where('notes', 'like', 'Order placed via %')->value('notes');

        if (is_string($note) && preg_match('/Order placed via\s+([A-Za-z0-9_]+)/', $note, $match)) {
            return strtolower($match[1]);
        }

        return null;
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->paymentMethodCode()) {
            'cod' => 'Cash on Delivery',
            'onepay' => 'OnePay',
            'payzy' => 'Payzy',
            'payhere' => 'PayHere',
            null => 'Not set',
            default => strtoupper((string) $this->paymentMethodCode()),
        };
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'PW';
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad((string) $last, 4, '0', STR_PAD_LEFT);
    }
}
