<?php

namespace App\Models;

use App\Support\ProductMediaPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'shortlisted' => 'Shortlisted',
        'rejected' => 'Rejected',
        'hired' => 'Hired',
    ];

    protected $fillable = [
        'job_opening_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'street_address',
        'address_line_2',
        'position_applied',
        'employment_history',
        'cover_letter',
        'resume_path',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'employment_history' => 'array',
    ];

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function resumeUrl(): ?string
    {
        if (!$this->resume_path) {
            return null;
        }

        return ProductMediaPath::publicUrl($this->resume_path);
    }
}
