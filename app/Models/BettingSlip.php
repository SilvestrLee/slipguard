<?php

namespace App\Models;

use Database\Factories\BettingSlipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BettingSlip extends Model
{
    /** @use HasFactory<BettingSlipFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    protected $fillable = ['name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(BettingSlipLeg::class)->orderBy('display_order');
    }
}
