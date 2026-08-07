<?php

use App\Models\User;

/**
 * U-11.3 §7: light/dark/system theme system. True end-to-end client
 * behaviour is covered by the browser evidence recorded in
 * docs/engineering/THEME-STABILITY-AUDIT-2026-07-31.md. These tests keep
 * the server-rendered mechanism and shared runtime contract under focused
 * regression coverage.
 */
test('the public homepage includes the pre-paint theme script and toggle control', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee("localStorage.getItem('slipguard-theme')", false)
        ->assertSee('data-theme', false);
});

test('the authenticated layout includes the pre-paint theme script and toggle control', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee("localStorage.getItem('slipguard-theme')", false)
        ->assertSee('Switch to dark theme');
});

/**
 * Founder direct instruction (2026-07-28): "remove the theme toggle from
 * the auth screens." The layout still reads and reflects whichever theme
 * is globally active (pre-paint script + the logo's isDark swap) — a
 * customer just can't change the theme from this screen anymore.
 */
test('the guest auth layout includes the pre-paint theme script but no toggle control', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee("localStorage.getItem('slipguard-theme')", false)
        ->assertDontSee('Switch to dark theme');
});

test('the CSS defines both directions of the explicit theme override', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain(':root:not([data-theme="light"])')
        ->and($css)->toContain(':root[data-theme="dark"]');
});

test('theme initialization and runtime storage access degrade safely', function () {
    $prePaint = file_get_contents(resource_path('views/partials/theme-init-script.blade.php'));
    $runtime = file_get_contents(resource_path('js/app.js'));

    expect($prePaint)
        ->toContain('try {')
        ->toContain("localStorage.getItem('slipguard-theme')")
        ->toContain('catch (error)')
        ->and($runtime)
        ->toContain('window.SlipGuardTheme = slipGuardTheme')
        ->toContain("window.addEventListener('storage'")
        ->toContain("systemPreference.addEventListener('change'")
        ->toContain('new MutationObserver')
        ->toContain("attributeFilter: ['data-theme']")
        ->toContain('window.localStorage.getItem(storageKey)')
        ->toContain('window.localStorage.setItem(storageKey, theme)')
        ->toContain('catch (error)');
});

test('theme-aware components synchronize through the shared runtime controller', function () {
    foreach ([
        'views/components/theme-toggle.blade.php',
        'views/components/public-nav.blade.php',
        'views/livewire/layout/sidebar-navigation.blade.php',
        'views/livewire/layout/navigation.blade.php',
    ] as $resource) {
        $markup = file_get_contents(resource_path($resource));

        expect($markup)
            ->toContain('window.SlipGuardTheme?.isDark()')
            ->toContain('slipguard-theme-changed.window')
            ->not->toContain("localStorage.getItem('slipguard-theme')");
    }

    $toggle = file_get_contents(resource_path('views/components/theme-toggle.blade.php'));

    expect($toggle)
        ->toContain('window.SlipGuardTheme.toggle()')
        ->toContain('effectiveIsDark = $event.detail.dark');
});
