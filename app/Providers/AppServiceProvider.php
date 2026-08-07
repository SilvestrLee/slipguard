<?php

namespace App\Providers;

use App\Domain\AccumulatorConversation\AccumulatorIntentInterpreter;
use App\Domain\AccumulatorConversation\DeterministicAccumulatorIntentInterpreter;
use App\Listeners\CheckDatabaseHealth;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // `PO-U23-001` Decision 1 — the rest of the app depends on the
        // interface, never a provider SDK directly (§19). No live
        // provider is bound for this commission; substituting one later
        // is a one-line change here.
        $this->app->bind(AccumulatorIntentInterpreter::class, DeterministicAccumulatorIntentInterpreter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(DiagnosingHealth::class, CheckDatabaseHealth::class);
    }
}
