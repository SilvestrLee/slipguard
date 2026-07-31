<?php

namespace App\Models;

use Database\Factories\OperationsAuditLogEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * U-10.2 §5: immutable once created — no `updated_at` column exists at
 * all (`UPDATED_AT = null`), and no update/delete action exists anywhere
 * for this model.
 */
class OperationsAuditLogEntry extends Model
{
    /** @use HasFactory<OperationsAuditLogEntryFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'operator_id',
        'customer_id',
        'action',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
