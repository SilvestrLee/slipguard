<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * U-17.5 §5/§6 — one row per bookmaker quote for one canonical market on
 * one fixture, confirmed against real U-17.3 payloads (outcomes carries
 * the confirmed {name, price, point?} shape — `point` is the Total Goals
 * line value, absent on every other confirmed market family).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_intelligence_market_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_intelligence_fixture_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('canonical_market');
            $table->string('provider_market_key');
            $table->string('bookmaker_key');
            $table->json('outcomes');
            $table->timestamp('provider_last_update')->nullable();
            $table->timestamp('retrieved_at');
            $table->timestamp('cache_expires_at');
            $table->string('evidence_quality');
            $table->timestamps();

            $table->index(['canonical_market', 'evidence_quality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_intelligence_market_quotes');
    }
};
