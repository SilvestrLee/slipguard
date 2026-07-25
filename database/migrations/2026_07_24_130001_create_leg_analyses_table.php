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
        Schema::create('leg_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slip_analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignId('betting_slip_leg_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('sport_code')->nullable();
            $table->string('sport_status');
            $table->string('market_code')->nullable();
            $table->string('market_family')->nullable();
            $table->string('market_complexity');
            $table->string('market_status');
            $table->decimal('decimal_odds', 6, 2);
            $table->string('raw_market_input');
            $table->string('raw_selection_input');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leg_analyses');
    }
};
