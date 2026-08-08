<?php

use App\Models\User;
use Livewire\Livewire;

/**
 * `PO-U24-002` — Help & Methodology Completion. Replaces the `coming-soon`
 * stub previously rendered at `/help`. Every assertion below traces to a
 * fact verified against the real RC1 implementation before this page was
 * written — see the directive's own Content Truth Matrix in the
 * implementation report, not assumed from older documentation.
 */
test('a guest is redirected away from Help', function () {
    $this->get(route('help'))->assertRedirect(route('login'));
});

test('an authenticated user sees the real Help & Methodology page, not the coming-soon stub', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('help'))
        ->assertOk()
        ->assertSee('Help & Methodology')
        ->assertDontSee('Help is on the way')
        ->assertDontSee("This part of SlipGuard hasn't been built yet");
});

test('every recommended section from the commissioning directive is present', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('help'))
        ->assertOk()
        ->assertSee('Getting Started')
        ->assertSee('Analysing a Slip')
        ->assertSee('Understanding Your Report')
        ->assertSee('Build an Accumulator')
        ->assertSee('How SlipGuard Thinks')
        ->assertSee('What SlipGuard Does Not Do')
        ->assertSee('Common Questions')
        ->assertSee('Need More Help?');
});

test('Structural Risk Score is explained with the real 0-100 range and does not imply probability', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('Structural Risk Score')
        ->toContain('0 to 100')
        ->toContain('does not represent a probability of winning');
});

/**
 * Real bands/thresholds, verified against `App\Domain\Risk\Results\RiskBand`
 * before writing this copy — not invented.
 */
test('Risk Band explanation uses the real implemented bands and thresholds', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('Low')->toContain('0–24')
        ->toContain('Moderate')->toContain('25–49')
        ->toContain('High')->toContain('50–74')
        ->toContain('Very High')->toContain('75–100');
});

test('Main Contributing Factor is explained and all five real structural factors are listed', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('Main Contributing Factor')
        ->toContain('Number of Selections')
        ->toContain('Combined Odds')
        ->toContain('Selection Odds')
        ->toContain('Risk Concentration')
        ->toContain('Market Complexity');
});

test('Build an Accumulator is documented, including the conversational entry layer described within its real bounds', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('Build an Accumulator')
        ->toContain('own words')
        ->not->toContain('understands anything you type')
        ->not->toContain('AI-powered');
});

/**
 * `PO-RC1-009` regression protection, explicitly required by this
 * directive's §3/§11/§26: Main Contributing Factor (base Analyse/Report)
 * and the Planner's own structural ranking capability must remain
 * distinct, never conflated, on this new page.
 */
test('PO-RC1-009 protection: Main Contributing Factor and the Planner ranking capability remain explicitly distinct', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('This is different from the structural selection-ranking capability inside Build an Accumulator')
        ->toContain('This ranking is separate from the base report')
        ->toContain('the report names a structural factor; the Planner ranks your own selections');
});

test('no prediction, guarantee, or fund-handling language is introduced', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('does not predict')
        ->not->toContain('guaranteed win')
        ->not->toContain('winning picks')
        ->not->toContain('tip of the day');

    // What SlipGuard Does Not Do — the actual, real boundary list.
    expect($html)->toContain('predict match outcomes')
        ->toContain('guarantee winning bets')
        ->toContain('place bets')
        ->toContain('control customer funds');
});

test('the Contact CTA is present and resolves to the real contact route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('help'))
        ->assertOk()
        ->assertSee('Contact SlipGuard')
        ->assertSee(route('contact'), false);
});

test('the FAQ accordion is accessible: real buttons, aria-expanded, aria-controls', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('help'))->assertOk()->getContent();

    expect($html)->toContain('aria-expanded')
        ->toContain('aria-controls="help-faq-panel-0"')
        ->toContain('type="button"')
        ->toContain('Can I reanalyse a slip?')
        ->toContain('Once a slip is analysed, its report is a fixed, permanent record');
});

test('the authenticated shell shows the correct sticky page title for Help', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('help'))
        ->assertOk()
        ->assertSee('<h1 class="truncate text-lg font-semibold tracking-tight text-neutral-900 sm:text-xl">', false)
        ->assertSeeInOrder(['Help &amp; Methodology'], false);
});

test('the sidebar Help description is now honest — real methodology content exists to match it', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('layout.sidebar-navigation')
        ->assertSee('Product guidance and methodology');

    $this->actingAs($user)->get(route('help'))->assertOk()->assertSee('methodology', false);
});
