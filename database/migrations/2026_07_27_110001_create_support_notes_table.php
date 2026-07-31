<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * U-10.2 §4: append-only — no update or delete path exists anywhere for
 * this table. A correction is made by adding a new note, never editing
 * the original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_notes');
    }
};
