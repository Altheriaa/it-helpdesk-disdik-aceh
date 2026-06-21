<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class TicketStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $baseQuery = Ticket::query();

        // Scope query based on role
        if ($user?->hasRole('it_support')) {
            $baseQuery->where('support_id', $user->support?->id);
        } elseif ($user?->hasRole('pegawai')) {
            $baseQuery->whereHas('client', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return [
            Stat::make('Total Tiket', (clone $baseQuery)->count())
                ->icon('heroicon-o-ticket')
                ->color('primary'),

            Stat::make('Open', (clone $baseQuery)->where('status', 'open')->count())
                ->icon('heroicon-o-inbox')
                ->color('warning'),

            Stat::make('In Progress', (clone $baseQuery)->where('status', 'in_progress')->count())
                ->icon('heroicon-o-arrow-path')
                ->color('info'),

            Stat::make('Resolved', (clone $baseQuery)->where('status', 'resolved')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
