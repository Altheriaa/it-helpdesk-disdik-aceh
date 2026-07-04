<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TicketStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return $this->getAdminStats();
        }

        if ($user?->hasRole('it_support')) {
            return $this->getItSupportStats($user);
        }

        return $this->getPegawaiStats($user);
    }

    /**
     * Build sparkline data for the last 7 days based on a query builder.
     *
     * @return array<int, int>
     */
    private function buildSparkline(Builder $query, ?string $status = null): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayQuery = (clone $query)->whereDate('created_at', $date);

            if ($status) {
                $dayQuery->where('status', $status);
            }

            $data[] = $dayQuery->count();
        }

        return $data;
    }

    /**
     * Calculate a percentage change description comparing this week to last week.
     */
    private function trendDescription(Builder $query, ?string $status = null): array
    {
        $thisWeek = (clone $query)
            ->where('created_at', '>=', Carbon::now()->subDays(7));
        $lastWeek = (clone $query)
            ->whereBetween('created_at', [Carbon::now()->subDays(14), Carbon::now()->subDays(7)]);

        if ($status) {
            $thisWeek->where('status', $status);
            $lastWeek->where('status', $status);
        }

        $current = $thisWeek->count();
        $previous = $lastWeek->count();

        if ($previous === 0) {
            return $current > 0
                ? ["+{$current} baru minggu ini", 'heroicon-m-arrow-trending-up', 'success']
                : ['Tidak ada perubahan', null, 'gray'];
        }

        $change = round((($current - $previous) / $previous) * 100);

        if ($change > 0) {
            return ["+{$change}% dari minggu lalu", 'heroicon-m-arrow-trending-up', 'danger'];
        }

        if ($change < 0) {
            return ["{$change}% dari minggu lalu", 'heroicon-m-arrow-trending-down', 'success'];
        }

        return ['Sama dengan minggu lalu', null, 'gray'];
    }

    /**
     * @return array<int, Stat>
     */
    private function getAdminStats(): array
    {
        $base = Ticket::query();

        [$totalDesc, $totalIcon, $totalColor] = $this->trendDescription(clone $base);
        [$openDesc, $openIcon] = $this->trendDescription(clone $base, 'open');
        [$progressDesc, $progressIcon] = $this->trendDescription(clone $base, 'in_progress');
        [$resolvedDesc, $resolvedIcon] = $this->trendDescription(clone $base, 'resolved');

        return [
            Stat::make('Total Tiket', (clone $base)->count())
                ->icon('heroicon-o-ticket')
                ->description($totalDesc)
                ->descriptionIcon($totalIcon)
                ->color('primary')
                ->chart($this->buildSparkline(clone $base)),

            Stat::make('Open', (clone $base)->where('status', 'open')->count())
                ->icon('heroicon-o-inbox')
                ->description($openDesc)
                ->descriptionIcon($openIcon)
                ->color('warning')
                ->chart($this->buildSparkline(clone $base, 'open')),

            Stat::make('In Progress', (clone $base)->where('status', 'in_progress')->count())
                ->icon('heroicon-o-arrow-path')
                ->description($progressDesc)
                ->descriptionIcon($progressIcon)
                ->color('info')
                ->chart($this->buildSparkline(clone $base, 'in_progress')),

            Stat::make('Resolved', (clone $base)->where('status', 'resolved')->count())
                ->icon('heroicon-o-check-circle')
                ->description($resolvedDesc)
                ->descriptionIcon($resolvedIcon)
                ->color('success')
                ->chart($this->buildSparkline(clone $base, 'resolved')),

            Stat::make('Belum Diassign', (clone $base)->whereNull('support_id')->whereIn('status', ['open'])->count())
                ->icon('heroicon-o-user-minus')
                ->color('danger'),

            Stat::make('Total Pengguna', User::count())
                ->icon('heroicon-o-users')
                ->color('gray'),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private function getItSupportStats(User $user): array
    {
        $base = Ticket::query()->where(function (Builder $q) use ($user) {
            $q->where('support_id', $user->support?->id)
                ->orWhereNull('support_id');
        });

        $myTickets = Ticket::query()->where('support_id', $user->support?->id);

        [$totalDesc, $totalIcon] = $this->trendDescription(clone $base);

        return [
            Stat::make('Antrian Tiket', (clone $base)->count())
                ->icon('heroicon-o-queue-list')
                ->description($totalDesc)
                ->descriptionIcon($totalIcon)
                ->color('primary')
                ->chart($this->buildSparkline(clone $base)),

            Stat::make('Open', (clone $base)->where('status', 'open')->count())
                ->icon('heroicon-o-inbox')
                ->color('warning')
                ->chart($this->buildSparkline(clone $base, 'open')),

            Stat::make('Sedang Dikerjakan', (clone $myTickets)->where('status', 'in_progress')->count())
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('info')
                ->chart($this->buildSparkline(clone $myTickets, 'in_progress')),

            Stat::make('Selesai', (clone $myTickets)->where('status', 'resolved')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->buildSparkline(clone $myTickets, 'resolved')),

            Stat::make('Belum Diassign', Ticket::whereNull('support_id')->where('status', 'open')->count())
                ->icon('heroicon-o-user-minus')
                ->color('danger'),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private function getPegawaiStats(User $user): array
    {
        $base = Ticket::query()->whereHas('client', fn (Builder $q) => $q->where('user_id', $user->id));

        [$totalDesc, $totalIcon] = $this->trendDescription(clone $base);

        $waitingResponse = (clone $base)
            ->whereIn('status', ['open', 'in_progress'])
            ->whereDoesntHave('replies', function (Builder $q) {
                $q->where('user_id', '!=', auth()->id())
                    ->whereColumn('replies.created_at', '>=', 'tickets.updated_at');
            })
            ->count();

        return [
            Stat::make('Total Tiket Saya', (clone $base)->count())
                ->icon('heroicon-o-ticket')
                ->description($totalDesc)
                ->descriptionIcon($totalIcon)
                ->color('primary')
                ->chart($this->buildSparkline(clone $base)),

            Stat::make('Open', (clone $base)->where('status', 'open')->count())
                ->icon('heroicon-o-inbox')
                ->color('warning')
                ->chart($this->buildSparkline(clone $base, 'open')),

            Stat::make('In Progress', (clone $base)->where('status', 'in_progress')->count())
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->chart($this->buildSparkline(clone $base, 'in_progress')),

            Stat::make('Selesai', (clone $base)->where('status', 'resolved')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->buildSparkline(clone $base, 'resolved')),

            Stat::make('Menunggu Respon', $waitingResponse)
                ->icon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}
