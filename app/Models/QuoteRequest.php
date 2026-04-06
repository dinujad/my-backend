<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteRequest extends Model
{
    protected $fillable = [
        'request_number', 'customer_id', 'assigned_to',
        'customer_name', 'company_name', 'email', 'phone', 'address',
        'status', 'preferred_contact', 'preferred_response',
        'deadline', 'urgency', 'customer_notes', 'admin_notes',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline'          => 'date',
            'last_activity_at'  => 'datetime',
        ];
    }

    public static array $statuses = [
        'new'                  => 'New',
        'reviewing'            => 'Under Review',
        'awaiting_pricing'     => 'Awaiting Pricing',
        'quoted'               => 'Quoted',
        'sent'                 => 'Sent',
        'customer_responded'   => 'Customer Responded',
        'approved'             => 'Approved',
        'rejected'             => 'Rejected',
        'closed'               => 'Closed',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(QuoteRequestStatusLog::class)->orderBy('created_at');
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? ucfirst($this->status);
    }
}
