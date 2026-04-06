<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationWhatsappLog extends Model
{
    protected $fillable = [
        'quotation_id', 'sent_by', 'phone', 'message', 'success', 'api_response', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['success' => 'boolean', 'sent_at' => 'datetime'];
    }

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function sentBy(): BelongsTo { return $this->belongsTo(User::class, 'sent_by'); }
}
