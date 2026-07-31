<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * U-10.2 §5: session-event-granular, retained indefinitely, immutable.
 * No `updated_at` column exists at all — enforced structurally, not just
 * by convention (mirrors PlannerRegenerationEvent's own precedent, though
 * that model does keep timestamps(); this one deliberately omits
 * updated_at since an audit entry is never touched again after creation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations_audit_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_audit_log_entries');
    }
};
