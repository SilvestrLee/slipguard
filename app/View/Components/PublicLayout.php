<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * U-13.0 — shared shell for every public-website page (Home, Analyse,
 * Planner, Reports, About, and the honest coming-soon stubs). Mirrors
 * the existing AppLayout/GuestLayout class-component pattern.
 */
class PublicLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    public function render(): View
    {
        return view('layouts.public');
    }
}
