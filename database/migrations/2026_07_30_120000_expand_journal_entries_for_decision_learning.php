<?php

use App\Domain\Journal\JournalEntryCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('title', 120)->nullable()->after('slip_analysis_id');
            $table->string('category')->default(JournalEntryCategory::GeneralReflection->value)->after('title');
            $table->text('next_time_note')->nullable()->after('reflection');
            $table->boolean('analysis_was_linked')->default(false)->after('slip_analysis_id');
        });

        // Every pre-Sprint 12 record was constitutionally required to link
        // an analysis. Preserve that historical fact before nullability is
        // introduced so a later missing relation can be labelled honestly.
        DB::table('journal_entries')->update(['analysis_was_linked' => true]);

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['slip_analysis_id']);
            $table->foreignId('slip_analysis_id')->nullable()->change();
            $table->foreign('slip_analysis_id')->references('id')->on('slip_analyses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Forward-only by Product Office direction. Once an intentionally
        // unlinked entry exists, restoring the former required/cascading
        // relationship would destroy valid customer history.
    }
};
