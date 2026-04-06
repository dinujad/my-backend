<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationStatusLog extends Model
{
    protected $fillable = ['quotation_id', 'changed_by', 'from_status', 'to_status', 'note'];

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
