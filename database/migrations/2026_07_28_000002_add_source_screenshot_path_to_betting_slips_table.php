<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Screenshot intake (bounded scope, 2026-07-28): the uploaded image is
 * stored privately and referenced here so the Builder can display it
 * alongside the manual transcription form — never served from a public
 * disk/URL, always through an authorized, ownership-checked route.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('betting_slips', function (Blueprint $table) {
            $table->string('source_screenshot_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('betting_slips', function (Blueprint $table) {
            $table->dropColumn('source_screenshot_path');
        });
    }
};
