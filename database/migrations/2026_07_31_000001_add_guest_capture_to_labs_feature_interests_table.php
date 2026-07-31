<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `PO-U19.1-001` — the existing Labs interest system is authenticated-only
 * by design (`toggleInterest()` on the Labs page does
 * `abort_unless(Auth::check(), 401)`, unchanged by this migration or
 * anything built on top of it). This adds the columns needed for a
 * *separate*, minimal-friction, guest-capable entry point (the public
 * homepage's mobile-apps waitlist) to write into the same table — reusing
 * the model, not building a parallel one — without touching the existing
 * authenticated Labs page's behaviour at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labs_feature_interests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('email')->nullable()->after('user_id');
            $table->string('platform_preference')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('labs_feature_interests', function (Blueprint $table) {
            $table->dropColumn(['email', 'platform_preference']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
