<?php

namespace App\Models;

use Database\Factories\SupportNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * U-10.2 §4: append-only. There is deliberately no update or delete
 * action anywhere in this codebase for this model — a correction is made
 * by creating a new note, never editing this one.
 */
class SupportNote extends Model
{
    /** @use HasFactory<SupportNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'author_id',
        'customer_id',
        'note',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
