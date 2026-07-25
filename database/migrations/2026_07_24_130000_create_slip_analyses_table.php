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
        Schema::create('slip_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_slip_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('availability');
            $table->unsignedTinyInteger('structural_score')->nullable();
            $table->string('risk_band')->nullable();
            $table->unsignedTinyInteger('data_quality_score');
            $table->string('data_quality_band');
            $table->boolean('limited_analysis')->default(false);
            $table->json('factor_results');
            $table->json('interaction_adjustments');
            $table->json('data_quality_deductions');
            $table->json('factors_not_evaluated');
            $table->json('reason_codes');
            $table->string('engine_version');
            $table->string('rule_set_version');
            $table->string('input_schema_version');
            $table->string('market_taxonomy_version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_analyses');
    }
};
