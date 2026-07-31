<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * U-10.2 §2/OQ-1: `is_internal` alone is an insufficient permission for
 * viewing customer data — a distinct grant is required. This is that
 * grant, deliberately separate from `is_internal` (which only controls
 * whether the /operations panel is reachable at all).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_manage_customer_data')->default(false)->after('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_manage_customer_data');
        });
    }
};
