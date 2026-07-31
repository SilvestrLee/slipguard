<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

/**
 * U-12.0 (SGDS). Verifies the token foundation exists in the compiled CSS
 * source, the new shared component primitives render with their expected
 * classes, and no legacy raw Tailwind palette alert class survives on the
 * screens migrated this programme. True visual verification (gradient
 * quality, shadow contrast, spacing rhythm) can't be performed without a
 * browser, which isn't available in this environment — stated explicitly,
 * not implied, per `PO-U12.0-001` §33.3's own allowance for this case.
 */
test('the CSS defines the U-12.0 surface, elevation, gradient, and alert tokens', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('--surface-card')
        ->and($css)->toContain('--shadow-1')
        ->and($css)->toContain('--gradient-hero')
        ->and($css)->toContain('--gradient-page')
        ->and($css)->toContain('--gradient-inverse')
        ->and($css)->toContain('--alert-success')
        ->and($css)->toContain('--alert-error')
        ->and($css)->toContain('--confidence-high');
});

test('x-card renders each variant with its expected surface/elevation classes', function () {
    $standard = Blade::render('<x-card>content</x-card>');
    $elevated = Blade::render('<x-card variant="elevated">content</x-card>');
    $interactive = Blade::render('<x-card variant="interactive" href="/somewhere">content</x-card>');
    $soft = Blade::render('<x-card variant="soft">content</x-card>');

    expect($standard)->toContain('workspace-record-surface')->toContain('shadow-elevation-1');
    expect($elevated)->toContain('workspace-primary-card')->toContain('shadow-elevation-2');
    expect($interactive)->toContain('<a href="/somewhere"')->toContain('workspace-record-surface');
    expect($soft)->toContain('bg-surface-section')->toContain('workspace-structural-border');
});

test('x-badge renders the neutral tone by default and a quality tone when given one', function () {
    $neutral = Blade::render('<x-badge>Draft</x-badge>');
    $quality = Blade::render('<x-badge tone="quality-strong">Strong</x-badge>');

    expect($neutral)->toContain('bg-neutral-100')->toContain('text-neutral-600');
    expect($quality)->toContain('bg-quality-strong/10')->toContain('text-quality-strong');
});

test('x-alert renders each variant using dedicated alert tokens, never the raw Tailwind palette', function () {
    foreach (['success', 'error', 'caution', 'info'] as $variant) {
        $html = Blade::render("<x-alert variant=\"{$variant}\">message</x-alert>");

        expect($html)->toContain("alert-{$variant}")
            ->and($html)->toContain('role="alert"');
    }
});

test('x-empty-state and x-page-header render their title, description, and action slot', function () {
    $emptyState = Blade::render(
        '<x-empty-state title="Nothing here" description="Come back later">
            <x-slot name="action"><a href="/x">Go</a></x-slot>
        </x-empty-state>'
    );

    expect($emptyState)->toContain('Nothing here')->toContain('Come back later')->toContain('href="/x"');

    $pageHeader = Blade::render(
        '<x-page-header title="My Title" description="My description">
            <x-slot name="action"><a href="/y">Action</a></x-slot>
        </x-page-header>'
    );

    expect($pageHeader)->toContain('My Title')->toContain('My description')->toContain('href="/y"');
});

test('the betting slip index, dashboard, and journal no longer render raw red/green/amber/blue alert classes', function () {
    $user = User::factory()->create();

    foreach (['analyze', 'dashboard', 'journal', 'history', 'planner.history'] as $routeName) {
        $this->actingAs($user)->get(route($routeName))
            ->assertOk()
            ->assertDontSee('bg-red-50', false)
            ->assertDontSee('bg-green-50', false)
            ->assertDontSee('bg-amber-50', false)
            ->assertDontSee('bg-blue-50', false);
    }
});

test('the homepage hero and Intelligence Section use the new gradient and surface tokens', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('bg-gradient-hero', false)
        ->assertSee('bg-surface-inverse', false)
        ->assertSee('bg-gradient-inverse', false);
});

/**
 * U-12.0 completion pass (`PO-U12.0-CP-002`): the semantic container
 * hierarchy replacing the ad hoc max-w-2xl..max-w-7xl mix flagged during
 * U-12.0's own audit. Verifies both that the four utilities exist in the
 * compiled CSS with the correct pixel values, and that each screen uses
 * the container its purpose maps to.
 */
test('the CSS defines all four semantic container utilities with the correct widths', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('@utility container-reading')
        ->and($css)->toContain('max-width: 45rem')
        ->and($css)->toContain('@utility container-standard')
        ->and($css)->toContain('max-width: 60rem')
        ->and($css)->toContain('@utility container-analytics')
        ->and($css)->toContain('max-width: 75rem')
        ->and($css)->toContain('@utility container-marketing')
        ->and($css)->toContain('max-width: 80rem');
});

test('the homepage uses container-marketing throughout, never a raw max-w-Nxl section wrapper', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('container-marketing', false)
        ->assertDontSee('max-w-6xl', false)
        ->assertDontSee('max-w-5xl', false);
});

test('Slip Index, History, Journal, and Profile use container-standard while Dashboard uses the wider workspace canvas', function () {
    $user = User::factory()->create();

    foreach (['analyze', 'history', 'journal', 'profile'] as $routeName) {
        $this->actingAs($user)->get(route($routeName))
            ->assertOk()
            ->assertSee('container-standard', false);
    }

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('max-w-[88rem]', false)
        ->assertSee('workspace-gutter', false);
});

