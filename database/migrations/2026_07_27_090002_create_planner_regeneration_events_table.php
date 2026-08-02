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
        Schema::create('planner_regeneration_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planner_session_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('sequence_number');
            $table->string('availability');
            $table->unsignedTinyInteger('structural_score')->nullable();
            $table->string('risk_band')->nullable();
            $table->json('attributions');
            $table->timestamps();

            $table->unique(
                ['planner_session_id', 'sequence_number'],
                'planner_regen_session_sequence_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planner_regeneration_events');
    }
};
