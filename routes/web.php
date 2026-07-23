<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'name' => 'SlipGuard',
        'status' => 'ok',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    Volt::route('analyze', 'betting-slips.index')->name('analyze');
    Volt::route('analyze/create', 'betting-slips.builder')->name('analyze.create');
    Volt::route('analyze/{bettingSlip}/edit', 'betting-slips.builder')->name('analyze.edit');

    Route::view('history', 'coming-soon', ['title' => 'History'])->name('history');
    Route::view('journal', 'coming-soon', ['title' => 'Journal'])->name('journal');
    Route::view('help', 'coming-soon', ['title' => 'Help'])->name('help');
    Route::view('settings', 'coming-soon', ['title' => 'Settings'])->name('settings');
});

require __DIR__.'/auth.php';
