<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'quote_number', 'quote_request_id', 'created_by',
        'status',
        'customer_name', 'company_name', 'email', 'phone', 'address',
        'quotation_date', 'valid_until',
        'subtotal', 'discount_amount', 'tax_amount', 'total',
        'payment_terms', 'delivery_details', 'terms_conditions', 'notes',
        'pdf_path', 'pdf_generated_at',
        'public_token',
        'sent_at', 'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date'    => 'date',
            'valid_until'       => 'date',
            'subtotal'          => 'decimal:2',
            'discount_amount'   => 'decimal:2',
            'tax_amount'        => 'decimal:2',
            'total'             => 'decimal:2',
            'pdf_generated_at'  => 'datetime',
            'sent_at'           => 'datetime',
            'viewed_at'         => 'datetime',
        ];
    }

    protected $appends = ['status_label'];

    public static array $statuses = [
        'draft'     => 'Draft',
        'ready'     => 'Ready',
        'sent'      => 'Sent',
        'viewed'    => 'Viewed',
        'accepted'  => 'Accepted',
        'rejected'  => 'Rejected',
        'expired'   => 'Expired',
    ];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(QuotationStatusLog::class)->orderBy('created_at');
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(QuotationWhatsappLog::class)->orderByDesc('sent_at');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(QuotationEmailLog::class)->orderByDesc('sent_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(QuotationNote::class)->orderByDesc('created_at');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? ucfirst($this->status);
    }
}
