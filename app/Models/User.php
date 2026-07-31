<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_internal' => 'boolean',
            'can_manage_customer_data' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_internal;
    }

    /**
     * U-10.2 §2/OQ-1: a distinct grant from `is_internal` — an internal
     * user does not automatically gain visibility into customer data by
     * virtue of panel access alone.
     */
    public function canManageCustomerData(): bool
    {
        return (bool) $this->is_internal && (bool) $this->can_manage_customer_data;
    }

    public function bettingSlips(): HasMany
    {
        return $this->hasMany(BettingSlip::class);
    }

    public function slipAnalyses(): HasMany
    {
        return $this->hasMany(SlipAnalysis::class);
    }

    public function labsFeatureInterests(): HasMany
    {
        return $this->hasMany(LabsFeatureInterest::class);
    }

    public function plannerSessions(): HasMany
    {
        return $this->hasMany(PlannerSession::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /** Support Notes written about this user (as a customer), not by them. */
    public function supportNotes(): HasMany
    {
        return $this->hasMany(SupportNote::class, 'customer_id')->latest();
    }
}