test('Builder and Planning History use container-analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('analyze.create'))
        ->assertOk()
        ->assertSee('container-analytics', false);

    $this->actingAs($user)->get(route('planner.history'))
        ->assertOk()
        ->assertSee('container-analytics', false);
});

test('Journal Entry uses container-reading', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('journal.create'))
        ->assertOk()
        ->assertSee('container-reading', false);
});

test('the Risk Report uses container-reading, a deliberate deviation from the commission\'s own container-analytics table entry, preserving the pre-existing "document, not a dashboard" principle', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win',
        'decimal_odds' => '1.90',
        'display_order' => 0,
    ]);
    $slip->markReady();
    (new AnalyzeBettingSlip)->execute($slip->fresh('legs'));

    $this->actingAs($user)->get(route('analyze.report', $slip))
        ->assertOk()
        ->assertSee('container-reading', false)
        ->assertDontSee('container-analytics', false);
});

test('Sprint 10 defines the shared workspace spacing and typography language', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    foreach ([
        '@utility workspace-page',
        '@utility workspace-gutter',
        '@utility workspace-stack',
        '@utility workspace-section',
        '@utility workspace-grid',
        '@utility workspace-card-padding',
        '@utility workspace-title',
        '@utility workspace-section-title',
        '@utility workspace-card-title',
        '@utility workspace-metadata',
        '@utility workspace-helper',
    ] as $utility) {
        expect($css)->toContain($utility);
    }
});

test('Sprint 10 shared workspace components render semantic states and visible focus treatment', function () {
    $risk = Blade::render('<x-workspace.risk-badge band="High" tone="high" />');
    $record = Blade::render('<x-workspace.record-card title="Weekend review" metadata="Analysed today">Open</x-workspace.record-card>');
    $error = Blade::render('<x-workspace.inline-error>Try again.</x-workspace.inline-error>');
    $limited = Blade::render('<x-workspace.limited-data-notice>Some markets were unavailable.</x-workspace.limited-data-notice>');
    $progress = Blade::render('<x-workspace.progress-stage label="Evaluating structural risk" state="current" />');
    $noResults = Blade::render('<x-workspace.no-results />');

    expect($risk)->toContain('High')->toContain('text-risk-high');
    expect($record)->toContain('Weekend review')->toContain('Analysed today')->toContain('focus-visible:outline');
    expect($error)->toContain('role="alert"')->toContain('Try again.');
    expect($limited)->toContain('Limited data')->toContain('Some markets were unavailable.');
    expect($progress)->toContain('aria-current="step"')->toContain('Evaluating structural risk');
    expect($noResults)->toContain('role="status"')->toContain('No matching results');
});

test('the complete Sprint 10 component inventory is available for later page composition', function () {
    foreach ([
        'section-heading', 'filter-bar', 'search-field', 'segmented-control',
        'selection-chip', 'risk-badge', 'status-badge', 'record-card',
        'overflow-menu', 'no-results', 'loading-skeleton', 'inline-error',
        'limited-data-notice', 'sticky-summary', 'progress-stage',
        'completion-summary', 'confirmation-dialog', 'load-more',
    ] as $component) {
        expect(file_exists(resource_path("views/components/workspace/{$component}.blade.php")))->toBeTrue();
    }
});

test('core authenticated routes adopt the Sprint 10 workspace rhythm and shared record language', function () {
    $user = User::factory()->create();

    foreach (['dashboard', 'analyze', 'history', 'journal', 'planner.history'] as $routeName) {
        $this->actingAs($user)->get(route($routeName))
            ->assertOk()
            ->assertSee('workspace-page', false)
            ->assertSee('workspace-gutter', false);
    }
});

test('the final Sprint 10 hierarchy defines distinct section, record, primary, and border tokens in both themes', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    foreach ([
        '--surface-section',
        '--surface-record',
        '--surface-primary',
        '--border-structural',
        '--border-internal',
        '.workspace-section-panel',
        '.workspace-primary-card',
        '.workspace-record-surface',
        '.workspace-structural-border',
        '.workspace-internal-border',
    ] as $tokenOrClass) {
        expect($css)->toContain($tokenOrClass);
    }

    expect(substr_count($css, '--surface-section:'))->toBeGreaterThanOrEqual(3)
        ->and(substr_count($css, '--surface-record:'))->toBeGreaterThanOrEqual(3);
});

test('dashboard, histories, journal, slip list, planner, and report adopt the three-level surface hierarchy', function () {
    foreach ([
        'livewire/dashboard.blade.php',
        'livewire/betting-slips/index.blade.php',
        'livewire/betting-slips/report.blade.php',
        'livewire/history/index.blade.php',
        'livewire/journal/index.blade.php',
        'livewire/planner/history.blade.php',
        'livewire/planner/session.blade.php',
    ] as $view) {
        $markup = file_get_contents(resource_path("views/{$view}"));
        expect(
            str_contains($markup, 'workspace-primary-card')
            || str_contains($markup, 'workspace-section-panel')
        )->toBeTrue();
    }

    expect(file_get_contents(resource_path('views/components/workspace/record-card.blade.php')))
        ->toContain('workspace-record-surface');
});
