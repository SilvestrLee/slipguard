<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `PO-U17.0-001` §5/§31 — every demo-owned record is identifiable via the
 * demo user's own `is_demo` marker (not inferred from email address alone,
 * per the commission's own explicit instruction), matching the existing
 * `is_internal` column's exact convention.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
