<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planner_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planner_session_id')->constrained()->cascadeOnDelete();
            $table->string('sport');
            $table->string('competition')->nullable();
            $table->string('event_name');
            $table->string('market_name');
            $table->string('selection_name');
            $table->decimal('decimal_odds', 6, 2);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('lock_state')->default('unlocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planner_selections');
    }
};
