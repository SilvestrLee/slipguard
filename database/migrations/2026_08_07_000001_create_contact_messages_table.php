<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `PO-U22-001` — the Contact page's form has nowhere to go without this:
 * no Mailable class exists anywhere in this codebase and `MAIL_MAILER`
 * is `log` (not a real delivery channel), so a submission with no
 * persistence would silently vanish while the page promises a response.
 * A small, standalone table — genuinely a source of truth (the submitted
 * text), not a derivable value, and unrelated to any existing domain
 * (`BettingSlip`, `User`) per `database-migration-safety`'s own
 * "computed vs. stored" test. No foreign key: the Contact page is
 * public, unauthenticated, and a submission is not owned by any
 * SlipGuard account even when the sender happens to be signed in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('subject');
            $table->string('category');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
