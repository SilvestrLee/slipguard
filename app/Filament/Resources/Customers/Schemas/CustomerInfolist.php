<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

/**
 * U-10.1 §7 (Customer Overview) / U-10.2 §6 (diagnostics). Session
 * metadata (IP, user agent, last activity) is read directly from
 * Laravel's own existing `sessions` table — already retained for
 * ordinary session management, not new collection (U-10.2's own
 * proportionality determination).
 */
class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('created_at')
                            ->label('Registered')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Activity')
                    ->schema([
                        TextEntry::make('betting_slips_count')
                            ->label('Slips')
                            ->state(fn (User $record) => $record->bettingSlips()->count()),
                        TextEntry::make('slip_analyses_count')
                            ->label('Analyses')
                            ->state(fn (User $record) => $record->slipAnalyses()->count()),
                        TextEntry::make('planner_sessions_count')
                            ->label('Planner sessions')
                            ->state(fn (User $record) => $record->plannerSessions()->count()),
                        TextEntry::make('journal_entries_count')
                            ->label('Journal entries')
                            ->state(fn (User $record) => $record->journalEntries()->count())
                            ->helperText('Content is not shown here — use "View Journal Entries" below.'),
                    ])
                    ->columns(4),

                Section::make('Session diagnostics')
                    ->description('From the most recent session on record — not newly collected for this console.')
                    ->schema([
                        TextEntry::make('last_activity')
                            ->label('Last active')
                            ->state(function (User $record) {
                                $session = DB::table('sessions')
                                    ->where('user_id', $record->id)
                                    ->orderByDesc('last_activity')
                                    ->first();

                                return $session ? date('Y-m-d H:i:s', $session->last_activity) : 'No session on record';
                            }),
                        TextEntry::make('ip_address')
                            ->label('IP address')
                            ->state(function (User $record) {
                                return DB::table('sessions')
                                    ->where('user_id', $record->id)
                                    ->orderByDesc('last_activity')
                                    ->value('ip_address') ?? '—';
                            }),
                        TextEntry::make('user_agent')
                            ->label('Browser / device')
                            ->state(function (User $record) {
                                return DB::table('sessions')
                                    ->where('user_id', $record->id)
                                    ->orderByDesc('last_activity')
                                    ->value('user_agent') ?? '—';
                            }),
                    ])
                    ->columns(3),
            ]);
    }
}
