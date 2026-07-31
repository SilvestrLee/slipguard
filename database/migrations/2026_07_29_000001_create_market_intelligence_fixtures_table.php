<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * U-17.5 §5/§7 — a small operational evidence store, not a warehouse,
 * mirroring SlipAnalysis/PlannerRegenerationEvent's own "one row per
 * fact" shape. No customer/user foreign key: this is provider evidence,
 * not customer data, and is short-lived per U-17.4's conservative-caching
 * requirement (cache_expires_at, pruned rather than retained indefinitely).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_intelligence_fixtures', function (Blueprint $table) {
            $table->id();
            $table->string('provider_event_id')->unique();
            $table->string('competition_key');
            $table->string('home_team');
            $table->string('away_team');
            $table->timestamp('commence_time');
            $table->timestamp('retrieved_at');
            $table->timestamp('cache_expires_at');
            $table->timestamps();

            $table->index(['competition_key', 'commence_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_intelligence_fixtures');
    }
};
