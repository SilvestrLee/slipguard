<?php

use App\Http\Controllers\AnalysisProcessingController;
use App\Http\Controllers\BettingSlipScreenshotController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/health', function () {
    return response()->json([
        'name' => 'SlipGuard',
        'status' => 'ok',
    ]);
});

/*
 * U-13.0 — public multi-page information architecture. Every navigation
 * item is an independent route, never a same-page anchor. Pricing and
 * Contact remain honest stubs — see public-coming-soon.blade.php. Privacy
 * and Terms became real pages under `PO-CO-002` — no commercial/legal
 * content is fabricated in the process (see each page's own
 * placeholder-handling note).
 */
Route::view('/analyse', 'pages.analyse')->name('analyse');
Route::view('/planner', 'pages.planner')->name('planner.public');
Route::view('/reports', 'pages.reports')->name('reports');
Route::view('/about', 'pages.about')->name('about');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/release-notes', 'pages.release-notes')->name('release-notes');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/contact', 'pages.public-coming-soon', [
    'title' => 'Contact',
    'description' => __("A dedicated contact page hasn't been built yet."),
])->name('contact');
// `CO-MVP-001`/`PO-CO-002` — real Compliance-Office-drafted content,
// replacing the honest stub. Specific clauses still require qualified
// legal counsel review before this can be treated as final — marked
// inline in each page and tracked in
// docs/09-compliance/CO-005-LEGAL-REVIEW-REGISTER.md.
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');

/*
 * U-14.2 — SlipGuard Labs is now guest-visible (read-only teaser);
 * Notify Me/Join Beta remain customer-only actions, gated inside the
 * component itself (labs.index), not by route middleware.
 */
Volt::route('labs', 'labs.index')->name('labs');

Route::middleware('auth')->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    Volt::route('analyze', 'betting-slips.index')->name('analyze');
    Volt::route('analyze/new', 'betting-slips.intake')->name('analyze.intake');
    Volt::route('analyze/create', 'betting-slips.builder')->name('analyze.create');
    Volt::route('analyze/{bettingSlip}/edit', 'betting-slips.builder')->name('analyze.edit');
    Volt::route('analyze/{bettingSlip}/processing', 'betting-slips.processing')->name('analyze.processing');
    Route::post('analyze/{bettingSlip}/processing/run', AnalysisProcessingController::class)->name('analyze.processing.run');
    Volt::route('analyze/{bettingSlip}/report', 'betting-slips.report')->name('analyze.report');
    // Bounded-scope Screenshot Upload (2026-07-28): the only way an uploaded slip screenshot is ever served — private disk, ownership-checked, never a public URL.
    Route::get('analyze/{bettingSlip}/screenshot', [BettingSlipScreenshotController::class, 'show'])->name('analyze.screenshot');

    Volt::route('planner/history', 'planner.history')->name('planner.history');
    Volt::route('planner/{plannerSession}', 'planner.session')->name('planner.session');

    // U-17 Increment Two — internal-only until MARKET_WIDE_PLANNER_ENABLED is
    // true; the component's own mount() aborts 404 while the flag is off.
    Volt::route('plan-accumulator', 'market-intelligence.builder')->name('builder');

    Volt::route('history', 'history.index')->name('history');

    Volt::route('journal', 'journal.index')->name('journal');
    Volt::route('journal/create', 'journal.entry')->name('journal.create');
    Volt::route('journal/{journalEntry}/edit', 'journal.entry')->name('journal.edit');

    Route::view('help', 'coming-soon', ['title' => 'Help'])->name('help');
    Route::view('settings', 'coming-soon', ['title' => 'Settings'])->name('settings');
});

require __DIR__.'/auth.php';
