<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `PO-U22-001A` — the minimal New/Read/Resolved workflow the directive
 * asks for. Every existing row (from before this migration) defaults to
 * `new`, which is accurate: nothing persisted before an admin resource
 * existed to read it has actually been read yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->string('status')->default('new')->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
